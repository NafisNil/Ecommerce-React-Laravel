<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Dom\Text;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;

class ProductVariation extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Product Variations';
    }

    public function getBreadcrumb(): string
    {
        return 'Product Variations';
    }

    public function getTitle(): string
    {
        return 'Product Variations';
    }

    public function form(Schema $schema): Schema
    {
        $types = $this->record->variationTypes()->with('options')->get();
        // Build a quick lookup from option id -> [name, type_id]
        $optionIndex = [];
        foreach (($types ?? []) as $t) {
            foreach (($t->options ?? []) as $opt) {
                $optionIndex[$opt->id] = ['name' => $opt->name, 'type_id' => $t->id];
            }
        }

        $fields = [];
        foreach (($types ?? []) as $type) {
            $typeId = $type->id;
            $fields[] = Hidden::make('variation_type_' . $typeId . '_id')
                ->dehydrated(false);
            $fields[] = TextInput::make('variation_type_' . $typeId . '_name')
                ->label($type->name)
                ->disabled()
                ->dehydrated(false)
                ->formatStateUsing(function ($state, callable $get) use ($typeId, $optionIndex) {
                    // Prefer direct state when present
                    if (!empty($state)) {
                        return (string) $state;
                    }
                    // Derive from selected option ids
                    $ids = $get('variation_type_option_ids') ?? [];
                    foreach ($ids as $oid) {
                        if (isset($optionIndex[$oid]) && $optionIndex[$oid]['type_id'] === $typeId) {
                            return (string) $optionIndex[$oid]['name'];
                        }
                    }
                    return '';
                });
        }
        return $schema->columns(1)->components([
            // Repeater for product variations
            Repeater::make('variations')
                ->label('Product Variations')
                ->relationship()
                ->addable(false)
                ->collapsible()
                ->defaultItems(0)
                ->columns(3)
                ->columnSpan(2)
                ->itemLabel(function (array $state): ?string {
                    $pairs = [];
                    foreach ($state as $k => $v) {
                        if (is_string($k) && str_starts_with($k, 'variation_type_') && str_ends_with($k, '_name') && !empty($v)) {
                            // extract type id from key
                            $typeIdStr = str_replace(['variation_type_', '_name'], '', $k);
                            $typeId = is_numeric($typeIdStr) ? (int) $typeIdStr : null;
                            if ($typeId !== null) {
                                $pairs[$typeId] = $v;
                            }
                        }
                    }
                    if (empty($pairs)) {
                        return null;
                    }
                    $labels = [];
                    foreach ($pairs as $name) {
                        $labels[] = $name;
                    }
                    return implode(' | ', $labels);
                })
                ->afterStateHydrated(function (Repeater $component, $state) use ($types) {
                    // Prefill with generated combinations if empty
                    if (!empty($state)) {
                        return;
                    }
                    $defaultQuantity = $this->record->quantity ?? 0;
                    $defaultPrice = $this->record->price ?? 0.00;
                    $generated = $this->cartesianProduct($types, $defaultQuantity, $defaultPrice);
                    if (!empty($generated)) {
                        $component->state($generated);
                    }
                })
                ->schema(array_merge(
                    $fields,
                    [
                        Hidden::make('variation_type_option_ids'),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->required(),
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->required(),
                    ]
                ))
                ->createItemButtonLabel('Add Variation')
                ->disableLabel(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load variation types and options if needed

        $variations = $this->record->variations->toArray();
        $variationTypes = $this->record->variationTypes()->with('options')->get();
        $data['variations'] = $this->mergeCartesianWithExisting($variationTypes, $variations);
        return $data;
    }

    private function mergeCartesianWithExisting($variationTypes, $existingVariations)
    {
        // Generate all combinations of variation options
        $defaultQuantity = $this->record->quantity ?? 0;
        $defaultPrice = $this->record->price ?? 0.00;
        $cartesianProduct = $this->cartesianProduct($variationTypes, $defaultQuantity, $defaultPrice);
        $mergedResults = [];
        foreach (($cartesianProduct ?? []) as $combination) {
            $optionIds = collect($combination)
                ->filter(fn($value, $key) => is_string($key)
                    && str_starts_with($key, 'variation_type_')
                    && is_array($value)
                    && array_key_exists('id', $value))
                ->map(fn($option) => $option['id'])
                ->values()
                ->toArray();

            $match = array_filter($existingVariations ?? [], function ($existingOption) use ($optionIds) {
                return ($existingOption['variation_type_option_ids'] ?? []) === $optionIds;
            });

            // Start with the generated combination as base
            $row = $combination;
            $row['variation_type_option_ids'] = $optionIds; // ensure present

            if ($match) {
                $existingEntry = reset($match);
                $row['id'] = $existingEntry['id'] ?? null; // keep id to update instead of create
                $row['quantity'] = $existingEntry['quantity'] ?? $defaultQuantity;
                $row['price'] = $existingEntry['price'] ?? $defaultPrice;
            } else {
                $row['quantity'] = $defaultQuantity;
                $row['price'] = $defaultPrice;
            }

            // Ensure helper name/id keys exist for display
            $row = $this->ensureNameKeys($row, $variationTypes);

            $mergedResults[] = $row;
        }

        return $mergedResults;
    }

    private function ensureNameKeys(array $row, $variationTypes): array
    {
        foreach (($variationTypes ?? []) as $type) {
            $baseKey = 'variation_type_' . $type->id;
            if (isset($row[$baseKey]) && is_array($row[$baseKey])) {
                $row[$baseKey . '_id'] = $row[$baseKey . '_id'] ?? ($row[$baseKey]['id'] ?? null);
                $row[$baseKey . '_name'] = $row[$baseKey . '_name'] ?? ($row[$baseKey]['name'] ?? null);
            }
        }
        return $row;
    }

    private function cartesianProduct($variationTypes, $defaultQuantity, $defaultPrice)
    {
        $result = [[]];
        foreach (($variationTypes ?? []) as $type) {
            $newResult = [];
            foreach(($type->options ?? []) as $option) {
                foreach ($result as $combination) {
                    $newCombination = $combination +[
                        'variation_type_' . $type->id => ['id' => $option->id, 'name' => $option->name, 'label' => $type->name],
                    ];
                    $newResult[] = $newCombination;
                }
            }
            $result = $newResult;
    }
        // Add default quantity and price to each combination
        foreach ($result as &$combination) {
            if (count($combination) === count($variationTypes)) {
                # code...
                $combination['quantity'] = $defaultQuantity;
                $combination['price'] = $defaultPrice;
                // also compute the option ids array & flatten display names
                $optionIds = collect($combination)
                    ->filter(fn($v, $k) => is_string($k)
                        && str_starts_with($k, 'variation_type_')
                        && is_array($v)
                        && array_key_exists('id', $v))
                    ->map(fn($v) => $v['id'])
                    ->values()
                    ->toArray();
                $combination['variation_type_option_ids'] = $optionIds;

                // populate display fields for locked labels
                foreach ($combination as $k => $v) {
                    if (
                        is_string($k)
                        && str_starts_with($k, 'variation_type_')
                        && is_array($v)
                        && array_key_exists('id', $v)
                    ) {
                        $combination[$k . '_id'] = $v['id'] ?? null;
                        $combination[$k . '_name'] = $v['name'] ?? null;
                    }
                }
            }
        }
        return $result;
    }
}

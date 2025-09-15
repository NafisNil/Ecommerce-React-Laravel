<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Form;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Enums\ProductVariationTypesEnum;

/**
 * Deprecated: Replaced by embedding SpatieMediaLibraryFileUpload directly in the main Product form.
 * This page is retained temporarily for reference and can be safely deleted.
 */
class ProductVariationTypes extends EditRecord
{
    use InteractsWithForms;

    public static ?string $title = 'Product Variation Types';

    protected static string $resource = ProductResource::class;

    protected Product $product; // Bound model

    public function mount($record): void
    {
        $this->product = Product::findOrFail($record);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Repeater::make('variation_types')
                ->label('Variation Types')
                ->relationship()
                ->collapsible()
                ->defaultItems(1)
                ->columns(2)
                ->columnSpan(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Type Name')
                        ->placeholder('e.g. Color, Size')
                        ->required(),
                    Select::make('types')
                    ->options(ProductVariationTypesEnum::labels())->label('Select Type')->placeholder('Select a type')->searchable()->required(),
                      
                   
                    Repeater::make('options')
                        ->label('Options')
                        ->schema([TextInput::make('value')->label('Value')->placeholder('e.g. Red, Large, 128GB')->required(), TextInput::make('code')->label('Code / SKU Suffix')->placeholder('Optional code')->maxLength(50)->helperText('Optional shorthand or SKU fragment.'), Toggle::make('active')->label('Active')->default(true)])
                        ->minItems(1)
                        ->collapsed()
                        ->itemLabel(fn(array $state): ?string => $state['value'] ?? null)
                        ->reorderable()
                        ->addActionLabel('Add Option'),
                ])
                ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                ->reorderable()
                ->addActionLabel('Add Variation Type')
                ->helperText('Define variation types (e.g. Color, Size) and their options. Saved as JSON on the product.')
                ->cloneable()

        ]);
    }
}

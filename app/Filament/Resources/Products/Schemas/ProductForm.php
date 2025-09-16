<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatusEnum;
use Dom\Text;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput as ComponentsTextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select as ComponentsSelect;
use App\Enums\ProductVariationTypesEnum;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;


class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                TextInput::make('title')
                    ->label('Product Title')
                    ->live(onBlur: true)
                    ->required()
                    ->afterStateUpdated(
                        function(string $operation, callable $set, $state) {
                            $set('slug', Str::slug($state));
                        }
                    ),
                TextInput::make('slug')
                    ->label('Product Slug')
                    ->required(),
                Select::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->preload()
                    ->searchable()
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(function(callable $set) {
                        $set('category_id', null);
                    }),
                Select::make('category_id')->relationship(
                    name: 'category',
                    titleAttribute: 'name',
                    modifyQueryUsing:function(Builder $query, callable $get) {
                        $departmentId = $get('department_id');
                        if ($departmentId) {
                            $query->where('department_id', $departmentId);
                        }
                        return $query;
                    }
                )->preload()
                    ->searchable()
                    ->required(),
                RichEditor::make('description')
                    ->required()
                    ->label('Product Description')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'bulletList',
                        'orderedList',
                        'link',
                        'codeBlock',
                        'blockquote',
                        'redo',
                        'undo',
                    ])->columnSpan(2)
                    ->nullable(),
                TextInput::make('price')
                    ->label('Product Price')
                    ->required()
                    ->numeric()
                    ->rules(['regex:/^\d+(\.\d{1,2})?$/'])
                    ->helperText('Enter a valid price (e.g., 99.99)')
                    ->columnSpan(1),
                TextInput::make('quantity')
                    ->label('Product Quantity')
                    ->required()
                    ->numeric()
                    ->rules(['integer', 'min:0'])
                    ->helperText('Enter a valid quantity (e.g., 10)')
                    ->columnSpan(1),
                Select::make('status')
                    ->label('Product Status')
                    ->options([ProductStatusEnum::labels()])->default(ProductStatusEnum::DRAFT->value)->required()
                ,
                SpatieMediaLibraryFileUpload::make('images')
                    ->label('Product Images')
                    ->collection('products')
                    ->disk('public')
                    ->multiple()
                    ->image()
                    ->reorderable()
                    ->openable()
                    ->downloadable()
                    ->responsiveImages()
                    ->columnSpanFull()
                    ->helperText('Upload product images. You can drag to reorder. Changes save when you click Save.')
                    ,
                Repeater::make('variation_types')
                    ->label('Variation Types')
                    ->helperText('Define variation types (e.g. Color, Size) and their options.')
                    ->collapsed()
                    ->cloneable()
                    ->reorderable()
                    ->defaultItems(0)
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? 'New Type')
                    ->schema([
                        ComponentsTextInput::make('name')
                            ->label('Type Name')
                            ->required()
                            ->placeholder('e.g. Color, Size'),
                        ComponentsSelect::make('types')
                            ->label('Type')
                            ->options(ProductVariationTypesEnum::labels())
                            ->required()
                            ->placeholder('Select variation type')
                            ->searchable(),
                        Repeater::make('options')
                            ->label('Options')
                            ->collapsed()
                            ->reorderable()
                            ->defaultItems(0)
                            ->itemLabel(fn(array $state): ?string => $state['name'] ?? 'Option')
                            ->schema([
                                ComponentsTextInput::make('name')->label('Option Name')->required()->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('images')
                                    ->label('Images')
                                    ->collection('images')
                                    ->multiple()
                                    ->image()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->appendFiles()
                                    ->openable()
                                    ->preserveFilenames()
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('Add Option'),
                    ])
                    ->addActionLabel('Add Variation Type')
                    ->columnSpanFull()
            ]);
    }
}

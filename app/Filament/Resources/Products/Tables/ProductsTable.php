<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                SpatieMediaLibraryImageColumn::make('images')
                    ->label('Image')
                    ->collection('products')
                    ->conversion('thumb')
                    ->circular()
                    ->height(50)
                    ->width(50)
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->limit(1)
                    ->columnSpan(1),
                TextColumn::make('title')
                    ->label('Product Title')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap()
                    ->tooltip(fn ($record) => $record->title)
                    ->columnSpan(2),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors(ProductStatusEnum::colors())
                    ->sortable()
                    ->toggleable()
                    ->columnSpan(1),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap()
                    ->tooltip(fn ($record) => $record->department?->name)
                    ->columnSpan(1),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap()
                    ->tooltip(fn ($record) => $record->category?->name)
                    ->columnSpan(1),
                TextColumn::make('is_featured')
                    ->label('Featured')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => (bool)$state,
                        'gray' => fn($state) => !(bool)$state,
                    ])
                    ->toggleable(),
                TextColumn::make('offered_price')
                    ->label('Offer')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->is_offered && $record->offered_price !== null
                            ? number_format((float)$record->offered_price, 2)
                            : '-';
                    })
                    ->toggleable(),
                    TextColumn::make('created_at')->dateTime('M d, Y')
            ])
            ->filters([
                //
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ProductStatusEnum::labels()),
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

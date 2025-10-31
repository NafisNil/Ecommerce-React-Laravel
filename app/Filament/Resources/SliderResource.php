<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use App\RolesEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Slider as SliderModel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-photo';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Content';
    }

    public static function getNavigationSort(): ?int
    {
        return 50;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('title')->maxLength(255),
            Forms\Components\TextInput::make('subtitle')->maxLength(255),
            Forms\Components\TextInput::make('link_url')->url()->maxLength(255)->nullable(),
            Forms\Components\FileUpload::make('image_path')
                ->label('Image')
                ->image()
                ->directory('sliders')
                ->disk('public')
                ->required(),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->label('Image')->square(),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('subtitle')->limit(40),
                Tables\Columns\TextColumn::make('link_url')->limit(30),
                Tables\Columns\ToggleColumn::make('active')->sortable(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('actions')
                    ->label('Actions')
                    ->html()
                    ->state(function ($record) {
                        $editUrl = static::getUrl('edit', ['record' => $record]);
                        $deleteUrl = route('admin.sliders.destroy', $record);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');
                        return <<<HTML
                            <a href="{$editUrl}" class="text-primary hover:underline">Edit</a>
                            <form action="{$deleteUrl}" method="POST" style="display:inline" onsubmit="return confirm('Delete this slide?')">
                                {$csrf}
                                {$method}
                                <button type="submit" class="text-error ml-3">Delete</button>
                            </form>
                        HTML;
                    }),
            ])->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->recordUrl(fn (SliderModel $record) => static::getUrl('edit', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        
        $user = Filament::auth()->user();
        return $user && $user->hasRole(RolesEnum::Admin);
    }
}

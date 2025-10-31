<?php

namespace App\Filament\Resources;

use App\Enums\CouponTypeEnum;
use App\Filament\Resources\CouponResource\Pages\CreateCoupon;
use App\Filament\Resources\CouponResource\Pages\EditCoupon;
use App\Filament\Resources\CouponResource\Pages\ListCoupons;
use App\Models\Coupon;
use App\RolesEnum;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Coupon Code')->unique(ignoreRecord: true)->required()->live(onBlur: true),
            Select::make('type')->label('Type')->options(CouponTypeEnum::labels())->required()->reactive(),
            TextInput::make('value')->label('Value')->numeric()->rules(['regex:/^\\d+(\\.\\d{1,2})?$/'])->helperText('Percentage (0-100) for Percentage type; Fixed amount for Fixed type')->required(),
            Toggle::make('active')->label('Active')->default(true),
            TextInput::make('min_order')->label('Minimum Order')->numeric()->rules(['nullable','regex:/^\\d+(\\.\\d{1,2})?$/'])->helperText('Optional'),
            TextInput::make('usage_limit')->label('Usage Limit')->numeric()->rules(['nullable','integer','min:1'])->helperText('Leave empty for unlimited'),
            DateTimePicker::make('starts_at')->label('Starts At')->native(false),
            DateTimePicker::make('ends_at')->label('Ends At')->native(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Code')->searchable(),
            TextColumn::make('type')->label('Type')->badge(),
            TextColumn::make('value')->label('Value')->formatStateUsing(fn($state, $record) => $record->type === 'percentage' ? $state.'%' : number_format((float)$state, 2)),
            IconColumn::make('active')->label('Active')->boolean(),
            TextColumn::make('starts_at')->dateTime('M d, Y'),
            TextColumn::make('ends_at')->dateTime('M d, Y'),
        ])->recordActions([
            EditAction::make()
                ->visible(fn () => ! static::isVendor()),
            DeleteAction::make()
                ->visible(fn () => ! static::isVendor()),
        ])->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->visible(fn () => ! static::isVendor()),
            ])->visible(fn () => ! static::isVendor()),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }

    protected static function isVendor(): bool
    {
        $user = Auth::user();
        return $user && method_exists($user, 'hasRole')
            ? $user->hasRole(RolesEnum::Vendor->value)
            : false;
    }

    public static function canCreate(): bool
    {
        return ! static::isVendor();
    }

    public static function canEdit($record): bool
    {
        return ! static::isVendor();
    }

    public static function canDelete($record): bool
    {
        return ! static::isVendor();
    }

    public static function canDeleteAny(): bool
    {
        return ! static::isVendor();
    }
}

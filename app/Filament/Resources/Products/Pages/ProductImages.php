<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

/**
 * Deprecated: Replaced by embedding SpatieMediaLibraryFileUpload directly in the main Product form.
 * This page is retained temporarily for reference and can be safely deleted.
 */
class ProductImages extends Page
{
    use InteractsWithForms;

    public static ?string $title = 'Product Images';
    protected static string $resource = ProductResource::class;
    protected string $view = 'filament.resources.products.pages.product-images';

    protected Product $product; // Bound model
    public array $data = []; // Form state container

    public function mount($record): void
    {
        $this->product = Product::findOrFail($record);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                SpatieMediaLibraryFileUpload::make('images')
                    ->collection('products')
                    ->multiple()
                    ->label('Product Images')
                    ->image()
                    ->reorderable()
                    ->openable()
                    ->downloadable()
                    ->responsiveImages()
                    ->columnSpanFull(),
            ]);
    }

    protected function getForms(): array
    {
        return [
            'form',
        ];
    }

    public function getFormModel(): Model
    {
        return $this->product; // provides model binding context
    }

    public function submit(): void
    {
        try {
            $this->form->saveRelationships();
            Notification::make()
                ->title('Images uploaded successfully')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Image upload failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
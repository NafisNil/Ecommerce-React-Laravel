<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Manage Images</x-slot>
            <form wire:submit.prevent="submit" class="space-y-4">
                {{ $this->form }}
                <div>
                    <x-filament::button type="submit">Save Images</x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>

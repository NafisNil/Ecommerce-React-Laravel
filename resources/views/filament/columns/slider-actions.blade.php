@php
    /** @var \App\Models\Slider $record */
    $editUrl = \App\Filament\Resources\SliderResource::getUrl('edit', ['record' => $record]);
@endphp
<div class="flex items-center gap-3">
    <a href="{{ $editUrl }}" class="text-primary hover:underline">Edit</a>
    <form action="{{ route('admin.sliders.destroy', $record) }}" method="POST" onsubmit="return confirm('Delete this slide?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-error hover:underline">Delete</button>
    </form>
</div>

@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="filament-url-image-uploader">
        {{ $getChildComponentContainer() }}

        @php
            $state = $field->getState();
            $record = $field->getRecord();
            
            // Handle different data types
            $imageData = $record ? (
                is_string($record->{$field->getName()}) 
                    ? json_decode($record->{$field->getName()}, true) 
                    : $record->{$field->getName()}
            ) : null;
            
            // Handle both new uploads and existing images
            if (is_array($state) && !empty($state['image'])) {
                $uploadedImage = Storage::disk('public')->url($field->getDirectory() . '/' . $state['image'][0]);
            } else {
                $uploadedImage = $imageData['preview_url'] ?? null;
            }
        @endphp
        
        @if($uploadedImage)
            <div class="mt-2 flex items-center gap-2">
                <div class="relative group">
                    <img src="{{ $uploadedImage }}" 
                         alt="Image Preview" 
                         class="w-32 h-32 object-contain rounded-lg shadow-sm transition-all duration-300 group-hover:brightness-90" />
                    <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg">
                        <span class="text-white text-sm">Preview</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-dynamic-component>
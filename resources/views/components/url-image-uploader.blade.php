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
                    ? $record->{$field->getName()}
                    : ($record->{$field->getName()} ?? null)
            ) : null;
            
            // Handle both new uploads and existing images
            $uploadedImage = null;
            if (!empty($state)) {
                if (is_array($state)) {
                    if (isset($state['image'])) {
                        $filename = is_array($state['image']) ? ($state['image'][0] ?? '') : $state['image'];
                    } else {
                        $filename = is_array($state) ? ($state[0] ?? '') : $state;
                    }
                } else {
                    $filename = $state;
                }
                
                if (!empty($filename)) {
                    $uploadedImage = Storage::disk('public')->url( $filename);
                }
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
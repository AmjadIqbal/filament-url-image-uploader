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
            
            // Handle different data types and sources
            $uploadedImage = null;
            
            if (is_array($state) && isset($state['image']) && !empty($state['image_url']) ) {
                // Handle new uploads
                if (is_array($state)) {
                    $filename = is_array($state['image'] ?? null) 
                        ? ($state['image'][0] ?? '') 
                        : ($state['image'] ?? ($state[0] ?? ''));
                } else {
                    $filename = $state;
                }
                
                if (!empty($filename)) {
                    $uploadedImage = Storage::disk('public')->url($filename);
                }
            } elseif ($record && $record->{$field->getName()}) {
                // Handle existing database record
                $existingImage = $record->{$field->getName()};
             
                if (filter_var($existingImage, FILTER_VALIDATE_URL)) {
                    $uploadedImage = $existingImage;
                } else {
                    $uploadedImage = Storage::disk('public')->url($existingImage);
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
@if($getState())
    <div class="flex items-center space-x-2">
        <img src="{{ Storage::disk('public')->url($this->directory . '/' . $getState()) }}" 
             alt="Image Preview" 
             class="w-32 h-32 object-cover rounded-lg shadow-sm" />
    </div>
@endif
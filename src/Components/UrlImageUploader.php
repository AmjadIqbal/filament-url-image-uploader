<?php

namespace AmjadIqbal\FilamentUrlImageUploader\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\ViewField;
use Illuminate\Support\Facades\Storage;

class UrlImageUploader extends Field
{
    protected string $view = 'filament-url-image-uploader::components.url-image-uploader';
    protected string $directory = 'images';
    protected bool $shouldPreserveFilenames = false;

    public function directory(string $directory): static
    {
        $this->directory = $directory;
        return $this;
    }

    public function preserveFilenames(bool $condition = true): static
    {
        $this->shouldPreserveFilenames = $condition;
        return $this;
    }

    // Add this method to access the protected directory property
    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getChildComponents(): array
    {
        return [
            Tabs::make('upload_methods')
                ->tabs([
                    Tabs\Tab::make('upload')
                        ->label('File Upload')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->schema([
                            FileUpload::make('image')
                                ->image()
                                ->preserveFilenames($this->shouldPreserveFilenames)
                                ->directory($this->directory)
                                ->disk('public')
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if (!empty($state)) {
                                      

                                        if ($state instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                            $filename = $state->getFilename();
                                            
                                            // Store file directly in the public directory
                                            Storage::disk('public')->putFileAs(
                                                $this->directory,
                                                $state,
                                                $filename
                                            );

                                        } else {
                                            $filename = is_array($state) ? $state[0] : $state;
                                        }

                                        $set('image', [$filename]);
                                        $set('preview_url', asset("storage/{$this->directory}/{$filename}"));
                                    }
                                }),
                        ]),
                    Tabs\Tab::make('url')
                        ->label('URL Upload')
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            TextInput::make('image_url')
                                ->url()
                                ->helperText('Enter a valid image URL')
                                ->dehydrated(false)
                                ->live(),  // Make it live to update preview
                            Actions::make([
                                Actions\Action::make('fetch_image')
                                    ->label('Fetch Image')
                                    ->action(function (Set $set, $state, $action) {
                                        $imageUrl = $state['image_url'] ?? null;
                                   
                                        if (!$imageUrl || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                                            Notification::make()
                                                ->danger()
                                                ->title('Invalid URL')
                                                ->body('Please enter a valid image URL')
                                                ->send();
                                            return;
                                        }
                                        
                                        try {
                                            $ch = curl_init($imageUrl);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                            curl_setopt($ch, CURLOPT_HEADER, true);
                                            curl_setopt($ch, CURLOPT_NOBODY, true);
                                            curl_exec($ch);
                                            
                                            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                                            curl_close($ch);
                                            
                                            if (!str_starts_with($contentType, 'image/')) {
                                                throw new \Exception('URL does not point to a valid image');
                                            }
                                    
                                            $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
                                            $tempImage = file_get_contents($imageUrl);
                                            
                                            if (!$tempImage) {
                                                throw new \Exception('Could not fetch image from URL');
                                            }
                                        
                                            $directory = storage_path("app/public/{$this->directory}");
                                            
                                            if (!file_exists($directory)) {
                                                mkdir($directory, 0755, true);
                                            }
                                        
                                            $tempPath = $directory . '/' . $filename;
                                            file_put_contents($tempPath, $tempImage);
                                            
                                            $set('image', [$filename]);
                                            $set('preview_url', asset("storage/{$this->directory}/{$filename}"));
                                            
                                            Notification::make()
                                                ->success()
                                                ->title('Image fetched successfully')
                                                ->send();
                                        } catch (\Exception $e) {
                                            Notification::make()
                                                ->danger()
                                                ->title('Failed to fetch image')
                                                ->body($e->getMessage())
                                                ->send();
                                        }
                                    })
                            ]),
                        ]),
                ])->columnSpanFull(),
        ];
    }
}
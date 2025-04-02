<?php

namespace AmjadIqbal\FilamentUrlImageUploader\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Tabs;
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
        $fieldName = $this->getName();

        return [
            Tabs::make('upload_methods')
                ->tabs([
                    Tabs\Tab::make('upload')
                        ->label('File Upload')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->schema([
                            FileUpload::make($fieldName)
                                ->image()
                                ->preserveFilenames($this->shouldPreserveFilenames)
                                ->directory($this->directory)
                                ->disk('public')
                                ->afterStateHydrated(function (Field $component, $state) {
                                    if ($record = $component->getRecord()) {
                                        $value = $record->{$component->getName()};
                                        
                                        if (is_string($value)) {
                                            $component->state([$value]);
                                        } elseif (is_array($value)) {
                                            $component->state(array_filter($value));
                                        }
                                    }
                                })
                                ->afterStateUpdated(function ($state, Set $set) use ($fieldName) {
                                    if ($state) {
                                        $uploadedFile = $state ?? null;
                                        if ($uploadedFile instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                            $filename = $uploadedFile->getClientOriginalName();
                                            
                                            // Save file to storage
                                            $path = $uploadedFile->storeAs(
                                                $this->directory,
                                                $filename,
                                                'public'
                                            );
                                            
                                            $url = Storage::disk('public')->url($path);
                                            $set("{$fieldName}", [$path]);
                                            $set("{$fieldName}_url", $url);
                                        }
                                    }
                                })
                        ]),
                    Tabs\Tab::make('url')
                        ->label('URL Upload')
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            TextInput::make("{$fieldName}_url")
                                ->url()
                                ->helperText('Enter a valid image URL'),
                            Actions::make([
                                Actions\Action::make('fetch')
                                    ->label('Fetch Image')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->action(function (Set $set, $state) use ($fieldName) {
                                        $imageUrl = $state["{$fieldName}_url"];
                                        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                                            return;
                                        }

                                        try {
                                            $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
                                            $tempImage = file_get_contents($imageUrl);
                                            
                                            if (!$tempImage) {
                                                throw new \Exception('Could not fetch image');
                                            }
                                            
                                            Storage::disk('public')->put(
                                                "{$this->directory}/{$filename}",
                                                $tempImage
                                            );
                                            
                                            $url = Storage::disk('public')->url("{$this->directory}/{$filename}");
                                            $set("{$fieldName}", ["{$this->directory}/{$filename}"]);
                                            $set("{$fieldName}_url", $url);
                                            
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
                            ])
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
}
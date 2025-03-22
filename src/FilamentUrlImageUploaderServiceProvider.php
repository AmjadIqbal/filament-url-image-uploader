<?php

namespace AmjadIqbal\FilamentUrlImageUploader;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use AmjadIqbal\FilamentUrlImageUploader\Components\UrlImageUploader;

class FilamentUrlImageUploaderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-url-image-uploader')
            ->hasViews()
            ->hasViewComponent('filament-url-image-uploader', UrlImageUploader::class);
    }

    public function packageBooted(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'filament-url-image-uploader'
        );
    }
}
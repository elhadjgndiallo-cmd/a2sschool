<?php

namespace App\Listeners;

use App\Events\ImageUploaded;
use App\Helpers\ImageSyncHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncImageToPublic
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ImageUploaded $event): void
    {
        // Synchroniser l'image vers public/storage
        ImageSyncHelper::syncImage($event->imagePath);
    }
}

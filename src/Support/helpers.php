<?php

use Illuminate\Filesystem\Filesystem as File;
use Illuminate\Support\Facades\Storage;

if(!function_exists('downloadFile')) {
    function downloadFile($document)
    {
        $file = (new File);
        if (
            !empty(trim($document->storagePath()))
            && !empty(trim($document->url()))
            && !$file->exists(Storage::disk('public')->path($document->storagePath().'/'.$document->basename()))
        ) {
            if(!$file->isDirectory(Storage::disk('public')->path($document->storagePath()))) {
                $file->makeDirectory(Storage::disk('public')->path($document->storagePath()), 0775, true, true);
            }

            $path = $document->storagePath().'/'.$document->basename();
            $file->copy($document->url(), Storage::disk('public')->path($path));
            $document->setFilePath($path);
        }
    }
}
<?php

namespace App\Lib;

class DownloadFile
{
    public static function download($image)
    {
        $general = gs();
        $filePath = fileUrl($image->file);
        $extension = getExtension($filePath);
        
        if ($general->storage_type == 1) {
            $fileName  = $general->site_name . '_' . $image->track_id . '.' . $extension;

            $headers = [
                'Content-Type' => 'application/octet-stream',
                'Cache-Control' => 'no-store, no-cache'
            ];

            file_get_contents($filePath);

            return response()->download($filePath, $fileName, $headers);
        } else {
            $extension = getExtension($filePath);

            $fileName =  $general->site_name . '_' . $image->track_id . '.' . $extension;

            header('Content-type: application/octet-stream');
            header("Content-Disposition: attachment; filename=" . $fileName);

            while (ob_get_level()) {
                ob_end_clean();
            }
            readfile($filePath);
        }
    }
}

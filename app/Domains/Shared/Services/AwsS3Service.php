<?php

namespace Domains\Shared\Services;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class AwsS3Service
{
    public function uploadBase64ImageToS3(string $base64String, string $path, ?string $existingFileReference = null)
    {
        preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches);
        $imageExtension = $matches[1] ?? 'jpg';
        $allowedExtensions = ['jpeg', 'jpg', 'png'];

        if (!in_array($imageExtension, $allowedExtensions)) {
            return null;
        }

        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64String));

        $fileName = Str::uuid()->toString() . '.' . $imageExtension;

        $amazonS3 = Storage::disk('s3');
        try {
            if ($existingFileReference && $amazonS3->exists($path . $existingFileReference)) {
                $amazonS3->delete($path . $existingFileReference);
            }
            Storage::disk('s3')->put($path  . $fileName, $imageData, 'public');
            return $fileName;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function generateTemporaryUrl(string $path, string $fileName, int $expirationInMinutes = 5)
    {
        return Storage::disk('s3')->temporaryUrl("$path/$fileName", now()->addMinutes($expirationInMinutes));
    }
}

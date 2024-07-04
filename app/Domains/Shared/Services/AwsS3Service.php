<?php

namespace App\Domains\Shared\Services;

use Aws\Exception\AwsException;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AwsS3Service
{
    private S3Client $s3Client;
    private string $bucket;

    public function __construct()
    {
        $this->bucket = env('AWS_BUCKET');
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    private function generateFileName(string $extension): string
    {
        return Str::uuid()->toString() . '.' . $extension;
    }

    public function uploadBase64ImageToS3(string $base64String, string $path, ?string $existingFileReference = null)
    {
        try {
            preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches);
            $imageExtension = $matches[1] ?? 'jpg';
            $allowedExtensions = ['jpeg', 'jpg', 'png'];

            if (!in_array($imageExtension, $allowedExtensions)) {
                return null;
            }

            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64String));
            $fileName = $this->generateFileName($imageExtension);

            if (!is_null($existingFileReference)) {
                $this->deleteFromS3($path, $existingFileReference);
            }
            Storage::disk('s3')->put($path  . $fileName, $imageData, 'public');
            return $fileName;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function uploadFromS3(UploadedFile $file, string $arquivoAtual = null, string $caminhoS3 = null): string
    {
        try {
            #$this->deleteFromS3($caminhoS3, $arquivoAtual);

            $extensaoArquivo = $file->getClientOriginalExtension();
            $nome = $this->generateFileName($extensaoArquivo);
            $uploader = new MultipartUploader($this->s3Client, $file->getRealPath(), [
                'bucket' => $this->bucket,
                'key'    => $caminhoS3 . $nome,
                'acl'    => 'public-read'
            ]);
            $uploader->upload();
            return $nome;
        } catch (AwsException  $e) {
            return "Erro ao enviar o arquivo: " . $e->getMessage();
        }
    }

    public function deleteFromS3(string $caminhoS3, string $arquivo): void
    {
        try {
            $doesExist = $this->s3Client->doesObjectExist($this->bucket, $caminhoS3 . $arquivo);

            if ($doesExist) {
                $this->s3Client->deleteObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $caminhoS3 . $arquivo
                ]);
            }
        } catch (AwsException $e) {
            echo "Erro ao excluir o arquivo: " . $e->getMessage();
        }
    }

    public static function getSignedUrl(string $caminhoS3, string $arquivo, int $expirationInMinutes = 5): string
    {
        try {
            $service = new self();

            $cmd = $service->s3Client->getCommand('GetObject', [
                'Bucket' => $service->bucket,
                'Key'    => $caminhoS3 . $arquivo
            ]);

            $request = $service->s3Client->createPresignedRequest($cmd, '+' . $expirationInMinutes . ' minutes', [
                'ResponseContentDisposition' => 'inline; filename="' . $arquivo . '"'
            ]);

            // Get the actual presigned-url
            return (string)$request->getUri();
        } catch (AwsException $e) {
            return "Erro ao obter URL assinado: " . $e->getMessage();
        }
    }

    public static function getFileFromS3(string $caminhoS3, string $arquivo): string
    {
        try {
            $service = new self();

            $result = $service->s3Client->getObject([
                'Bucket' => $service->bucket,
                'Key'    => $caminhoS3 . $arquivo
            ]);

            return $result['Body'];
        } catch (AwsException $e) {
            return "Erro ao obter arquivo do S3: " . $e->getMessage();
        }
    }

    public static function getFileSignedFromS3(string $caminhoS3, string $arquivo, int $expirationInMinutes = 5): string
    {
        try {
            $service = new self();

            $cmd = $service->s3Client->getCommand('GetObject', [
                'Bucket' => $service->bucket,
                'Key'    => $caminhoS3 . $arquivo
            ]);

            $request = $service->s3Client->createPresignedRequest($cmd, '+' . $expirationInMinutes . ' minutes', [
                'ResponseContentDisposition' => 'inline; filename="' . $arquivo . '"'
            ]);

            // Get the actual presigned-url
            return (string)$request->getUri();
        } catch (AwsException $e) {
            return "Erro ao obter URL assinado: " . $e->getMessage();
        }
    }
}

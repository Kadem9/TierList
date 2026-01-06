<?php

namespace App\Infrastructure\Service\Storage;

use App\Domain\Port\PdfStorageInterface;
use Aws\S3\S3Client;

class MinioS3Adapter implements PdfStorageInterface
{
    private S3Client $s3Client;
    private string $bucket;

    public function __construct(
        string $endpoint,
        string $key,
        string $secret,
        string $bucket
    ) {
        $this->bucket = $bucket;

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
        ]);
    }

    public function store(string $filename, string $content): string
    {
        $result = $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $filename,
            'Body' => $content,
            'ContentType' => 'application/pdf',
        ]);

        return $result->get('ObjectURL') ?? $this->s3Client->getObjectUrl($this->bucket, $filename);
    }
}


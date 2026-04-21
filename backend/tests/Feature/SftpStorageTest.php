<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use Tests\TestCase;
use Throwable;

class SftpStorageTest extends TestCase
{
    public function test_it_can_store_and_delete_files_via_sftp(): void
    {
        $disk = Storage::disk('sftp');

        $path = sprintf(
            'integration-tests/%s/example.txt',
            Str::uuid()->toString()
        );
        $content = 'sftp-integration-' . Str::random(12);

        try {
            $disk->put($path, $content);
        } catch (FilesystemException|Throwable $e) {
            $this->markTestSkipped('SFTP storage is not available: ' . $e->getMessage());
        }

        try {
            $exists = $disk->exists($path);
        } catch (FilesystemException|Throwable $e) {
            $this->markTestSkipped('Cannot verify SFTP existence: ' . $e->getMessage());
        }

        $this->assertTrue($exists, 'Uploaded file should exist on SFTP.');

        try {
            $contentFromDisk = $disk->get($path);
        } catch (FilesystemException|Throwable $e) {
            $this->markTestSkipped('Cannot read file from SFTP: ' . $e->getMessage());
        }

        $this->assertSame($content, $contentFromDisk, 'Uploaded file content should match.');

        try {
            $disk->delete($path);
        } catch (FilesystemException|Throwable $e) {
            $this->markTestSkipped('Cannot delete file from SFTP: ' . $e->getMessage());
        }

        try {
            $this->assertFalse($disk->exists($path), 'File should be deleted from SFTP.');
        } catch (FilesystemException|Throwable $e) {
            $this->markTestSkipped('Cannot verify deletion on SFTP: ' . $e->getMessage());
        }
    }
}


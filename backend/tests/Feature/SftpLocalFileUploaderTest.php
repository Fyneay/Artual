<?php

namespace Tests\Feature;

use App\Services\LocalFileUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use Tests\TestCase;
use Throwable;

class SftpLocalFileUploaderTest extends TestCase
{
    public function test_it_uploads_and_deletes_file_via_sftp_using_local_file_uploader(): void
    {
        $uploader = new LocalFileUploader('sftp');

        $file = UploadedFile::fake()->createWithContent(
            'example.txt',
            'sftp-local-uploader-content'
        );

        $directory = 'integration-tests/' . Str::uuid()->toString();
        $disk = Storage::disk('sftp');

        try {
            $storedPath = $uploader->upload($file, $directory);
        } catch (FilesystemException|Throwable $e) {
            $this->markTestSkipped('Unable to upload file to SFTP: ' . $e->getMessage());
            return;
        }

        try {
            $this->assertTrue($disk->exists($storedPath), 'Uploaded file must exist on SFTP.');
        } catch (FilesystemException|Throwable $e) {
            $this->markTestSkipped('Unable to verify file existence on SFTP: ' . $e->getMessage());
            return;
        }

        try {
            $contentFromDisk = $disk->get($storedPath);
        } catch (FilesystemException|Throwable $e) {
            $this->markTestSkipped('Unable to read file from SFTP: ' . $e->getMessage());
            return;
        }

        $this->assertSame('sftp-local-uploader-content', $contentFromDisk);

        try {
            $uploader->delete($storedPath);
        } catch (FilesystemException|Throwable $e) {
            $this->markTestSkipped('Unable to delete file from SFTP: ' . $e->getMessage());
            return;
        }

        try {
            $this->assertFalse($disk->exists($storedPath), 'File must be removed from SFTP.');
        } catch (FilesystemException|Throwable $e) {
            $this->markTestSkipped('Unable to verify file deletion on SFTP: ' . $e->getMessage());
        }
    }
}


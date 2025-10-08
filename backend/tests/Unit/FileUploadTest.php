<?php

namespace Tests\Unit;

use App\Services\LocalFileUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use  Tests\TestCase;

class FileUploadTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    protected LocalFileUploader $uploader;

    public function setUp(): void
    {
        parent::setUp();
        $this->uploader = new LocalFileUploader();
        Storage::fake('local');
    }

    public function test_upload_file(): void
    {

        $file1 = UploadedFile::fake()->image('image.jpg');
        $file2= UploadedFile::fake()->create('doc.pdf', 2048, 'application/pdf');

        $fileUpload1 = $this->uploader->upload($file1, 'test');
        $fileUpload2 = $this->uploader->upload($file2, 'documents/sections/1');

        Storage::disk('local')->assertCount('/test',1);
        Storage::disk('local')->assertMissing('test/'. $file1->getClientOriginalName());
        Storage::disk('local')->assertExists($fileUpload1);
        Storage::disk('local')->assertCount('/documents/sections/1',1);
        Storage::disk('local')->assertMissing('documents/sections/1/'. $file2->getClientOriginalName());
        Storage::disk('local')->assertExists($fileUpload2);
    }

    public function test_delete_file() :void
    {
        $file1 = UploadedFile::fake()->image('image.jpg');
        $file2 = UploadedFile::fake()->image('super.png');
        $fileUpload1 = $this->uploader->upload($file1, 'profile');
        $fileUpload2 = $this->uploader->upload($file2, 'profile');
        $this->uploader->delete($fileUpload1);
        Storage::disk('local')->assertCount('/profile',1);
        Storage::disk('local')->assertExists($fileUpload2);

    }
}

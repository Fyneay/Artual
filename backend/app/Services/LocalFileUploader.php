<?php

namespace App\Services;


use App\Classes\FileUpload;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalFileUploader extends FileUpload
{
    private string $storageDriver;

    public function __construct(string $storageDriver = 'local')
    {
        $this->storageDriver = $storageDriver;
    }


    protected function generateName(UploadedFile $file) : string
    {
        $extension = $file->getClientOriginalExtension();
        $baseCode = Str::uuid()->toString();

        if ($extension !== '') {
            $extension = '.' . ltrim($extension, '.');
        }

        return $baseCode . $extension;
    }

    public function upload(UploadedFile $file, string $path) : string
    {
        $fileName = $this->generateName($file);

        return $this->disk()->putFileAs($path, $file, $fileName);
    }

    public function delete(string $path) : bool
    {
        return $this->disk()->delete($path);
    }

    public function getUrlFile(string $path): string
    {
        return $this->disk()->url($path);
    }

    private function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->storageDriver);

        return $disk;
    }







}

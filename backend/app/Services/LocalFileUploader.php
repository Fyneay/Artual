<?php

namespace App\Services;


use App\Classes\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalFileUploader extends FileUpload
{
    private string $storageDriver = 'local';


    protected function generateName(UploadedFile $file) : string
    {
        $extension = $file->getClientOriginalExtension();
        $baseCode = Str::uuid()->toString();

        return $baseCode . $extension ;
    }

    public function upload(UploadedFile $file, string $path) : string
    {
        $fileName = $this->generateName($file);

        return Storage::disk($this->storageDriver)->putFileAs($path, $file, $fileName);
    }

    public function delete(string $path) : bool
    {
        return Storage::disk($this->storageDriver)->delete($path);
    }

    public function getUrlFile(string $path): string
    {
        return Storage::disk($this->storageDriver)->url($path);
    }







}

<?php

namespace App\Classes;

use Illuminate\Http\UploadedFile;

abstract class FileUpload
{
    abstract public function upload(UploadedFile $file, string $path) : string;

    abstract public function delete(string $path) : bool;

    abstract public function getUrlFile(string $path) : string;


}

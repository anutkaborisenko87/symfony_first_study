<?php

namespace App\Utils\Inertfaces;

interface UploaderInterface
{
    public function upload($file);
    public function delete($path);
}
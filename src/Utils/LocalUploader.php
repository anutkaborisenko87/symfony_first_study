<?php

namespace App\Utils;

use App\Utils\Inertfaces\UploaderInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class LocalUploader implements UploaderInterface
{
    private $targetDirectory;
    public $file;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * @param $file
     * @return mixed
     */
    public function upload($file)
    {
        $video_number = random_int(1, 10000000);
        $fileName = $video_number . '.' . $file->getClientOriginalExtension();
        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $exception) {

        }
        $orig_file_name = $this->clear(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        return [$fileName, $orig_file_name];
    }

    /**
     * @param $path
     * @return mixed
     */
    public function delete($path)
    {
        $fileSystem = new Filesystem();
        try {
            $fileSystem->remove('.'. $path);
        } catch (IOException $exception) {
            echo "An error occurred while deleting your file at " . $exception->getPath();
        }
        return true;
    }

    private function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    private function clear(string $string)
    {
        return preg_replace('/[^A-Za-z0-9- ]+/', '', $string);
    }
}
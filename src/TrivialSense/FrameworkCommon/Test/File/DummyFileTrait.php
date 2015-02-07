<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Test\File;

use Symfony\Component\HttpFoundation\File\File;

trait DummyFileTrait
{
    /**
     * @var File
     */
    protected $originalFile;

    public function __destruct()
    {
        if($this->originalFile)
            @unlink($this->originalFile->getPathname());

        @unlink($this->getPathname());
    }

    public function setOriginalFile(File $file)
    {
        $this->originalFile = $file;
    }

    public static function createFromFile(File $file)
    {
        $dummyFile = new DummyUploadedFile($file->getPathname(), $file->getPathname());

        $dummyFile->setOriginalFile($file);

        return $dummyFile;
    }
}

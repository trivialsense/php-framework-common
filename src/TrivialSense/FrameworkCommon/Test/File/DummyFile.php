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

class DummyFile extends File
{
    use DummyFileTrait;
}

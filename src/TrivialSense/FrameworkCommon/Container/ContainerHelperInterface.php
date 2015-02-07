<?php

/*
 * This file is part of php-framework-common
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace TrivialSense\FrameworkCommon\Container;

interface ContainerHelperInterface
{
    /**
     * Gets a service by id or name
     *
     * @param $id
     *
     * @return object
     */
    public function get($id);

    /**
     * Gets a parameter
     *
     * @param $name
     * @return mixed
     */
    public function parameter($name);
}

<?php

/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors. test bootstrap
 *
 * @link        https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2016 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */
define('PHPOFFICE_BASE_PATH', __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
PhpOffice_Autoloader::Register();

class PhpOffice_Autoloader {

    public static function Register() {
        if (function_exists('__autoload')) {
            //Register any existing autoloader function with SPL, so we don't get any clashes
            spl_autoload_register('__autoload');
        }
        //    Register ourselves with SPL
        return spl_autoload_register(array('PhpOffice_Autoloader', 'Load'));
    }

    static function Load($class) {
        $class = ltrim($class, '\\');
        $prefix = 'PhpOffice\\';
        if (strpos($class, $prefix) === 0) {
            $file = PHPOFFICE_BASE_PATH . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

}

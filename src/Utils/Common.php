<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2/13/2019
 * Time: 6:23 AM
 */

namespace Saas\Installer\Utils;


class Common
{
    public static function isRunningAsRoot(){
        return posix_getuid() == 0;
    }

    public static function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }

    public static function getPhpVersion(){
        $version = explode('.', PHP_VERSION);
        return $version[0].'.'.$version[1];
    }
}
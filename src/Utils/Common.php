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

    public static function operating_system_detection(){
        if (false == function_exists("shell_exec") || false == is_readable("/etc/os-release")) {
            return null;
        }

        $os         = shell_exec('cat /etc/os-release');
        $listIds    = preg_match_all('/.*=/', $os, $matchListIds);
        $listIds    = $matchListIds[0];

        $listVal    = preg_match_all('/=.*/', $os, $matchListVal);
        $listVal    = $matchListVal[0];

        array_walk($listIds, function(&$v, $k){
            $v = strtolower(str_replace('=', '', $v));
        });

        array_walk($listVal, function(&$v, $k){
            $v = preg_replace('/=|"/', '', $v);
        });

        return array_combine($listIds, $listVal);
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    public static function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" composer.phar';
        }
        return 'composer';
    }

    public static  function setEnvironmentValue($envFile, array $values)
    {
        $str = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {

                $str .= "\n"; // In case the searched variable is in the last line without \n
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}={$envValue}\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                }

            }
        }

        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) return false;
        return true;
    }

    public static function isAbsolutePath($path) {
        if (!is_string($path)) {
            $mess = sprintf('String expected but was given %s', gettype($path));
            throw new \InvalidArgumentException($mess);
        }
        if (!ctype_print($path)) {
            $mess = 'Path can NOT have non-printable characters or be empty';
            throw new \DomainException($mess);
        }
        // Optional wrapper(s).
        $regExp = '%^(?<wrappers>(?:[[:print:]]{2,}://)*)';
        // Optional root prefix.
        $regExp .= '(?<root>(?:[[:alpha:]]:/|/)?)';
        // Actual path.
        $regExp .= '(?<path>(?:[[:print:]]*))$%';
        $parts = [];
        if (!preg_match($regExp, $path, $parts)) {
            $mess = sprintf('Path is NOT valid, was given %s', $path);
            throw new \DomainException($mess);
        }
        if ('' !== $parts['root']) {
            return true;
        }
        return false;
    }
}
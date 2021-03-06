<?php

/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 5/2/2016
 * Time: 11:59 PM
 */
namespace Common\Util;

class Directory {

    /**
     * @param $directory
     * @param $extension
     * @return array
     */
    public static function scan($directory, $extension){
        $files = scandir($directory);
        $results = array();
        foreach($files as $key => $value){
            $path = realpath($directory . DIRECTORY_SEPARATOR . $value);
            if(!is_dir($path)) {
                if(preg_match('/.'.$extension.'$/', $path)) {
                    $results[] = $path;
                }
            }
            else if($value != "." && $value != "..") {
                $results = array_merge($results, self::scan($path, $extension));
            }
        }

        return $results;
    }

    /**
     * @param $path
     * @param $mode
     * @throws \InvalidArgumentException
     */
    public static function make($path, $mode) {
        $success = mkdir($path, $mode, true);
        if(!$success) {
            throw new \InvalidArgumentException('Could not create directory with ' . $mode . 'mode, under "' . $path . '" path.');
        }
    }

    /**
     * @param $path
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function validatePath($path) {
        $realPath = realpath($path);
        if($realPath === false) {
            throw new \InvalidArgumentException('Invalid path "' . $path . '".');
        }

        return $realPath;
    }
}
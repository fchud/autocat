<?php

namespace fchud {

    class Classloader {

        public function __construct() {
            
        }

        public static function autoload($f) {
            global $docRoot;

            $file = \str_replace('\\', '/', $f);
            $path = $docRoot . '/lib';
            $filepath = $docRoot . '/lib/' . $file . '.php';

            if (\file_exists($filepath)) {
                require_once($filepath);
            } else {
                $flag = true;
                static::recursive_autoload($file, $path, $flag);
            }
        }

        public static function recursive_autoload($file, $path, &$flag) {
            if (FALSE !== ($handle = \opendir($path)) && $flag) {
                while (FAlSE !== ($dir = \readdir($handle)) && $flag) {

                    if (\strpos($dir, '.') === FALSE) {
                        $path2 = $path . '/' . $dir;
                        $filepath = $path2 . '/' . $file . '.php';
                        if (\file_exists($filepath)) {
                            $flag = FALSE;
                            require_once($filepath);
                            break;
                        }
                        static::recursive_autoload($file, $path2, $flag);
                    }
                }
                \closedir($handle);
            }
        }

    }

    \spl_autoload_register(__NAMESPACE__ . '\Classloader::autoload');
}
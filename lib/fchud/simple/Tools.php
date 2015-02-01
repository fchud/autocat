<?php

namespace fchud\simple;

use fchud\simple\Debug as debug;

/**
 * helper class
 * 
 * provides some usefull functions
 */
class Tools {

    /**
     * converts string size measured in bytes, using shorthand notations (K, M, G, T, P) to integer
     * 
     * @param string $value size measured in bytes, using shorthand notations (K, M, G, T, P)
     * @return int size in bytes
     */
    public static function size_bytes($value) {
        $val = trim($value);
        $last = strtolower($val[strlen($val) - 1]);

        switch ($last) {
            case 'p':
                $val *= 1024;
            case 't':
                $val *= 1024;
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * does some magick to help generate flattend $_FILES array.
     * 
     * used only by flattenFilesArray() method
     * 
     * @param array $newFiles target array
     * @param array $subArray sub array
     * @param string $domain path so far
     * @param string $group initial 'name', 'type', 'tmp_name', 'error', 'size' group
     */
    private static function newFilesArray(&$newFiles, $subArray, $domain, $group) {
        foreach ($subArray as $key => $val) {
            $addDom = "{$domain}[{$key}]";
            if (is_array($val)) {
                self::newFilesArray($newFiles, $val, $addDom, $group);
            } else {
                $newFiles[$domain][$key][$group] = $val;                        // $newFiles[$addDom][$group] = $val;
            }
        }
    }

    /**
     * flattens $_FILES array
     * 
     * @param array $files $_FILES
     * @return array tidy and clean
     */
    public static function flattenFilesArray($files) {
        $newFiles = [];
        foreach ($files as $domain => $fileSet) {
            foreach ($fileSet as $group => $subArray) {
                self::newFilesArray($newFiles, $subArray, $domain, $group);
            }
        }
        return $newFiles;
    }

    /**
     * replaces [back]slashes and adds trailing DIRECTORY_SEPARATOR
     * 
     * @param string $path path-like
     * @return string path with system-specific separators
     */
    public static function formatPath($path) {
        if (!$path) {
            return $path;
        }

        $ds = DIRECTORY_SEPARATOR;
        $newPath = trim(str_replace(['\\', '/'], $ds, $path), $ds) . $ds;

        return $newPath;
    }

    /**
     * replaces back slashes with slashes and adds a trailing one
     * 
     * mainly used to convert directory path to url path
     * 
     * @param string $path directory-like path
     * @return string path with slashes as separators
     */
    public static function formatUrl($path) {
        if (!$path) {
            return $path;
        }
        $replace = [
            '\\' => '/',
        ];

        $newUrl = strtr(self::formatPath($path), $replace);
        return $newUrl;
    }

    /**
     * convert special characters to HTML entities for string or array of strings
     * 
     * @param string|array $object not safe string or array of strings
     * @return string|array html-safe string or array of strings
     */
    public static function safeString($object) {
        if (!is_array($object)) {
            return htmlspecialchars($object);
        }

        $temp = [];
        foreach ($object as $key => $val) {
            $temp[$key] = htmlspecialchars($val);
        }
        return $temp;
    }

    /**
     * does some routine to prepare list of pages
     * 
     * used only by paginate() method
     * 
     * @param array $pages all listed and max elements
     * @return array list of actual elements
     */
    private static function pagiHelp($pages) {
        $lst = $pages['lst'];
        $max = $pages['max'];

        $new[] = 1;
        if ($max > 1) {
            foreach ($lst as $i) {
                $new[] = $i;
            }
            $new[] = $max;
        }

        $all = [];
        $len = strlen($max);
        foreach ($new as $i => $v) {
            $all[] = str_pad($v, $len, 0, STR_PAD_LEFT);
        }

        $c = count($all);
        if ($c > 2) {
            $a = ($all[1] == 2) ? '' : '...';
            array_splice($all, 1, 0, $a);
            $b = ($all[$c - 1] == $all[$c] - 1) ? '' : '...';
            array_splice($all, $c, 0, $b);
        }

        return $all;
    }

    /**
     * create array of items to use for pagination
     * 
     * all values are zero-filled to match the length of the last element
     * 
     * @param int $totalItems total amount of items
     * @param int $perPage amount of items per page
     * @param int $currentPage current page
     * @param int $visiblePages amount of visible pages
     * @return array specific pages
     */
    public static function paginate($totalItems, $perPage = 5, $currentPage = 0, $visiblePages = 5) {
        $maxPages = ceil($totalItems / $perPage);

        $pages['max'] = $maxPages;
        $pages['lst'] = [];

        if ($maxPages > 2) {
            $allPages = range(2, $maxPages - 1);

            $length = floor($visiblePages / 2) * 2 + 1;
            $offset = max(0, min(count($allPages) - $length, intval($currentPage) - ceil($length / 2)));

            $pages['lst'] = array_slice($allPages, $offset, $length);
        }

        return self::pagihelp($pages);
    }

}

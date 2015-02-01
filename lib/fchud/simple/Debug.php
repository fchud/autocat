<?php

namespace fchud\simple;

defined('SITE_ENV') or define('SITE_ENV', 'prod');

/**
 * helper class
 * 
 * privides static methods for storing and displaying data
 */
class Debug {

    /**
     * @var array stores raw data of [['head' => 'title', 'body' => data], ...] elements
     */
    private static $storedRaw = [];

    /**
     * returns title for add*() and show*() methods
     * 
     * returns filename and line where call of add*() and show*() methods was made;
     * or $name and $setLine, if those defined.
     * 
     * @param string $name used to set title
     * @param string $setLine used to set line. paired with $name.
     * @return string "$name [.$setLine]" OR "filename .line"
     */
    private static function getCaller($name, $setLine = null) {
        if ($name !== '') {
            return $name . ($setLine ? " .{$setLine}" : '');
        }

        $file = '';
        $method = '';
        $trace = debug_backtrace();
        foreach ($trace as $call) {
            if ((isset($call['class']) && ($call['class'] !== __CLASS__)) || !isset($call['class'])) {
                break;
            }
            $file = $call['file'];
            $getLine = $call['line'];
        }
        $caler = $method ? $method : basename($file);
        $caler .= ' .';
        $caler .= $setLine ? $setLine : $getLine;

        return $caler;
    }

    /**
     * basically a setter for the self::$storedRaw
     * 
     * @param array $set [['head' => 'title', 'body' => data], ...]
     */
    private static function store($set) {
        self::$storedRaw[] = $set;
    }

    /**
     * clears stored array
     */
    public static function clean() {
        self::$storedRaw = [];
    }

    /**
     * generates HTML view of stored/provided data
     * 
     * @param array $raw [optional] use instead of stored data
     * @return string HTML-formatted
     */
    public static function getHTML($raw = []) {
        $rawArray = ($raw !== []) ? $raw : self::$storedRaw;

        $html = '';
        foreach ($rawArray as $rawItem) {
            $out = '';
            foreach ($rawItem as $item) {
                $out .= (strlen($out) > 0) ? "<br />" : '';
                $out .= "<i>[" . htmlspecialchars($item['head']) . "]</i>:<br />\n";
                $out .= "<div style='border: 1px  dotted #808080;'>" . htmlspecialchars($item['body']) . "</div>";
            }
            $html .= "<hr /><pre>{$out}</pre><hr />\n";
        }
        return $html;
    }

    /**
     * flushes stored data
     * 
     * generates HTML, sends to browser and cleans stored data
     */
    public static function flush() {
        echo self::getHTML();

        self::clean();
    }

    /**
     * get HTML-view and clear stored data
     * 
     * @return string HTML-formatted
     */
    public static function getHTMLClean() {
        $out = self::getHTML();

        self::clean();

        return $out;
    }

    /**
     * prepare data to be stored or displayed
     * 
     * @param mixed $object any data to be stored/dysplayed
     * @param string $name title. if empty, filename and line will be used
     * @param bool $debug if TRUE 'var_dump' will be used to represent $object,
     *  'print_r' otherwise
     * @return string contains title and human-readable information about a $object
     */
    private static function prepare($object, $name, $debug) {
        $out = $debug ? 'var_dump' : 'print_r';

        $raw = [];
        $head = self::getCaller($name);

        $raw[0]['head'] = $head;
        ob_start();
        $out($object);
        $raw[0]['body'] = ob_get_clean();

        return $raw;
    }

    /**
     * prepare Exception data to be stored or displayed
     * 
     * @param \Exception $ex exception to be stored or displayed
     * @param bool $debug [optional, default: false] if TRUE, adds Exception stack trace
     * @return array contains filename, line, Exception message and trace(if needed)
     */
    private static function prepareEx(\Exception $ex, $debug = false) {
        $raw = [];
        $raw[0]['head'] = 'exception';
        $raw[0]['body'] = $ex->getMessage();
        if ((SITE_ENV === 'debug') || ($debug !== false)) {
            $raw[0]['head'] .= ' @ ' . self::getCaller(basename($ex->getFile()), $ex->getLine());
            $raw[1]['head'] = 'trace';
            $raw[1]['body'] = $ex->getTraceAsString();
        }

        return $raw;
    }

    /**
     * add any data to be stored in human-readable format
     * 
     * print_r is used to represent data
     * 
     * @param mixed $object data to be stored
     * @param string $name [optional, default: ''] set title to be associated with object.
     *  if not specified, filename and line of caller will be used.
     */
    public static function add($object, $name = '') {
        $raw = self::prepare($object, $name, false);
        self::store($raw);
    }

    /**
     * add any data to be stored in human-readable format
     * 
     * var_dump is used to represent data
     * 
     * @param mixed $object data to be stored
     * @param string $name [optional, default: ''] set title to be associated with object.
     *  if not specified, filename and line of caller will be used.
     */
    public static function addX($object, $name = '') {
        $raw = self::prepare($object, $name, true);
        self::store($raw);
    }

    /**
     * add Exception to be stored
     * 
     * @param Exception $ex Exception to store
     * @param bool $debug [optional, default: false] if TRUE adds Exception stack trace
     */
    public static function addEx(\Exception $ex, $debug = false) {
        $raw = self::prepareEx($ex, $debug);
        self::store($raw);
    }

    /**
     * display any data in human-readable format
     * 
     * print_r is used to represent data
     * 
     * @param mixed $object data to be displayed
     * @param string $name [optional, default: ''] set title to be associated with object.
     *  if not specified, filename and line of caller will be used.
     */
    public static function show($object, $name = '') {
        $raw = self::prepare($object, $name, false);
        echo self::getHTML([$raw]);
    }

    /**
     * display any data in human-readable format
     * 
     * var_dump is used to represent data
     * 
     * @param mixed $object data to be displayed
     * @param string $name [optional, default: ''] set title to be associated with object.
     *  if not specified, filename and line of caller will be used.
     */
    public static function showX($object, $name = '') {
        $raw = self::prepare($object, $name, true);
        echo self::getHTML([$raw]);
    }

    /**
     * show Exception information
     * 
     * @param Exception $ex Exception to display
     * @param bool $debug [optional, default: false] if TRUE adds Exception stack trace
     */
    public static function showEx(\Exception $ex, $debug = false) {
        $raw = self::prepareEx($ex, $debug);
        echo self::getHTML([$raw]);
    }

    /**
     * print human-readable information about a variable
     * 
     * @param mixed $object
     * @param bool $debug [optional, default: false] if TRUE 'var_dump' is used to represent data,
     *  'print_r' otherwise
     */
    public static function debug($object, $debug = false) {
        $out = $debug ? 'var_dump' : 'print_r';
        $out($object);
    }

}

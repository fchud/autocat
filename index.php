<?php

defined('SITE_ENV') or define('SITE_ENV', 'debug');

set_include_path(get_include_path() . PATH_SEPARATOR . 'lib');

require_once 'fchud/Classloader.php';

use fchud\simple\Debug as debug;
use fchud\simple\Tools as tools;

$settings = require_once('config/settings.php');
$mvc = $settings['mvcSet'];

$controller = tools::formatPath($mvc['mvcPath'])
        . tools::formatPath($mvc['controlersPath'])
        . $mvc['siteController'] . '.php';


try {
    require_once $controller;

    (new $mvc['siteController']($settings))->run();
} catch (Exception $ex) {
    debug::showEx($ex, true);
}
<?php

use fchud\simple\Debug as debug;

global $docRoot;

$loadJs = '';
$loadCss = '';

$moduleCss = 'css/' . $module . '.css';
$moduleCssFile = "{$docRoot}/{$moduleCss}";

$moduleJs = 'js/' . $module . '.js';
$moduleJsFile = "{$docRoot}/{$moduleJs}";

if (file_exists($moduleCssFile)) {
    $loadCss .= "        <link rel='stylesheet' href='{$moduleCss}'>\n";
}
if (file_exists($moduleJsFile)) {
    $loadJs .= "        <script src='{$moduleJs}'></script>\n";
}

function genNavBar($navFile) {
    if (!file_exists($navFile)) {
        return "<a href='?'>index</a> <a href='?carAdd'>add car</a>";
    }

    ob_start();
    require_once $navFile;
    return ob_get_clean();
}

$navBar = genNavBar($this->layoutsPath . 'navLayout.php');
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel='stylesheet' href='css/hint.css'>
        <link rel='stylesheet' href='css/style.css'>
<?= $loadCss ?>
        <title>
            catalog
        </title>
        <script src='js/jquery-1.11.2.min.js'></script>
<?= $loadJs ?>
    </head>
    <body>
        <div class='header'>
<?= $navBar ?>
        </div>
        <div class='content'>
<?= debug::getHTMLClean() ?>
<?= $content ?>
        </div>
        <div class='footer'>
<?= $navBar ?>
        </div>
    </body>
</html>

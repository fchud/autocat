<?php

use fchud\simple\Debug as debug;

$loadJs = '';
$loadCss = '';
$documentRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');

$moduleCss = '/css/' . $module . '.css';
$moduleCssFile = $documentRoot . $moduleCss;

$moduleJs = '/js/' . $module . '.js';
$moduleJsFile = $documentRoot . $moduleJs;

if (file_exists($moduleCssFile)) {
    $loadCss .= "        <link rel='stylesheet' href='$moduleCss'>\n";
}
if (file_exists($moduleJsFile)) {
    $loadJs .= "        <script src='$moduleJs'></script>\n";
}

$navFile = $this->layoutsPath . 'navigation.html';
if (file_exists($navFile)) {
    $navBar = file_get_contents($navFile, true);
} else {
    $navBar = "<a href='/'>index</a> <a href='/?carAdd'>add car</a>";
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel='stylesheet' href='/css/hint.css'>
        <link rel='stylesheet' href='/css/style.css'>
        <?= $loadCss ?>
        <title>
            catalog
        </title>
        <script src='/js/jquery-1.11.2.min.js'></script>
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
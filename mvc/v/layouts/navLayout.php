<?php

use fchud\simple\Debug as debug;

function genNavLinks($data, $template) {
    $links = '';
    foreach ($data as $item) {
        $rep = [
            '[@url]' => $item[0],
            '[@name]' => $item[1],
            '[@pos]' => $item[2],
        ];
        $links .= strtr($template, $rep);
    }
    return $links;
}

/**
 * - 0: url
 * - 1: name
 * - 2: position
 */
$navUrls = [
    ['', 'index', 'left'],
    ['carAdd', 'add car', 'left'],
    ['createDb', 'wipe DB', 'right'],
];
$template = "   <div class='col [@pos]'><a href='?[@url]'>[@name]</a></div>\n";
$navLinks = genNavLinks($navUrls, $template);
?>
<div class='row nav'>
<?= $navLinks ?>
</div>

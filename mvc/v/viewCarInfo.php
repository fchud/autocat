<?php

use fchud\simple\Debug as debug;
use fchud\simple\Tools as tools;

$catId = $$module->getCatId();

$car = $$module->getCar();
$info = $car['info'];
$colors = $car['colors'];
$photos = $car['photos'];

function genHTML($dataSet, $template) {
    if (!is_array($dataSet)) {
        return;
    }

    $str = '';
    foreach ($dataSet as $item) {
        $rep = [
            '[@photo]' => $item['photo'],
            '[@thumb]' => $item['thumb'],
        ];
        $str .= strtr($template, $rep);
    }

    return $str;
}

$template = "            <a href='[@photo]' target='_blanc'><img src='[@thumb]'></a>\n";
$photoHTML = genHTML($photos, $template);
?>
<div class='container'>
    <div class='row info'>
        <div class='col left label'>
            id
        </div>
        <div class='col left data'>
            <?= $info['id'] ?>
        </div>
    </div>
    <div class='row info'>
        <div class='col left label'>
            brand
        </div>
        <div class='col left data'>
            <?= $info['brand'] ?>
        </div>
    </div>
    <div class='row info'>
        <div class='col left label'>
            model
        </div>
        <div class='col left data'>
            <?= $info['model'] ?>
        </div>
    </div>
    <div class='row info'>
        <div class='col left label'>
            body type
        </div>
        <div class='col left data'>
            <?= $info['body'] ?>
        </div>
    </div>
    <div class='row info'>
        <div class='col left label'>
            colors
        </div>
        <div class='col left data'>
            <?= $colors ?>
        </div>
    </div>
    <div class='row info'>
        <div class='col left label'>
            price
        </div>
        <div class='col left data'>
            <?= $info['price'] ?>
        </div>
    </div>
    <div class='row info'>
        <div class='col left label'>
            description
        </div>
        <div class='col left data'>
            <pre><?= $info['description'] ?></pre>
        </div>
    </div>
    <div class='row info'>
        <div class='col left label'>
            photo
        </div>
        <div class='col left data'>
            <?= $photoHTML ?>
        </div>
    </div>
</div>

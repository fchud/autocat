<?php

use fchud\simple\Debug as debug;
use fchud\simple\Tools as tools;

function genPages($carsCount, $currentPage) {
    $pages = tools::paginate($carsCount, 5, $currentPage, 15);

    $templateRow = "<div class='row page'>[@cells]</div>\n";
    $pageCell = "<div class='col left'><a href='?catIndex=[@catId]'>[@catId]</a></div>";
    $blankCell = "<div class='col left current'>[@catId]</div>";

    $allCells = '';
    foreach ($pages as $page) {
        if (is_numeric($page) && $page != $currentPage + 1) {
            $allCells .=str_replace('[@catId]', $page, $pageCell);
        } else {
            $allCells .=str_replace('[@catId]', $page, $blankCell);
        }
    }

    $pagesRow = str_replace('[@cells]', $allCells, $templateRow);
    return $pagesRow;
}

function genHTML($dataSet, $template) {
    if (!is_array($dataSet)) {
        return;
    }

    $str = '';
    foreach ($dataSet as $item) {
        $rep = [
            '[@id]' => $item['id'],
            '[@thumb]' => $item['thumb'],
            '[@model]' => $item['model'],
            '[@price]' => $item['price'],
            '[@descr]' => $item['descr'],
        ];
        $str .= strtr($template, $rep);
    }

    return $str;
}

$currentPage = $$module->getCatIndex();
$carsCount = $$module->getPages();
$pagesRow = genPages($carsCount, $currentPage);

$cars = $$module->getCars();
$template = "    <div class='row cat'>
        <a href='?carInfo=[@id]'>
            <div class='col left thumb'>
                <img src='[@thumb]' />
            </div>
            <div class='col left model'>
                [@model]
            </div>
            <div class='col left price'>
                [@price]
            </div>
            <div class='col left descr'>
                [@descr]
            </div>
        </a>
    </div>\n";
$catPage = genHTML($cars, $template);
?>
<div class='container'>
    <?= $pagesRow ?>
</div>
<div class='container'>
    <div class='row cat'>
        <div class='table__cell col left thumb'>
            &nbsp;
        </div>
        <div class='table__cell col left model'>
            brand, model
        </div>
        <div class='table__cell col left price'>
            price
        </div>
        <div class='table__cell col left descr'>
            descr
        </div>
    </div>
    <?= $catPage ?>
</div>
<div class='container'>
    <?= $pagesRow ?>
</div>

<?php

use fchud\simple\Tools as tools;

$postMaxSize = tools::size_bytes(ini_get('post_max_size'));
$maxFileSize = tools::size_bytes(ini_get('upload_max_filesize'));
$maxFiles = ini_get('max_file_uploads');
$postSetings = "$postMaxSize, $maxFileSize, $maxFiles";

$postErrors = $$module->getErrors();
$formErrors = isset($postErrors['form']) ? json_encode($postErrors['form']) : '{}';

/*
 * todo:
 * create Generator class
 * ...
 * or use some template engine [smarty?]
 */

function getTags($template) {
    preg_match_all('/(\[@(\w+)\])/', $template, $match);

    return ['old' => $match[1], 'tag' => $match[2], 'rep' => array_combine($match[2], $match[1])];
}

function genHTML($dataSet, $template, $args = []) {
    $tags = getTags($template);
    $result = '';

    foreach ($dataSet as $row) {
        $tTags = $tags;
        foreach ($tTags['tag'] as $tag) {
            if (isset($row[$tag])) {
                $tTags['rep'][$tag] = $row[$tag];
            }
        }

        foreach ($args as $arg) {
            if (isset($arg['sub']) && isset($tTags['rep'][$arg['sub']])) {
                if (isset($arg['def'])) {
                    $tTags['rep'][$arg['sub']] = $arg['def'];
                }
                foreach ($arg['cond'] as $cond) {
                    if (isset($cond['tag']) && isset($tTags['rep'][$cond['tag']])) {
                        $needle = $tTags['rep'][$cond['tag']];
                        if (isset($cond['val']) && in_array($needle, $cond['val'])) {
                            $tTags['rep'][$arg['sub']] = $arg['val'];
                        }
                    }
                }
            }
        }
        $t = array_combine($tTags['old'], $tTags['rep']);
        $result .= strtr($template, $t);
    }

    return $result;
}

$template = "                    <option value='[@id]'[@sel]>[@name]</option>\n";
$args[] = [
    'sub' => 'sel',
    'def' => '',
    'cond' => [
        [
            'tag' => 'id',
            'val' => $$module->getPost('colors'),
        ],
    ],
    'val' => ' selected',
];

$colors = $$module->getSome('colors');
$colorsHTML = genHTML($colors, $template, $args);
?>
<script>
<?= $module ?>Lib.init([<?= $postSetings ?>], <?= $formErrors ?>);
</script>
<div class='container'>
    <form action='<?= filter_input(INPUT_SERVER, 'REQUEST_URI') ?>' method='post' enctype='multipart/form-data'>
        <div class='row add'>
            <div class='col left label'>
                brand
            </div>
            <div class='col left info'>
                <span id='brandInfo' class='hint--top hint--bounce hint--info' data-hint='required'>*</span>
            </div>
            <div class='col left form'>
                <input type='text' id='brand' name='<?= $module ?>[brand]' placeholder='brand' value='<?= $$module->getPostString('brand') ?>'>
            </div>
        </div>
        <div class='row add'>
            <div class='col left label'>
                model
            </div>
            <div class='col left info'>
                <span id='modelInfo' class='hint--top hint--bounce hint--info' data-hint='required'>*</span>
            </div>
            <div class='col left form'>
                <input type='text' id='model' name='<?= $module ?>[model]' placeholder='model' value='<?= $$module->getPostString('model') ?>'>
            </div>
        </div>
        <div class='row add'>
            <div class='col left label'>
                price
            </div>
            <div class='col left info'>
                <span id='priceInfo' class='hint--top hint--bounce hint--info' data-hint='required'>*</span>
            </div>
            <div class='col left form'>
                <input type='text' id='price' name='<?= $module ?>[price]' placeholder='price' value='<?= $$module->getPostString('price') ?>'>
            </div>
        </div>
        <div class='row add'>
            <div class='col left label'>
                colors
            </div>
            <div class='col left info'>
                <span id='colorsInfo' class='hint--top hint--bounce hint--info' data-hint='required'>*</span>
            </div>
            <div class='col left form'>
                <select id='colors' name='<?= $module ?>[colors][]' multiple>
                    <?= $colorsHTML ?>
                </select>
            </div>
        </div>
        <div class='row add'>
            <div class='col left label'>
                body type
            </div>
            <div class='col left info'>
                &nbsp;
            </div>
            <div class='col left form'>
                <input type='text' id='body' name='<?= $module ?>[body]' placeholder='body type' value='<?= $$module->getPostString('body') ?>'>
            </div>
        </div>
        <div class='row add'>
            <div class='col left label'>
                description
            </div>
            <div class='col left info'>
                &nbsp;
            </div>
            <div class='col left form'>
                <textarea id='description' name='<?= $module ?>[description]' placeholder='description'><?= $$module->getPostString('description') ?></textarea>
            </div>
        </div>
        <div class='row add'>
            <div class='col left label'>
                photos
            </div>
            <div class='col left info'>
                <span id='photoInfo' class='hint--top hint--bounce hint--error' data-tip='?'>&nbsp;</span>
            </div>
            <div class='col left form'>
                <input type='file' id='photo' name='<?= $module ?>[photos][]' accept='image/*'  multiple>
            </div>
        </div>
        <div class='row add'>
            <div class='col left label'>
                <input type='button' id='random' name='random' value='random'>
            </div>
            <div class='col left info'>
                &nbsp;
            </div>
            <div class='col left form'>
                <input type='submit' name='<?= $module . '[' . $module . ']' ?>' value='add car'>
            </div>
        </div>
    </form>
</div>

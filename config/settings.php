<?php

$settings = [
    'dbSet' => require_once(__DIR__ . '/db.php'),
    'mvcSet' => require_once(__DIR__ . '/mvc.php'),
    'defaultModule' => 'catIndex',
    'images' => 'img',
    'nophoto' => 'blank.png',
    'uploads' => 'uploads',
    'photo' => 'photo',
    'thumbs' => 'thumbs',
];

return $settings;

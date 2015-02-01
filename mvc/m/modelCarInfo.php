<?php

use fchud\simple\DataBase as db;
use fchud\simple\Debug as debug;
use fchud\simple\Tools as tools;

class CarInfo {

    private $carId = null;
    private $dbSet = [];
    private $photoPath = 'uploads/photo';
    private $thumbsPath = 'uploads/thumbs';
    private $pdo;

    public function __construct($data = []) {
        try {
            $this->carId = $data['carId'];
            $this->dbSet = $data['dbSet'];
            $this->photoPath = $data['photoPath'] . tools::formatPath($this->carId);
            $this->thumbsPath = $data['thumbsPath'] . tools::formatPath($this->carId);

            $this->pdo = new db($this->dbSet);
        } catch (Exception $ex) {
            debug::showEx($ex);
        }
    }

    public function __destruct() {
        $this->pdo = null;
    }

    public function getCatId() {
        return $this->carId;
    }

    private function getCarInfo() {
        if (!$this->carId) {
            return;
        }

        $cols = [
            'cat.cat_id AS id',
            'brand.name AS brand',
            'model.name AS model',
            'body.name AS body',
            'cat.price',
            'cat.description',
        ];
        $tables = [
            'catalog cat',
            'dict_brand brand',
            'dict_model model',
            'dict_body body',
        ];
        $conds = [
            'brand.brand_id = model.brand_id',
            'model.model_id = cat.model_id',
            'body.body_id = cat.body_id',
            'cat.cat_id = ' . $this->carId,
        ];

        try {
            return $this->pdo->select($tables, $cols, $conds)[0];
        } catch (Exception $ex) {
            debug::addEx($ex);
        }
    }

    private function getCarColors() {
        if (!$this->carId) {
            return ['unknown'];
        }

        $cols = [
            'dco.name',
        ];
        $tables = [
            'dict_color dco',
            'catcolor cco',
            'catalog cat',
        ];
        $conds = [
            'dco.color_id = cco.color_id',
            'cco.cat_id = cat.cat_id',
            'cat.cat_id = ' . $this->carId,
        ];
        $options = [
            'ORDER BY dco.name ASC',
        ];

        try {
            $colorRows = $this->pdo->select($tables, $cols, $conds, $options);
            if (is_array($colorRows)) {
                $colors = array_column($colorRows, 'name');
            } else {
                $colors = [];
            }

            return $colors;
        } catch (Exception $ex) {
            debug::addEx($ex);
        }
    }

    private function getCarPhotos() {
        if (!is_dir($this->thumbsPath) || !is_dir($this->photoPath) || !$this->carId) {
            return;
        }

        try {
            $photos = [];
            $handle = opendir($this->thumbsPath);
            if ($handle !== false) {
                while (($thumb = readdir($handle)) !== false) {
                    if ($thumb != '.' && $thumb != '..') {
                        $photo = tools::formatUrl($this->photoPath) . $thumb;
                        if (file_exists($photo)) {
                            $photos[] = [
                                'photo' => $photo,
                                'thumb' => tools::formatUrl($this->thumbsPath) . $thumb,
                            ];
                        }
                    }
                }
                closedir($handle);
            }

            return $photos;
        } catch (Exception $ex) {
            debug::addEx($ex);
        }
    }

    public function getCar() {
        $carInfo = tools::safeString($this->getCarInfo());
        $carColor = $this->getCarColors();
        $carPhoto = $this->getCarPhotos();

        $car = [
            'info' => [
                'id' => isset($carInfo['id']) ? $carInfo['id'] : 'unknown',
                'brand' => isset($carInfo['brand']) ? $carInfo['brand'] : 'unknown',
                'model' => isset($carInfo['model']) ? $carInfo['model'] : 'unknown',
                'body' => isset($carInfo['body']) ? $carInfo['body'] : '',
                'price' => isset($carInfo['price']) ? $carInfo['price'] : 'unknown',
                'description' => isset($carInfo['description']) ? $carInfo['description'] : '',
            ],
            'colors' => $carColor ? implode(', ', $carColor) : 'unknown',
            'photos' => $carPhoto ? $carPhoto : [],
        ];

        return $car;
    }

    public function run() {
        
    }

}

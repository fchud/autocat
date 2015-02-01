<?php

use fchud\simple\DataBase as db;
use fchud\simple\Debug as debug;
use fchud\simple\Tools as tools;

class CatIndex {

    private $catIndex = 0;
    private $dbSet = [];
    private $thumbsPath = 'uploads/thumbs';
    private $blankImage = 'img/blank.png';
    private $pdo;

    public function __construct($data = []) {
        try {
            $this->dbSet = $data['dbSet'];
            $this->blankImage = $data['nophoto'];
            $this->thumbsPath = $data['thumbsPath'];
            $this->catIndex = (!empty($data['catId']) && $data['catId'] > 0) ? $data['catId'] - 1 : 0;

            $this->pdo = new db($this->dbSet);
        } catch (Exception $ex) {
            debug::addEx($ex);
        }
    }

    public function __destruct() {
        $this->pdo = null;
    }

    public function getCatIndex() {
        return $this->catIndex;
    }

    public function getPages() {
        $cols = [
            'count(*) AS count',
        ];
        $tables = [
            'catalog',
        ];

        try {
            return $this->pdo->select($tables, $cols)[0]['count'];
        } catch (Exception $ex) {
            debug::addEx($ex);
        }
    }

    private function getCatPage() {
        $amount = 5;
        $start = $this->catIndex * $amount;
        $cols = [
            'cat.cat_id as id',
            "CONCAT_WS(', ', brand.name, model.name) AS model",
            'cat.price AS price',
            'LEFT(description, 500) AS descr',
        ];
        $tables = [
            'catalog cat',
            'dict_brand brand',
            'dict_model model',
        ];
        $conds = [
            'model.brand_id = brand.brand_id',
            'cat.model_id = model.model_id',
        ];
        $opts = [
            'ORDER BY cat.cat_id DESC',
            "LIMIT {$start} , {$amount}",
        ];

        try {
            return $this->pdo->select($tables, $cols, $conds, $opts);
        } catch (Exception $ex) {
            debug::addEx($ex);
        }
    }

    private function getThumb($carId) {
        $thumbs = $this->thumbsPath . tools::formatPath($carId);
        try {
            if (file_exists($thumbs)) {
                $handle = opendir($thumbs);
                if ($handle !== false) {
                    while (($photo = readdir($handle)) !== false) {
                        if (($photo != '.') && ($photo != '..')) {
                            closedir($handle);
                            return tools::formatUrl($thumbs) . $photo;
                        }
                    }
                    closedir($handle);
                }
            }
        } catch (Exception $ex) {
            debug::addEx($ex);
        }

        return $this->blankImage;
    }

    public function getCars() {
        $cars = $this->getCatPage();
        try {
            foreach ($cars as &$car) {
                $car['id'] = !empty($car['id']) ? $car['id'] : 'unknown';
                $car['model'] = !empty($car['model']) ? $car['model'] : 'unknown';
                $car['price'] = !empty($car['price']) ? $car['price'] : 'unknown';
                $car['descr'] = !empty($car['descr']) ? $car['descr'] : '&nbsp;';
                $car['thumb'] = $this->getThumb($car['id']);
            }
        } catch (Exception $ex) {
            debug::addEx($ex);
        }

        return $cars;
    }

    public function run() {
//        debug::add('asd');
    }

}

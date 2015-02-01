<?php

use fchud\simple\DataBase as db;
use fchud\simple\Debug as debug;
use fchud\simple\Tools as tools;

class CarAdd {

    private $result = null;
    private $errors = [];
    private $post = null;
    private $files = [];
    private $photo = 'uploads/photo';
    private $thumbs = 'uploads/photo/thumbs';
    private $required = ['brand', 'model', 'price', 'colors'];
    private $dbSet = [];
    private $pdo;

    public function __construct($data = []) {
        $this->post = $data['post'];
        $this->files = tools::flattenFilesArray($data['files']);
        $this->dbSet = $data['dbSet'];
        $this->photo = $data['photo'];
        $this->thumbs = $data['thumbs'];

        try {
            $this->pdo = new db($this->dbSet);
        } catch (Exception $ex) {
            debug::showEx($ex);
        }
    }

    public function __destruct() {
        $this->pdo = null;
    }

    public function getResult() {
        return $this->result;
    }

    public function setResult($result) {
        $this->result = "carInfo={$result}";
    }

    public function getErrors() {
        return $this->errors;
    }

    public function addError($group, $id, $message = null) {
        if ($message === null) {
            $this->errors[$group][] = $id;
        } else {
            $this->errors[$group][$id] = $message;
        }
    }

    public function getPost($id) {
        return isset($this->post[$id]) ? $this->post[$id] : null;
    }

    public function getPostString($id) {
        $result = $this->getPost($id) ? $this->getPost($id) : '';

        return is_string($result) ? $result : json_encode($result);
    }

    public function getSome($ident, $args = []) {
        if ($ident === 'colors') {
            $statement = 'SELECT color_id AS id, name FROM dict_color ORDER BY name ASC';
        }

        return $this->pdo->getSome($statement, $args);
    }

    private function validatePost() {
        foreach ($this->required as $item) {
            if (!isset($this->post[$item]) || ($this->post[$item] === '')) {
                $this->addError('form', $item, 'required');
            }
        }
// some other checks
    }

    private function getMimeExt($mimeType) {
        switch ($mimeType) {
            case 'image/gif':
                $ext = 'gif';
                break;
            case 'image/jpeg':
            case 'image/pjpeg':
                $ext = 'jpg';
                break;
            case 'image/png':
            case 'image/x-png':
                $ext = 'png';
                break;
            default :
                return false;
        }

        return $ext;
    }

    private function validateFiles() {
        $uploadErrors = [
            1 => 'The uploaded file exceeds max filesize directive in php.ini [' . ini_get('upload_max_filesize') . ']',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
        ];


        $photos = [];
        try {
            if (!function_exists('finfo_open')) {
                throw new Exception("fileinfo extension is not available");
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            foreach ($this->files as $fileSet) {
                foreach ($fileSet as $file) {

                    if ($file['name'] === '') {
                        continue;
                    }
                    if ($file['error'] !== 0) {
                        $this->addError('upload', $file['name'], $file['error']);
                        continue;
                    }

                    $mimeType = finfo_file($finfo, $file['tmp_name']);
                    $ext = $this->getMimeExt($mimeType);
                    if (!$ext) {
                        $this->addError('files', 'mime', $file['name']);
                        continue;
                    }

                    $photos[] = [
                        'old' => $file['name'],
                        'ext' => $ext,
                        'uid' => uniqid(),
                        'tmp' => $file['tmp_name'],
                    ];
                }
            }
            finfo_close($finfo);
        } catch (Exception $ex) {
            $this->addError('ex', __FUNCTION__, $ex->getMessage());
            debug::addEx($ex);
        }
        $this->files = $photos;
    }

    private function validateData() {
        $this->validatePost();
        $this->validateFiles();

        return $this->getErrors();
    }

    private function addCarInfo() {
        try {
            $this->pdo->begin();

            $brand = ['name' => $this->post['brand']];
            $brandId = $this->pdo->insert('dict_brand', $brand, 'brand_id');

            $model = ['brand_id' => $brandId, 'name' => $this->post['model']];
            $modelId = $this->pdo->insert('dict_model', $model, 'model_id');

            $body = ['name' => $this->post['body']];
            $bodyId = $this->pdo->insert('dict_body', $body, 'body_id');

            $cat = [
                'model_id' => $modelId,
                'body_id' => $bodyId,
                'price' => $this->post['price'],
                'description' => $this->post['description'],
            ];
            $catId = str_pad($this->pdo->insert('catalog', $cat), 10, 0, STR_PAD_LEFT);

            foreach ($this->post['colors'] as $color) {
                $colors = ['cat_id' => $catId, 'color_id' => $color];
                $this->pdo->insert('catcolor', $colors);
            }

            $this->pdo->commit();
            return $catId;
        } catch (Exception $ex) {
            $this->pdo->cancel();
            $this->addError('ex', __FUNCTION__, $ex->getMessage());
            debug::addEx($ex);
        }
    }

    private function getSizeFactor($imgWidth, $imgHeight) {
        $maxW = 160;
        $maxH = 120;

        $xW = ($imgWidth / $maxW);
        $xH = ($imgHeight / $maxH);

        $x = ($xW > $xH) ? $xW : $xH;


        return ($x > 1) ? $x : 1;
    }

    private function createThumbnail($catId, $file) {
        try {
            if (!function_exists('gd_info')) {
                throw new Exception("something wrong with GD [used to generate thumbnails]");
            }

            $dir = $this->thumbs . tools::formatPath($catId);

            $pathArr = pathinfo($file);
            $name = $pathArr['basename'];
            $ext = $pathArr['extension'];
            $type = str_replace('jpg', 'jpeg', $ext);
            $thumb = "{$dir}{$name}";

            if (!is_dir($dir)) {
                mkdir($dir, 0644, true);
            }

            $createFrom = "imagecreatefrom{$type}";
            $saveAs = "image{$type}";

            list($imgWidth, $imgHeight) = getimagesize($file);

            if (!function_exists('exif_read_data')) {
                $this->addError('ext', 'exif', 'seems to be disabled');
            } else {
                $exif = exif_read_data($file);
            }
            $ort = isset($exif['Orientation']) ? $exif['Orientation'] : 1;
            if (($ort === 8) || ($ort === 6)) {
                $sizeFactor = $this->getSizeFactor($imgHeight, $imgWidth);
            } else {
                $sizeFactor = $this->getSizeFactor($imgWidth, $imgHeight);
            }

            $newWidth = round($imgWidth / $sizeFactor);
            $newHeight = round($imgHeight / $sizeFactor);

            $resource = imagecreatetruecolor($newWidth, $newHeight);
            $image = $createFrom($file);
            //imagecopyresampled
            imagecopyresized($resource, $image, 0, 0, 0, 0, $newWidth, $newHeight, $imgWidth, $imgHeight);

            switch ($ort) {
                case 8:
                    $resource = imagerotate($resource, 90, 0);
                    break;
                case 3:
                    $resource = imagerotate($resource, 180, 0);
                    break;
                case 6:
                    $resource = imagerotate($resource, -90, 0);
                    break;
            }

            $saveAs($resource, $thumb);
        } catch (Exception $ex) {
            $this->addError('ex', __FUNCTION__, $ex->getMessage());
            debug::addEx($ex);
        }
    }

    private function movePhotosFromTemp($catId) {
        try {
            if ($this->files === []) {
                return;
            }

            $dir = $this->photo . tools::formatPath($catId);
            if (!is_dir($dir)) {
                mkdir($dir, 0644, true);
            }

            foreach ($this->files as $photo) {
                $file = "{$dir}{$photo['uid']}.{$photo['ext']}";
                $result = move_uploaded_file($photo['tmp'], $file);

                if (!$result) {
                    $this->addError('files', 'move', $photo['old']);
                    continue;
                }

                $this->createThumbnail($catId, $file);
            }
        } catch (Exception $ex) {
            $this->addError('ex', __FUNCTION__, $ex->getMessage());
            debug::addEx($ex);
        }
    }

    public function run() {
        try {
            if (!is_array($this->post) || $this->validateData() !== []) {
                return;
            }

            $catId = $this->addCarInfo();

            if (!$catId) {
                throw new Exception('failed to add car info');
            }

            $this->movePhotosFromTemp($catId);

            $this->setResult($catId);
        } catch (Exception $ex) {
            $this->addError('ex', __FUNCTION__, $ex->getMessage());
            debug::addEx($ex);
        }
    }

}

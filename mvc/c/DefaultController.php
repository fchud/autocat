<?php

use fchud\simple\DataBase as db;
use fchud\simple\Debug as debug;
use fchud\simple\Tools as tools;

class DefaultController {

    private $pdo;
    private $requestUri;
    private $urlPath;
    private $urlQuery;
    private $reqMethod;
    private $dbSet;
    private $mvcSet;
    private $mvcPath;
    private $mPath;
    private $vPath;
    private $cPath;
    private $layoutsPath;
    private $siteLayout;
    private $defModule;
    private $imagesPath;
    private $blankImage;
    private $uploadsPath;
    private $photoPath;
    private $thumbsPath;

    public function __construct($settings) {
        $this->requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $this->urlPath = parse_url($this->requestUri, PHP_URL_PATH);
        $this->urlQuery = parse_url($this->requestUri, PHP_URL_QUERY);

        $this->reqMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

        $this->dbSet = $settings['dbSet'];
        $this->mvcSet = $settings['mvcSet'];
        $this->mvcPath = tools::formatPath($this->mvcSet['mvcPath']);
        $this->mPath = $this->mvcPath . tools::formatPath($this->mvcSet['modelsPath']);
        $this->vPath = $this->mvcPath . tools::formatPath($this->mvcSet['viewsPath']);
        $this->cPath = $this->mvcPath . tools::formatPath($this->mvcSet['controlersPath']);
        $this->layoutsPath = $this->vPath . tools::formatPath($this->mvcSet['layoutsPath']);
        $this->siteLayout = $this->layoutsPath . $this->mvcSet['siteLayout'];

        $this->defModule = ucfirst($settings['defaultModule']);
        $this->imagesPath = tools::formatPath($settings['images']);
        $this->blankImage = tools::formatUrl($settings['images']) . $settings['nophoto'];
        $this->uploadsPath = tools::formatPath($settings['uploads']);
        $this->photoPath = $this->uploadsPath . tools::formatPath($settings['photo']);
        $this->thumbsPath = $this->uploadsPath . tools::formatPath($settings['thumbs']);

        $this->validate();
    }

    private function checkDb() {
        $this->pdo = new db($this->dbSet);
        try {

            $statement = 'SELECT color_id AS id, name FROM dict_color ORDER BY name ASC';
            $result = $this->pdo->getSome($statement);

            if (empty($result)) {
                throw new Exception('empty colors');
            }
        } catch (Exception $ex) {
            $this->redirect('createDb');
        }
    }

    private function validate() {
        if (!file_exists($this->siteLayout)) {
            debug::show($this->siteLayout, 'not found');
            die;
        }
    }

    private function redirect($query) {
        $location = 'http://' . filter_input(INPUT_SERVER, 'SERVER_NAME') . $this->urlPath . '?' . $query;

        header("Location: $location");
        die;
    }

    private function useModel($name, $data = []) {
        if ($name !== 'createDb') {
            $this->checkDb();
        }

        $module = ucfirst($name);
        $file = "{$this->mPath}model{$module}.php";

        require_once($file);

        $model = new $module($data);
        $model->run();

        return $model;
    }

    private function renderLayout($content, $module) {
        require_once($this->siteLayout);
    }

    private function renderPhpFile($fileName, $data) {
        ob_start();
        extract($data);
        require_once($fileName);
        return ob_get_clean();
    }

    private function renderView($name, $data = []) {
        $data['module'] = $name;

        $content = $this->renderPhpFile("{$this->vPath}view{$name}.php", $data);

        $this->renderLayout($content, $name);
    }

    /* actions */

    public function actionCreateDb($name, $data) {
        $params = [
            'dbSet' => $this->dbSet,
        ];
        $model = $this->useModel($name, $params);
        $data[$name] = $model;
        $this->renderView($name, $data);
    }

    public function action404($uri) {
        debug::add($uri, '404: not found');
        $this->renderLayout('', '404');
    }

    public function actionCatIndex($name, $data) {
        $params = [
            'catId' => isset($data[$name]) ? $data[$name] : '',
            'dbSet' => $this->dbSet,
            'nophoto' => $this->blankImage,
            'thumbsPath' => $this->thumbsPath,
        ];
        $model = $this->useModel($name, $params);
        $data[$name] = $model;
        $data['thumbsPath'] = $this->thumbsPath;
        $this->renderView($name, $data);
    }

    public function actionCarInfo($name, $data) {
        $params = [
            'carId' => $data[$name],
            'dbSet' => $this->dbSet,
            'photoPath' => $this->photoPath,
            'thumbsPath' => $this->thumbsPath,
        ];
        $model = $this->useModel($name, $params);
        $data[$name] = $model;
        $data['photoPath'] = $this->photoPath;
        $data['thumbsPath'] = $this->thumbsPath;
        $this->renderView($name, $data);
    }

    public function actionCarAdd($name, $data) {
        $post = filter_input(INPUT_POST, $name, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $params = [
            'dbSet' => $this->dbSet,
            'post' => $post,
            'files' => $_FILES,
            'photo' => $this->photoPath,
            'thumbs' => $this->thumbsPath,
        ];

        $model = $this->useModel($name, $params);
        if ($model->getResult() !== null) {

            $this->redirect($model->getResult());
        } else {
            $data[$name] = $model;
            $this->renderView($name, $data);
        }
    }

    public function run() {
        try {
            parse_str($this->urlQuery, $data);

            foreach (array_keys($data) as $key) {
                $action = 'action' . ucfirst($key);

                if (method_exists(__CLASS__, $action)) {
                    $this->$action($key, $data);

                    return;
                }
            }

            $action = 'action' . $this->defModule;
            if (method_exists(__CLASS__, $action)) {
                $this->$action($this->defModule, $data);
            } else {
                $this->action404($this->requestUri);
            }

            /*
              $action = 'action404';
              $this->$action();
             * 
             */
        } catch (Exception $ex) {
            debug::showEx($ex, true);
        }
    }

}

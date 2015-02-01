<?php

use fchud\simple\DataBase as db;
use fchud\simple\Debug as debug;
use fchud\simple\Tools as tools;

class CreateDB {

    private $sqlPath = 'sql';
    private $subPath = 'mysql';
    private $wipeAll = 'wipeAll.sql';
    private $createAll = 'createAll.sql';
    private $fillAll = 'fillAll.sql';
    private $dbSet = [];
    private $pdo;
    private $result = '';

    public function __construct($data = []) {
        $this->sqlPath = tools::formatPath($this->sqlPath);
        $this->subPath = $this->sqlPath . tools::formatPath($this->subPath);
        $this->wipeAll = $this->subPath . $this->wipeAll;
        $this->createAll = $this->subPath . $this->createAll;
        $this->fillAll = $this->subPath . $this->fillAll;

        $this->dbSet = $data['dbSet'];

        try {
//            throw new Exception();
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

    public function run() {
        try {
            $wipeStatement = @file_get_contents($this->wipeAll, true);
            if (!$wipeStatement) {
                throw new Exception('error reading: ' . $this->wipeAll);
            }
            $this->pdo->execSql($wipeStatement);

            $createStatement = @file_get_contents($this->createAll, true);
            if (!$createStatement) {
                throw new Exception('error reading: ' . $this->createAll);
            }
            $this->pdo->execSql($createStatement);

            $fillStatement = @file_get_contents($this->fillAll, true);
            if (!$fillStatement) {
                throw new Exception('error reading: ' . $this->fillAll);
            }
            $this->pdo->execSql($fillStatement);
            $this->result = 'data base was recreated';
        } catch (Exception $ex) {
            debug::addEx($ex);
        }
    }

}

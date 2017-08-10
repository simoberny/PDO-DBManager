<?php
    define("DB_NAME", "root");
    define("DB_PASSWORD", "");

    class DB{

        private $col = 'mysql:host=localhost;dbname=cms';
        private $pdo;
        private $bConnected = false;
        private $sQuery;
        private $parameters;

        public function __construct(){
            $this->connect();
            $this->parameters = array();
        }


        private function connect(){
            try {
                $this->pdo = new PDO($this->col , DB_NAME, DB_PASSWORD, array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ));
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->bConnected = true;
            }
            catch(PDOException $e) {
                echo 'Attenzione: '.$e->getMessage();
                die();
            }
        }

        public function bind($para, $value)
        {
            $this->parameters[sizeof($this->parameters)] = [":" . $para , $value];
        }

        public function bindMore($parray)
        {
            if (empty($this->parameters) && is_array($parray)) {
                $columns = array_keys($parray);
                foreach ($columns as $i => &$column) {
                    $this->bind($column, $parray[$column]);
                }
            }
        }

        private function Init($query, $parameters = "")
        {
            if (!$this->bConnected) {
                $this->Connect();
            }
            try {
                $this->sQuery = $this->pdo->prepare($query);
                $this->bindMore($parameters);
                
                # Bind parameters
                if (!empty($this->parameters)) {
                    foreach ($this->parameters as $param => $value) {
                        if(is_int($value[1])) {
                            $type = PDO::PARAM_INT;
                        } else if(is_bool($value[1])) {
                            $type = PDO::PARAM_BOOL;
                        } else if(is_null($value[1])) {
                            $type = PDO::PARAM_NULL;
                        } else {
                            $type = PDO::PARAM_STR;
                        }
                        // Add type when binding the values to the column
                        $this->sQuery->bindValue($value[0], $value[1], $type);
                    }
                }

                $this->sQuery->execute();
            }
            catch (PDOException $e) {
                echo 'Attenzione: '.$e->getMessage();
                die();
            }
            
            $this->parameters = array();
        }

        public function query($query, $params = null){
            $this->Init($query, $params);

            try {
                $result = $this->sQuery->fetchAll();
            }catch (PDOException $e) {
                echo 'Attenzione: '.$e->getMessage();
                die();
            }

            return $result;
        }

        public function iud($query, $params = null){
            $this->Init($query, $params);

            try {
                $result = $this->sQuery->rowCount();
            }catch (PDOException $e) {
                echo 'Attenzione: '.$e->getMessage();
                die();
            }

            return $result;
        }

    }
?>
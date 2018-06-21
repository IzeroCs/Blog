<?php

    namespace Librarys\Database\Module;

    use Librarys\Database\Connect;
    use Librarys\Database\ModuleAbstract;
    use Librarys\Database\Query\PDO as Query;
    use Librarys\Database\Schema\PDO as Schema;

    class PDO extends ModuleAbstract
    {

        /**
         * @var string $error
         */
        private $error;

        /**
         * @param string $uri
         * @param string $host
         * @param string $user
         * @param string $pass
         * @param string $name
         * @param int|string $port
         */
        public function connect(
            $uri,
            $host,
            $user,
            $pass,
            $name,
            $port
        ) {
            $pdo = null;

            try {
                $pdo = new \PDO($uri . ':host=' . $host . ';dbname=' . $name . ';port=' . $port, $user, $pass);
            } catch (\PDOException $e) {
                $this->error = $e->getMessage();

                return false;
            }

            if ($pdo == null)
                return false;

            return $pdo;
        }

        /**
         * @return bool
         */
        public function disconnect()
        {
            return true;
        }

        /**
         * @return string
         */
        public function errorconnect()
        {
            return $this->error;
        }

        /**
         * @return null|string
         */
        public function error()
        {
            $error = Connect::getInstance()->getResource()->errorInfo();

            if (is_array($error) && count($error) >= 3)
                return $error[0] . $error[1] . $error[2];

            return null;
        }

        /**
         * @param resource|\PDOStatement $result
         */
        public function free($result)
        {
            if ($result instanceof \PDOStatement)
                $result->closeCursor();
        }

        public function query($sql)
        {
            return Connect::getInstance()->getResource()->query($sql);
        }

        public function rows($sql)
        {
            if (Connect::isResource($sql) == false)
                $sql = $this->query($sql);

            return $sql->rowCount();
        }

        public function assoc($sql)
        {
            if (Connect::isResource($sql) == false)
                $sql = $this->query($sql);

            return $sql->fetch(\PDO::FETCH_ASSOC);
        }

        public function insertId()
        {
            return Connect::getInstance()->getResource()->lastInsertId();
        }

        /**
         * @param string $uri
         * @return bool
         */
        public static function isSupport($uri)
        {
            if (defined('\PDO::ATTR_DRIVER_NAME')) {
                $drivers = \PDO::getAvailableDrivers();

                if (in_array($uri, $drivers))
                    return true;
            }

            return false;
        }

        /**
         * @return string
         */
        public static function getQueryClass()
        {
            return Query::class;
        }

        /**
         * @return string
         */
        public static function getSchemaClass()
        {
            return Schema::class;
        }

    }

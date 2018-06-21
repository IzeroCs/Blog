<?php

    namespace Librarys\Database;

    abstract class ModuleAbstract
    {

        /**
         * ModuleAbstract constructor.
         */
        public function __construct()
        {

        }

        protected function __wakeup()
        {
            // TODO: Implement __wakeup() method.
        }

        protected function __clone()
        {
            // TODO: Implement __clone() method.
        }

        /**
         * @param string     $uri
         * @param string     $host
         * @param string     $user
         * @param string     $pass
         * @param string     $name
         * @param string|int $port
         * @return resource
         */
        public abstract function connect(
            $uri,
            $host,
            $user,
            $pass,
            $name,
            $port
        );

        /**
         * @return mixed
         */
        public abstract function disconnect();

        /**
         * @return mixed
         */
        public abstract function errorconnect();

        /**
         * @return mixed
         */
        public abstract function error();

        /**
         * @param resource $result
         * @return mixed
         */
        public abstract function free($result);

        /**
         * @param string $sql
         * @return resource|bool
         */
        public abstract function query($sql);

        /**
         * @param string|resource $sql
         * @return int|bool
         */
        public abstract function rows($sql);

        /**
         * @param string|resource $sql
         * @return array|bool
         */
        public abstract function assoc($sql);

        /**
         * @param string $charset
         */
        public function setCharset($charset)
        {
            $this->query('SET NAMES "' . $charset . '"');
            $this->query('SET CHARACTER SET ' . $charset);
        }

        /**
         * @return int
         */
        public abstract function insertId();

        /**
         * @param string $uri
         * @return bool
         */
        public static function isSupport($uri)
        {
            return false;
        }

        /**
         * @return null
         */
        public static function getQueryClass()
        {
            return null;
        }

        /**
         * @return null
         */
        public static function getSchemaClass()
        {
            return null;
        }

    }
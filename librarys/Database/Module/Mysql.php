<?php

    namespace Librarys\Database\Module;

    use Librarys\Database\Connect;
    use Librarys\Database\ModuleAbstract;
    use Librarys\Database\Query\Mysql as Query;
    use Librarys\Database\Schema\Mysql as Schema;

    class Mysql extends ModuleAbstract
    {

        /**
         * @param string $uri
         * @param string $host
         * @param string $user
         * @param string $pass
         * @param string $name
         * @param int|string $port
         * @return bool|resource
         */
        public function connect(
            $uri,
            $host,
            $user,
            $pass,
            $name,
            $port
        ) {
            if ($port != null && empty($port) && is_numeric($port))
                $host .= ':' . $port;

            $resource = @mysql_connect(
                $host,
                $user,
                $pass
            );

            if (Connect::isResource($resource) && $name != null) {
                if (@mysql_select_db($name) == false)
                    return false;
            }

            return $resource;
        }

        /**
         * @return resource
         */
        public function disconnect()
        {
            return @mysql_close(Connect::getInstance()->getResource());
        }

        /**
         * @return mixed|string
         */
        public function errorconnect()
        {
            return $this->error();
        }

        /**
         * @return string
         */
        public function error()
        {
            if (Connect::isResource(Connect::getInstance()->getResource()))
                return @mysql_error(Connect::getInstance()->getResource());

            return @mysql_error();
        }

        /**
         * @param resource $result
         */
        public function free($result)
        {
            if (Connect::isResource($result))
                @mysql_free_result($result);
        }

        /**
         * @param string $sql
         * @return resource
         */
        public function query($sql)
        {
            return @mysql_query($sql, Connect::getInstance()->getResource());
        }

        /**
         * @param resource|string $sql
         * @return int
         */
        public function rows($sql)
        {
            if (Connect::isResource($sql) == false)
                $sql = $this->query($sql);

            return @mysql_num_rows($sql);
        }

        /**
         * @param resource|string $sql
         * @return array
         */
        public function assoc($sql)
        {
            if (Connect::isResource($sql) == false)
                $sql = $this->query($sql);

            return @mysql_fetch_assoc($sql);
        }

        /**
         * @return int
         */
        public function insertId()
        {
            return @mysql_insert_id(Connect::getInstance()->getResource());
        }

        /**
         * @param string $uri
         * @return bool
         */
        public static function isSupport($uri)
        {
            return @function_exists('mysql_connect');
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
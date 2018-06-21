<?php

    namespace Librarys\Database\Module;

    use Librarys\Database\Connect;
    use Librarys\Database\ModuleAbstract;
    use Librarys\Database\Query\Mysqli as Query;
    use Librarys\Database\Schema\Mysqli as Schema;

    class Mysqli extends ModuleAbstract
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
            return @mysqli_connect(
                $host,
                $user,
                $pass,
                $name,
                $port
            );
        }

        /**
         * @return resource
         */
        public function disconnect()
        {
            return @mysqli_close(Connect::getInstance()->getResource());
        }

        /**
         * @return mixed|string
         */
        public function errorconnect()
        {
            return @mysqli_connect_error();
        }

        /**
         * @return string
         */
        public function error()
        {
            return @mysqli_error(Connect::getInstance()->getResource());
        }

        /**
         * @param resource|\mysqli_result $result
         */
        public function free($result)
        {
            if (Connect::isResource($result))
                @mysqli_free_result($result);
        }

        /**
         * @param string $sql
         * @return resource
         */
        public function query($sql)
        {
            return @mysqli_query(Connect::getInstance()->getResource(), $sql);
        }

        /**
         * @param resource|string|\mysqli_result $sql
         * @return int
         */
        public function rows($sql)
        {
            if (Connect::isResource($sql) == false)
                $sql = $this->query($sql);

            return @mysqli_num_rows($sql);
        }

        /**
         * @param resource|string|\mysqli_result $sql
         * @return array|null
         */
        public function assoc($sql)
        {
            if (Connect::isResource($sql) == false)
                $sql = $this->query($sql);

            return @mysqli_fetch_assoc($sql);
        }

        /**
         * @return int|string
         */
        public function insertId()
        {
            return @mysqli_insert_id(Connect::getInstance()->getResource());
        }

        /**
         * @param string $uri
         * @return bool
         */
        public static function isSupport($uri)
        {
            return @function_exists('mysqli_connect');
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
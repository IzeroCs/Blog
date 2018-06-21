<?php

    namespace Librarys\Database;

    class QueryFactory
    {

        /**
         * QueryFactory constructor.
         */
        protected function __construct()
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
         * @param string|null $table
         * @return bool|QueryAbstract
         */
        public static function createInstance($table = null)
        {
            $classQuery = Connect::getInstance()->getModule()->getQueryClass();

            if ($classQuery == null)
                return false;

            return new $classQuery($table);
        }

    }

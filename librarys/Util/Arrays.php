<?php

    namespace Librarys\Util;

    class Arrays
    {

        /**
         * Arrays constructor.
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
         * @param array $array
         * @param string $key
         * @param mixed|null $value
         * @return bool
         */
        public static function makeEntryIfNotExists(&$array, $key, $value = null)
        {
            if (is_array($array) == false)
                return false;

            if (array_key_exists($key, $array) == false)
                $array[$key] = $value;

            return true;
        }

    }

<?php

    namespace Librarys\Util;

    class Booleans
    {

        /**
         * @param bool $a
         * @param bool $b
         * @return bool
         */
        public static function equals($a, $b)
        {
            $a = boolval($a);
            $b = boolval($b);

            return $a === $b;
        }

    }
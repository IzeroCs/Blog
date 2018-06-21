<?php

    namespace Librarys\Util\Text;

    class Strings
    {

        /**
         * @param string $str
         * @return string
         */
        public static function escape($str)
        {
            return addslashes($str);
        }

        /**
         * @param string $str
         * @return string
         */
        public static function unescape($str)
        {
            return stripslashes($str);
        }

        /**
         * @param string $str
         * @return string
         */
        public static function enhtml($str)
        {
            return htmlspecialchars($str);
        }

        /**
         * @param string $str
         * @return string
         */
        public static function unhtml($str)
        {
            return htmlspecialchars_decode($str);
        }

        /**
         * @param string $str
         * @return string
         */
        public static function urlencode($str)
        {
            return rawurlencode($str);
        }

        /**
         * @param string $str
         * @return string
         */
        public static function urldecode($str)
        {
            return rawurldecode($str);
        }

        /**
         * @param string $a
         * @param string $b
         * @param bool   $case
         * @return bool
         */
        public static function equals($a, $b, $case = false)
        {
            if ($case)
                return strcasecmp($a, $b) === 0;

            return strcmp($a, $b) === 0;
        }

    }
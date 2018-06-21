<?php

    namespace Librarys\Environment;

    require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'FileSystem.php');
    require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Request.php');

    class Loader
    {

        /**
         * @var \Librarys\Environment\Loader $instance
         */
        private static $instance;

        /**
         * @var string $filepath
         */
        private $filepath;

        /**
         * @var array $cache
         */
        private $cache;

        /**
         * @var array $array
         */
        private $array;

        /**
         * Loader constructor.
         * @param string $filepath
         */
        protected function __construct($filepath)
        {
            global $_SERVER, $_POST, $_GET, $_REQUEST, $_COOKIE, $_SESSION;

            if ($filepath == null)
                return null;

            $this->filepath  = $filepath;
            $this->cache     = array();
            $this->array     = require_once($filepath);
        }

        protected function __wakeup()
        {
            // TODO: Implement __wakeup() method.
        }

        protected function __clone()
        {
            // TODO: Implement __clone() method.
        }

        public static function getInstance($path)
        {
            if (self::$instance == null)
                self::$instance = new Loader($path);

            return self::$instance;
        }

        public function execute()
        {

        }

        public static function env($name, $default = null)
        {
            if (self::$instance instanceof Loader == false)
                return null;

            $res = self::envSystem($name, $default);

            if ($res !== false)
                return $res;

            if (array_key_exists($name, self::$instance->cache))
                return self::$instance->cache[$name];

            return (self::$instance->cache[$name] = urlSeparatorMatches(self::$instance->get($name, $default)));
        }

        private static function envSystem($name, $default = null)
        {
            if (preg_match('/^(SERVER|POST|GET|REQUEST|GLOBALS|COOKIE|SESSION)(\.(.*?))?$/s', $name, $matches)) {
                if ($matches[1] != 'GLOBALS')
                    $matches[1] = '_' . $matches[1];

                if (count($matches) <= 2) {
                    if (isset($GLOBALS[$matches[1]]))
                        return $GLOBALS[$matches[1]];

                    return null;
                }

                if (isset($GLOBALS[$matches[1]]) == false)
                    return null;

                return self::$instance->get($matches[3], $default, $GLOBALS[$matches[1]]);
            }

            return false;
        }

        private function cache($name, $default = null, $recache = false)
        {
            if ($recache && array_key_exists($name, self::$instance->cache))
                unset(self::$instance->cache[$name]);

            self::$instance->cache[$name] = self::env($name, $default);
        }

        private function setCache($name, $value)
        {
            self::$instance->cache[$name] = $value;
        }

        protected function get($key, $default = null, $array = null)
        {
            if (is_string($key) && empty($key) == false) {
                if ($array == null)
                    $array = $this->array;

                $keys  = explode('.', $key);

                if (is_array($keys) == false)
                    $keys = array($key);

                foreach ($keys AS $entry) {
                    $entry = trim($entry);

                    if (array_key_exists($entry, $array) == false)
                        return $default;

                    $array = $array[$entry];
                }

                return self::envMatchesString($array);
            }

            return $default;
        }

        public static function envMatchesString($str)
        {
            if ($str == null || empty($str) || is_object($str))
                return $str;

            if (is_array($str) || preg_match('/\$\{(.+?)\}/si', $str, $matches) == false)
                return $str;

            return preg_replace_callback('/\$\{(.+?)\}/si', function($matches) {
                $result = null;

                if (isset($GLOBALS[$matches[1]]))
                    $result = $GLOBALS[$matches[1]];
                else if (defined($matches[1]))
                    $result = constant($matches[1]);
                else
                    $result = env(trim($matches[1]));

                if (is_array($result))
                    return 'Application';
                else if (is_object($result))
                    return 'Object';
                else if (is_resource($result))
                    return 'Resource';

                return $result;
            }, $str);
        }

    }

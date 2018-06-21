<?php

    namespace Librarys\Language;

    use Librarys\Environment\Loader as Environment;
    use Librarys\File\FileSystem;
    use Librarys\Language\Exception\LanguageException;

    class Loader
    {

        /**
         * @var array $lang
         */
        private $lang;

        /**
         * @var array $cache
         */
        private $cache;

        /**
         * @var Loader $instance
         */
        private static $instance;

        /**
         * @var array $params
         */
        private static $params;

        /**
         * Loader constructor.
         */
        protected function __construct()
        {
            $this->lang  = [];
            $this->cache = [];

            self::$instance = $this;
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
         * @return Loader
         */
        public static function getInstance()
        {
            if (null === self::$instance)
                self::$instance = new Loader();

            return self::$instance;
        }

        /**
         *
         */
        public function execute()
        {

        }

        /**
         * @param  string $name
         * @param array   $params
         * @return array|bool|mixed|null|string
         * @throws LanguageException
         */
        public static function lng($name, $params = [])
        {
            if (self::$instance instanceof Loader == false)
                return null;

            if ($name == null || empty($name))
                throw new LanguageException('Name is null');

            $filepath = null;

            if (preg_match('/^[a-zA-Z0-9_]+\..+?$/si', $name)) {

                if (array_key_exists($name, self::$instance->cache))
                    return self::$instance->cache[$name];

                $prefixKey  = null;
                $keyCurrent = $name;
                $array      = self::load($name, $filepath, true, $prefixKey);

                if ($prefixKey != null)
                    $keyCurrent = substr($keyCurrent, strlen($prefixKey));

                if (strpos($keyCurrent, '.') === 0)
                    $keyCurrent = substr($keyCurrent, 1);

                $arrayKeys = explode('.', $keyCurrent);

                if (is_array($arrayKeys) == false)
                    throw new LanguageException('Key "' . $name . '" not found in language "' . $filepath . '"');

                foreach ($arrayKeys AS $entry) {
                    $entry = trim($entry);

                    if (is_array($array) == false || array_key_exists($entry, $array) == false)
                        throw new LanguageException('Key "' . $name . '" not found in language "' . $filepath . '"');

                    $array = $array[$entry];
                }

                if (is_array($array))
                    return (self::$instance->cache[$name] = 'Array');
                else if (is_object($array))
                    return (self::$instance->cache[$name] = 'Object');
                else if (is_resource($array))
                    return (self::$instance->cache[$name] = 'Resource');

                $array = Environment::envMatchesString($array);
                $array = Loader::langMatchesString($array, $params);

                if (is_array($params) && count($params) > 0) {
                    $count = count($params);

                    if ($count % 2 == 0) {
                        for ($i = 0; $i < $count; $i += 2) {
                            $key   = $i;
                            $value = null;

                            if (isset($params[$i]))
                                $key = $params[$i];

                            if (isset($params[$i + 1]))
                                $value = $params[$i + 1];

                            $array = str_replace('{$' . $key . '}', $value, $array);
                        }
                    }

                    return $array;
                }

                return (self::$instance->cache[$name] = $array);
            }

            throw new LanguageException('Key "' . $name . '" not found in language "' . $filepath . '"');
        }

        /**
         * @param string      $filename
         * @param string|null $filepath
         * @param bool        $loadRequire
         * @param null|string $prefixKey
         * @return array|mixed
         * @throws LanguageException
         */
        public static function load($filename, &$filepath = null, $loadRequire = true, &$prefixKey = null)
        {
            if (strpos($filename, '.') === false)
                throw new LanguageException('File name "' . $filename . '" not matches symbol "."');

            $container = env('language.path');
            $mime      = env('language.mime');
            $locale    = env('language.locale', 'en');
            $key       = null;
            $mime      = '.' . $mime;

            // Split string to array of symbol "."
            $splitFilename = explode('.', $filename);

            // Check array split name is array
            if (is_array($splitFilename) == false || count($splitFilename) <= 0)
                throw new LanguageException('File name "' . $filename . '" is wrong');

            $path = null;

            // Find path file language
            foreach ($splitFilename AS $index => $value) {
                if ($index === 0) {
                    $path = $container . SP . $locale . SP . $value;

                    // Check file in locale set of user is exists
                    if (FileSystem::isTypeDirectory($path) == false) {
                        if (FileSystem::isTypeFile($path . $mime)) {
                            $path .= $mime;
                            $key  = $locale . '.' . $value;

                            break;
                        } else {
                            $locale = 'en';
                            $path   = $container . SP . $locale . SP . $value;

                            // Check file in locale default is exists
                            if (FileSystem::isTypeDirectory($path) == false) {
                                if (FileSystem::isTypeFile($path . $mime) == false) {
                                    throw new LanguageException('File name "' . $filename . '" not found');
                                } else {
                                    $path .= $mime;
                                    $key  = $locale . '.' . $value;

                                    break;
                                }
                            } else {
                                $key = $locale . '.' . $value;
                            }
                        }
                    } else {
                        $key = $locale . '.' . $value;
                    }
                } else if (FileSystem::isTypeDirectory($path . SP . $value)) {
                    $path .= SP . $value;
                } else if (FileSystem::isTypeFile($path . SP . $value . $mime)) {
                    $path .= SP . $value . $mime;
                    $key  .= '.' . $value;

                    break;
                }
            }

            $prefixKey = substr($key, strlen($locale) + 1);
            $array     = null;
            $filepath  = $path;

            if (array_key_exists($key, self::$instance->lang))
                $array = self::$instance->lang[$key];
            else if ($path != null && FileSystem::isTypeFile($path))
                self::$instance->lang[$key] = ($array = require_once($path));
            else
                throw new LanguageException('File language "' . $filename . '" not found');

            if ($loadRequire && is_array($array))
                return self::loadRequire($array);

            return $array;
        }

        /**
         * @param array $array
         * @return array
         */
        private static function loadRequire(array &$array)
        {
            if (is_array($array) == false)
                return $array;

            foreach ($array AS &$value) {
                if (is_array($value))
                    self::loadRequire($value);
                else if (preg_match_all('/\#\{([a-zA-Z0-9_]+)\.(.+?)\}/si', $value, $matches))
                    self::load($matches[1][0], $filepath, false);
            }

            return $array;
        }

        /**
         * @param string $str
         * @param mixed  $params
         * @return mixed
         */
        public static function langMatchesString($str, $params = null)
        {
            if (is_array($str) || (preg_match('/\#\{(.+?)\}/si', $str) == false && preg_match('/lng\{(.+?)\}/si', $str, $matches) == false))
                return $str;

            self::$params = $params;

            $str = preg_replace_callback('/\#\{(.+?)\}/si', function($matches) {
                return lng(trim($matches[1]), self::$params);
            }, $str);

            $str = preg_replace_callback('/lng\{(.+?)\}/si', function($matches) {
                return lng(trim($matches[1]), self::$params);
            }, $str);

            return $str;
        }

    }
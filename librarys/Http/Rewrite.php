<?php

    namespace Librarys\Http;

    use Librarys\Exception\RuntimeException;

    class Rewrite
    {

        /**
         * @var array $configArray
         */
        protected $configArray;

        /**
         * @var Rewrite $instance
         */
        protected static $instance;

        /**
         * Rewrite constructor.
         */
        protected function __construct($configPath)
        {
            $this->configArray = require_once($configPath);
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
         * @param string $configPath
         * @return Rewrite
         */
        public static function getInstance($configPath = null)
        {
            if (self::$instance == null) {
                if ($configPath == null)
                    throw new RuntimeException('Config path rewrite is null');

                self::$instance = new Rewrite($configPath);
            }

            return self::$instance;
        }

        /**
         * @param string     $key
         * @param array      $params
         * @param null|array $array
         * @param bool       $removeParamsNotProcess
         * @return array|bool|mixed|null
         */
        public function get($key, $params = [], $array = null, $removeParamsNotProcess = true)
        {
            if (is_string($key) && empty($key) == false) {
                if ($array == null)
                    $array = $this->configArray;

                $keys = explode('.', $key);

                if (is_array($keys) == false)
                    $keys = [$key];

                foreach ($keys AS $entry) {
                    $entry = trim($entry);

                    if (array_key_exists($entry, $array) == false)
                        return false;

                    $array = $array[$entry];
                }

                $res = $array;

                if (is_array($array)) {
                    if (isset($array[1]) && env('rewrite.enable'))
                        $res = $array[1];
                    else
                        $res = $array[0];
                }

                if (is_array($params) && count($params) > 0) {
                    foreach ($params AS $name => $value)
                        $res = str_replace('{$' . $name . '}', $value, $res);
                }

                if ($removeParamsNotProcess)
                    $res = self::removeParameterTag($res);

                $res = env('rewrite.config.baseurl', '') . $res;

                return $res;
            }

            return false;
        }

        public static function removeParameterTag($str)
        {
            return preg_replace('/\{\$[a-zA-Z0-9_]+\}/i', '', $str);
        }

    }

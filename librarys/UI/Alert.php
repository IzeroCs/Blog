<?php

    namespace Librarys\UI;

    use Librarys\Http\Request;

    class Alert
    {

        /**
         * @var Alert $instance
         */
        private static $instance;

        /**
         * @var string|int $id
         */
        private static $id;

        /**
         * @var string $langMsg
         */
        private static $langMsg;

        const DANGER  = 'danger';
        const SUCCESS = 'success';
        const WARNING = 'warning';
        const INFO    = 'info';
        const NONE    = 'none';

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
         *
         */
        public static function execute()
        {

        }

        /**
         * @param string      $message
         * @param string|null $id
         * @param string|null $urlGoto
         */
        public static function danger($message, $id = null, $urlGoto = null)
        {
            self::add($message, self::DANGER, $id, $urlGoto);
        }

        /**
         * @param string      $message
         * @param string|null $id
         * @param string|null $urlGoto
         */
        public static function success($message, $id = null, $urlGoto = null)
        {
            self::add($message, self::SUCCESS, $id, $urlGoto);
        }

        /**
         * @param string      $message
         * @param string|null $id
         * @param string|null $urlGoto
         */
        public static function warning($message, $id = null, $urlGoto = null)
        {
            self::add($message, self::WARNING, $id, $urlGoto);
        }

        /**
         * @param string      $message
         * @param string|null $id
         * @param string|null $urlGoto
         */
        public static function info($message, $id = null, $urlGoto = null)
        {
            self::add($message, self::INFO, $id, $urlGoto);
        }

        /**
         * @param string      $message
         * @param string      $type
         * @param string|null $id
         * @param string|null $urlGoto
         */
        public static function add($message, $type = self::DANGER, $id = null, $urlGoto = null)
        {
            if (env('alert.enable') == false)
                return;

            if ($id == null) {
                if (self::$id == null)
                    self::$id = time();

                $id = self::$id;
            }

            if ($message == null && self::$langMsg != null)
                $message = self::$langMsg;

            Request::session()->put(env('alert.session_prefix') . $id, [
                'message' => $message,
                'type'    => $type
            ], true);

            if ($urlGoto !== null)
                Request::redirect($urlGoto);
        }

        /**
         * @return void
         */
        public static function display()
        {
            if (env('alert.enable') == false)
                return;

            if (self::$id != null && Request::session()->has(env('alert.session_prefix') . self::$id) && count(Request::session()->get(env('alert.session_prefix') . self::$id)) > 0) {
                $array    = Request::session()->get(env('alert.session_prefix') . self::$id);
                $callback = env('alert.display_callback', function($lists) {
                    return dump($lists);
                });

                echo($callback($array));
                Request::session()->remove(env('alert.session_prefix') . self::$id);
            }
        }

        /**
         * @param string|int $id
         */
        public static function setID($id)
        {
            self::$id = $id;
        }

        /**
         * @param string $key
         */
        public static function setLangMsg($key)
        {
            $args = func_get_args();
            $nums = func_num_args();

            if ($nums <= 1)
                $args = [];
            else
                $args = array_splice($args, 1, $nums);

            self::$langMsg = lng($key, $args);
        }

        /**
         *
         */
        public static function removeLangMsg()
        {
            self::$langMsg = null;
        }

        /**
         * @return string
         */
        public static function getLangMsg()
        {
            return self::$langMsg;
        }

        /**
         * @return int|string
         */
        public static function getId()
        {
            return self::$id;
        }

        /**
         * @param null $id
         */
        public static function clear($id = null)
        {
            if ($id == null)
                $id = self::$id;

            if ($id == null)
                return;

            Request::session()->remove(env('alert.session_prefix') . $id);
        }

        /**
         * @param string|int|null $id
         * @return bool
         */
        public static function hasAlertDisplay($id = null)
        {
            if ($id == null)
                $id = self::$id;

            if ($id == null)
                return false;

            return Request::session()->has(env('alert.session_prefix') . $id) && count(Request::session()->get(env('alert.session_prefix') . $id)) > 0;
        }

    }

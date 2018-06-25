<?php

    use Librarys\Database\Connect;
    use Librarys\Database\QueryFactory;
    use Librarys\Exception\RuntimeException;

    class SettingSystem
    {

        protected static $arrayConfigs = array();

        public static function init()
        {
            if (Connect::isConnect()) {
                $query = QueryFactory::createInstance(env('database.tables.setting_system'));
                $query->setLimit(1);

                if ($query->execute() !== false && $query->rows() > 0) {
                    $assoc = $query->assoc();

                    foreach ($assoc AS $key => $value) {
                        $key = str_replace('_', '', $key);
                        $key = strtolower($key);

                        self::$arrayConfigs[$key] = $value;
                    }
                }
            }
        }

        /**
         * @param $name
         * @param $arguments
         * @return mixed
         * @throws RuntimeException
         */
        public static function __callStatic($name, $arguments)
        {
            $varname  = null;
            $funcname = null;

            if (strpos($name, 'get') === 0) {
                $varname  = substr($name, 3);
                $funcname = 'strval';
            } else if (strpos($name, 'is') === 0) {
                $varname  = substr($name, 2);
                $funcname = 'boolval';
            } else {
                throw new RuntimeException('Method name ' . $name . ' not found in class ' . __CLASS__);
            }

            $varname = strtolower($varname);

            if (array_key_exists($varname, self::$arrayConfigs) !== false) {
                $var = $funcname(self::$arrayConfigs[$varname]);

                if (count($arguments) > 0) {
                    foreach ($arguments AS $arg)
                        $var = $arg($var);
                }

                return $var;
            }

            //throw new RuntimeException('Method name ' . $name . ' not found in class ' . __CLASS__);
        }

    }
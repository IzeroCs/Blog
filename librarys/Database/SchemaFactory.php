<?php

    namespace Librarys\Database;

    class SchemaFactory
    {

        /**
         * @return bool|SchemaAbstract
         */
        public static function createInstance(TableAbstract $tableAbstract)
        {
            $classSchema = Connect::getInstance()->getModule()->getSchemaClass();

            if ($classSchema == null)
                return false;

            return new $classSchema($tableAbstract);
        }

    }

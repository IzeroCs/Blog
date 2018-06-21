<?php

    namespace Librarys\Database;

    class ModuleFactory
    {

        /**
         * @return ModuleAbstract
         */
        public static function getInstance()
        {
            return Connect::getInstance()->getModule();
        }

    }

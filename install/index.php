<?php

    require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

    Librarys\Bootstrap::run();
    Librarys\Database\TableAbstract::runTableClassInDirectory(__DIR__ . DIRECTORY_SEPARATOR . 'table');
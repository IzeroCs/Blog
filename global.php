<?php

    use Librarys\UI\Alert;

    if (defined('LOADED') == false)
        exit;

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    require_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

    Librarys\Bootstrap::run();
    SettingSystem::init();

    if (defined('SIGN') && User::isSignIn())
        Alert::info(lng('sign_in.alert.sign_in_is_already'), ALERT_HOME, env('app.http_host'));

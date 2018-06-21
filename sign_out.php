<?php

    use Librarys\UI\Alert;

    define('LOADED', 1);
    define('ROBOTS', 0);

    require_once('global.php');

    if (User::isSignIn() == false)
        Alert::danger(lng('sign_out.alert.not_sign_in'), ALERT_SIGN_IN, rewrite('url.sign_in'));

    if (User::closeSession() == false)
        Alert::danger(lng('sign_out.alert.sign_out_failed'), ALERT_HOME, env('app.http_host'));

    Alert::success(lng('sign_out.alert.sign_out_success'), ALERT_HOME, env('app.http_host'));

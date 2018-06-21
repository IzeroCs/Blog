<?php

    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;

    define('ROBOTS', 0);
    require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'global.php');

    if (User::isSignIn() == false)
        Alert::danger(lng('sign_out.alert.not_sign_in'), ALERT_SIGN_IN, rewrite('url.sign_in'));

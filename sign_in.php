<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;

    define('SIGN', 1);
    define('LOADED', 1);
    define('ROBOTS', 0);

    require_once('global.php');
    require_header(lng('sign_in.title'), ALERT_SIGN_IN);

    $username = null;
    $password = null;

    if (isset($_POST['submit'])) {
        $username = Strings::escape($_POST['username']);
        $password = Strings::escape($_POST['password']);

        if (empty($username) || empty($password)) {
            Alert::danger(lng('sign_in.alert.not_input'));
        } else {
            $query = QueryFactory::createInstance(env('database.tables.user'));
            $query->setCommand(QueryAbstract::COMMAND_SELECT);
            $query->addSelect('id');
            $query->addSelect('username');
            $query->addSelect('password');
            $query->addWhere('username', $username);
            $query->addWhere('email', $username, QueryAbstract::OPERATOR_EQUAL, QueryAbstract::WHERE_OR);
            $query->setLimit(1);

            if ($query->execute() == false) {
                Alert::danger(lng('sign_in.alert.sign_in_failed'));
            } else if ($query->rows() <= 0) {
                Alert::danger(lng('sign_in.alert.account_not_exists'));
            } else {
                $assoc = $query->assoc();

                if (User::equalsPassword($assoc['password'], $password) == false)
                    Alert::danger(lng('sign_in.alert.username_or_password_wrong'));
                else if (User::createSession($assoc) == false)
                    Alert::danger(lng('sign_in.alert.sign_in_failed'));
                else
                    Alert::success(lng('sign_in.alert.sign_in_success'), ALERT_HOME, env('app.http_host'));
            }
        }

        $username = Strings::unescape($username);
        $password = Strings::unescape($password);
    }
?>

    <div id="sign">
        <?php Alert::display(); ?>

        <form action="<?php echo rewrite('form.sign_in'); ?>" method="post">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>"/>

            <ul>
                <li class="input">
                    <input type="text" name="username" value="<?php echo Strings::enhtml($username); ?>" placeholder="<?php echo lng('sign_in.input.placeholder.input_username_or_email'); ?>" autofocus/>
                    <span class="icomoon icon-user"></span>
                </li>
                <li class="input">
                    <input type="password" name="password" value="<?php echo Strings::enhtml($password); ?>" placeholder="<?php echo lng('sign_in.input.placeholder.input_password'); ?>"/>
                    <span class="icomoon icon-key"></span>
                </li>
                <li class="button">
                    <?php if (SettingSystem::isEnableSignUp()) { ?>
                        <a href="<?php echo rewrite('url.sign_up'); ?>" id="sign-up">
                            <span><?php echo lng('sign_in.button.sign_up'); ?></span>
                        </a>
                    <?php } ?>
                    <button type="submit" name="submit">
                        <span><?php echo lng('sign_in.button.sign_in'); ?></span>
                    </button>
                </li>
            </ul>
        </form>
    </div>

<?php require_footer(); ?>
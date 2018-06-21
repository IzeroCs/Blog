<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\Http\Validate;
    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;

    define('SIGN', 2);
    define('LOADED', 1);
    define('ROBOTS', 0);

    require_once('global.php');
    require_header(lng('sign_up.title'), ALERT_SIGN_UP);

    $username   = null;
    $name       = null;
    $email      = null;
    $password   = null;
    $repassword = null;

    if (isset($_POST['submit'])) {
        $username   = Strings::escape($_POST['username']);
        $name       = Strings::escape($_POST['name']);
        $email      = Strings::escape($_POST['email']);
        $password   = Strings::escape($_POST['password']);
        $repassword = Strings::escape($_POST['repassword']);

        if (empty($username) || empty($name) || empty($email) || empty($password) || empty($repassword)) {
            Alert::danger(lng('sign_up.alert.not_input_all_field'));
        } else if (strlen($username) < User::MIN_USERNAME || strlen($username) > User::MAX_USERNAME) {
            Alert::danger(lng('sign_up.alert.username_is_small_or_large', 'min', User::MIN_USERNAME, 'max', User::MAX_USERNAME));
        } else if (strlen($name) < User::MIN_NAME || strlen($name) > User::MAX_NAME) {
            Alert::danger(lng('sign_up.alert.name_is_small_or_large', 'min', User::MIN_NAME, 'max', User::MAX_NAME));
        } else if (strlen($password) < User::MIN_PASSWORD) {
            Alert::danger(lng('sign_up.alert.password_is_small', 'min', User::MIN_PASSWORD));
        } else if (User::isUsernameValidate($username) == false) {
            Alert::danger(lng('sign_up.alert.username_not_validate'));
        } else if (Validate::email($email) == false) {
            Alert::danger(lng('sign_up.alert.email_not_validate'));
        } else if (Strings::equals($password, $repassword) === false) {
            Alert::danger(lng('sign_up.alert.password_not_equals'));
        } else {
            $query = QueryFactory::createInstance(env('database.tables.user'));
            $query->setCommand(QueryAbstract::COMMAND_SELECT);
            $query->addWhere('username', $username);
            $query->setLimit(1);

            if ($query->execute() == false) {
                Alert::danger(lng('sign_up.alert.sign_up_failed'));
            } else if ($query->rows() > 0) {
                Alert::danger(lng('sign_up.alert.username_exists'));
            } else {
                $query->removeWhere('username');
                $query->addWhere('email', $email);

                if ($query->execute(true) == false)
                    Alert::danger(lng('sign_up.alert.sign_up_failed'));
                else if ($query->rows() > 0)
                    Alert::danger(lng('sign_up.alert.email_exists'));
                else if (User::createUser($username, $name, $email, $password) == false)
                    Alert::danger(lng('sign_up.alert.sign_up_failed'));
                else
                    Alert::success(lng('sign_up.alert.sign_up_success'), ALERT_SIGN_IN, rewrite('url.sign_in'));
            }
        }

        $username   = Strings::unescape($username);
        $name       = Strings::unescape($name);
        $email      = Strings::unescape($email);
        $password   = Strings::unescape($password);
        $repassword = Strings::unescape($repassword);
    }
?>

    <div id="sign">
        <?php Alert::display(); ?>

        <form action="<?php echo rewrite('form.sign_up'); ?>" method="post">
            <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>" />

            <ul>
                <li class="input">
                    <input type="text" name="username" value="<?php echo Strings::enhtml($username); ?>" placeholder="<?php echo lng('sign_up.input.placeholder.input_username'); ?>" autofocus />
                    <span class="icomoon icon-user"></span>
                </li>
                <li class="input">
                    <input type="text" name="name" value="<?php echo Strings::enhtml($name); ?>" placeholder="<?php echo lng('sign_up.input.placeholder.input_name'); ?>" />
                    <span class="icomoon icon-profile"></span>
                </li>
                <li class="input">
                    <input type="email" name="email" value="<?php echo Strings::enhtml($email); ?>" placeholder="<?php echo lng('sign_up.input.placeholder.input_email'); ?>" />
                    <span class="icomoon icon-email"></span>
                </li>
                <li class="input">
                    <input type="password" name="password" value="<?php echo Strings::enhtml($password); ?>" placeholder="<?php echo lng('sign_up.input.placeholder.input_password'); ?>" />
                    <span class="icomoon icon-key"></span>
                </li>
                <li class="input">
                    <input type="password" name="repassword" value="<?php echo Strings::enhtml($repassword); ?>" placeholder="<?php echo lng('sign_up.input.placeholder.input_repassword'); ?>" />
                    <span class="icomoon icon-key"></span>
                </li>
                <li class="button">
                    <a href="<?php echo rewrite('url.sign_in'); ?>" id="sign-in">
                        <span><?php echo lng('sign_in.button.sign_in'); ?></span>
                    </a>
                    <button type="submit" name="submit">
                        <span><?php echo lng('sign_in.button.sign_up'); ?></span>
                    </button>
                </li>
            </ul>
        </form>
    </div>

<?php require_footer(); ?>
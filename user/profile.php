<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);
    require_once('global.php');

    $user = null;

    if (isset($_GET['user']))
        $user = Strings::urldecode($_GET['user']);

    if ($user == null || empty($user))
        Alert::danger(lng('user.profile.alert.user_not_exists'), ALERT_HOME, env('app.http_host'));

    $query = QueryFactory::createInstance(env('database.tables.user'));
    $query->setCommand(QueryAbstract::COMMAND_SELECT);
    $query->addWhere('username', QueryAbstract::escape($user));
    $query->setLimit(1);

    if ($query->execute() === false || $query->rows() <= 0)
        Alert::danger(lng('user.profile.alert.user_not_exists'), ALERT_HOME, env('app.http_host'));

    $assoc = $query->assoc();

    if (empty($assoc['wallpaper']))
        $assoc['wallpaper'] = 'images/wallpaper.jpg';

    if (empty($assoc['avatar'])) {
        $assoc['avatar'] = 'images/avatar-';

        if (intval($assoc['sex']) === 0)
            $assoc['avatar'] .= 'male';
        else
            $assoc['avatar'] .= 'female';

        $assoc['avatar'] .= '.png';
    }

    require_header($assoc['username'], ALERT_USER_PROFILE);
?>

    <div id="wrapper">
        <div id="content-wrapper">
            <?php Alert::display(); ?>
            <div id="user-profile">
                <div class="wallpaper">
                    <img src="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/<?php echo $assoc['wallpaper']; ?>" alt="<?php echo Strings::enhtml($assoc['username']); ?>"/>
                    <div class="avatar">
                        <img src="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/<?php echo $assoc['avatar']; ?>" alt="<?php echo Strings::enhtml($assoc['username']); ?>"/>
                    </div>
                    <span class="username"><?php echo $assoc['username']; ?></span>
                </div>
                <ul class="info">
                    <li>
                        <span class="icomoon icon-rectange"></span>
                        <span class="label"><?php echo lng('user.profile.info.label.name'); ?></span>
                        <span class="value"><?php echo Strings::enhtml($assoc['name']); ?></span>
                    </li>
                    <li>
                        <span class="icomoon icon-rectange"></span>
                        <span class="label"><?php echo lng('user.profile.info.label.email'); ?></span>
                        <span class="value"><?php echo Strings::enhtml($assoc['email']); ?></span>
                    </li>
                    <li>
                        <span class="icomoon icon-rectange"></span>
                        <span class="label"><?php echo lng('user.profile.info.label.brithday'); ?></span>
                        <span class="value"><?php echo date('d.m.Y', $assoc['birthday']); ?></span>
                    </li>
                    <li>
                        <span class="icomoon icon-rectange"></span>
                        <span class="label"><?php echo lng('user.profile.info.label.sex'); ?></span>
                        <span class="value"><?php echo (intval($assoc['sex']) === 0 ? lng('user.profile.info.value.sex.male') : lng('user.profile.info.value.sex.female')); ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <ul id="sidebar-wrapper">
            <?php get_sidebar_about_development(); ?>
            <?php get_sidebar_info(); ?>
        </ul>
    </div>

<?php require_footer(); ?>

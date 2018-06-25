<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\Http\Uri;
    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);

    require_once('global.php');
    require_header(lng('control.create_category.title'), ALERT_CONTROL_CREATE_CATEGORY);

    $id = 0;

    if (isset($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID]))
        $id = intval(Strings::urldecode($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID]));

    $queryParent   = null;
    $assocCategory = null;
    $actionForm    = null;
    $forwardUrl    = null;

    if ($id > 0) {
        $queryParent = QueryFactory::createInstance(env('database.tables.category'));
        $queryParent->setCommand(QueryAbstract::COMMAND_SELECT);
        $queryParent->addSelect('id');
        $queryParent->addSelect('title');
        $queryParent->addWhere('id', QueryAbstract::escape($id));
        $queryParent->setLimit(1);

        if ($queryParent->execute() !== false && $queryParent->rows() > 0)
            $assocCategory = $queryParent->assoc();
        else
            Alert::danger(lng('control.create_category.alert.category_container_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    } else {
        $id = 0;
    }

    if ($assocCategory != null) {
        $actionForm = rewrite('url.control.create_category', [
            'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
            'id'        => Strings::urlencode($id)
        ]);

        $forwardUrl = rewrite('url.control.list_category', [
            'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
            'id'        => Strings::urlencode($id)
        ]);
    } else {
        $actionForm = rewrite('url.control.create_category');
        $forwardUrl = rewrite('url.control.list_category');
    }

    $title       = null;
    $description = null;
    $seo         = null;
    $url         = null;
    $hidden      = false;

    if (isset($_POST['create_continue']) || isset($_POST['create'])) {
        $title       = Strings::escape($_POST['title']);
        $description = Strings::escape($_POST['description']);
        $seo         = Strings::escape($_POST['seo']);
        $url         = Strings::escape($_POST['url']);

        if (isset($_POST['hidden']))
            $hidden = boolval(Strings::escape($_POST['hidden']));
        else
            $hidden = false;

        if (empty($title)) {
            Alert::danger(lng('control.create_category.alert.not_input_title'));
        } else {
            $idParent    = 0;
            $seoTitle    = QueryAbstract::escape(Uri::seo($title));
            $queryCreate = QueryFactory::createInstance(env('database.tables.category'));

            if ($assocCategory != null)
                $idParent = $assocCategory['id'];

            $queryCreate->setCommand(QueryAbstract::COMMAND_SELECT);
            $queryCreate->addSelect('id');
            $queryCreate->addSelect('title');
            $queryCreate->addSelect('seo');
            $queryCreate->addWhere('title', $title);
            $queryCreate->addWhere('seo', $seoTitle, QueryAbstract::OPERATOR_EQUAL, QueryAbstract::WHERE_OR);
            $queryCreate->setLimit(1);

            if ($queryCreate->execute() === false) {
                Alert::danger(lng('control.create_category.alert.create_category_failed'));
            } else if ($queryCreate->rows() > 0) {
                $assocCreate = $queryCreate->assoc();

                if (strcasecmp($title, $assocCreate['title']) === 0)
                    Alert::danger(lng('control.create_category.alert.title_category_exists'));
                else if (strcasecmp($seoTitle, $assocCreate['seo']) === 0)
                    Alert::danger(lng('control.create_category.alert.seo_category_exists'));
                else
                    Alert::danger(lng('control.create_category.alert.create_category_failed'));
            } else {
                $queryCreate->clear();
                $queryCreate->setCommand(QueryAbstract::COMMAND_INSERT_INTO);
                $queryCreate->setLimit(1);

                $queryCreate->addDataArray([
                    'id_create'   => QueryAbstract::escape(User::getAssocId()),
                    'id_parent'   => QueryAbstract::escape($idParent),
                    'id_modify'   => 0,
                    'is_parent'   => $assocCategory == null,
                    'is_hidden'   => $hidden,
                    'title'       => $title,
                    'description' => $description,
                    'seo'         => $seoTitle,
                    'url'         => $url,
                    'create_at'   => time(),
                    'modify_at'   => 0
                ]);

                if ($queryCreate->execute() == false)
                    Alert::danger(lng('control.create_category.alert.create_category_failed'));
                else if (isset($_POST['create_continue']))
                    Alert::success(lng('control.create_category.alert.create_category_success', 'name', $title));
                else
                    Alert::success(lng('control.create_category.alert.create_category_success', 'name', $title), ALERT_CONTROL_LIST_CATEGORY, $forwardUrl);

                $title       = null;
                $description = null;
                $seo         = null;
                $url         = null;
                $hidden      = false;
            }
        }

        $title       = Strings::unescape($title);
        $description = Strings::unescape($description);
        $seo         = Strings::unescape($seo);
        $url         = Strings::unescape($url);
    }
?>

    <div id="content">
        <div id="content-wrapper">
            <?php ControlCategoryBreadcrumbs::createInstance([
                'default_url' => rewrite('url.control.create_category'),

                'begin_url' => rewrite('url.control.create_category', [
                    'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
                    'id'        => null
                ]),

                'end_url'     => null,
                'id_category' => $id
            ])->display(); ?>
            <?php Alert::display(); ?>

            <div class="form">
                <form action="<?php echo $actionForm; ?>" method="post">
                    <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>" />

                    <ul class="element">
                        <li class="input">
                            <span><?php echo lng('control.create_category.input.label.title'); ?></span>
                            <input type="text" name="title" value="<?php echo Strings::enhtml($title); ?>" placeholder="<?php echo lng('control.create_category.input.placeholder.input_title'); ?>" autofocus />
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.create_category.input.label.description'); ?></span>
                            <input type="text" name="description" value="<?php echo Strings::enhtml($description); ?>" placeholder="<?php echo lng('control.create_category.input.placeholder.input_description'); ?>" autofocus />
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.create_category.input.label.seo'); ?></span>
                            <input type="text" name="seo" value="<?php echo Strings::enhtml($seo); ?>" placeholder="<?php echo lng('control.create_category.input.placeholder.input_seo'); ?>" />
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.create_category.input.label.url'); ?></span>
                            <input type="text" name="url" value="<?php echo Strings::enhtml($url); ?>" placeholder="<?php echo lng('control.create_category.input.placeholder.input_url'); ?>" />
                        </li>
                        <li class="checkbox">
                            <span><?php echo lng('control.create_category.input.checkbox.options.title'); ?></span>

                            <ul>
                                <li>
                                    <input type="checkbox" id="label_hidden" name="hidden" value="1"<?php if ($hidden) { ?> checked="checked" <?php } ?> />
                                    <label for="label_hidden">
                                        <span><?php echo lng('control.create_category.input.checkbox.options.hidden_category'); ?></span>
                                    </label>
                                </li>
                            </ul>
                        </li>
                        <li class="button">
                            <button type="submit" name="create_continue">
                                <span><?php echo lng('control.create_category.button.create_continue'); ?></span>
                            </button>
                            <button type="submit" name="create">
                                <span><?php echo lng('control.create_category.button.create'); ?></span>
                            </button>
                            <a href="<?php echo $forwardUrl; ?>">
                                <span><?php echo lng('control.create_category.button.cancel'); ?></span>
                            </a>
                        </li>
                    </ul>
                </form>
            </div>
        </div>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <?php get_control_sidebar_list_action(rewrite('url.control.create_category')); ?>
                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
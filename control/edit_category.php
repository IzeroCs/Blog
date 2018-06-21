<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\Http\Uri;
    use Librarys\UI\Alert;
    use Librarys\Util\Booleans;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);

    require_once('global.php');
    require_header(lng('control.edit_category.title'), ALERT_CONTROL_EDIT_CATEGORY);

    $id = 0;

    if (isset($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID]))
        $id = intval(Strings::urldecode($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID]));

    SidebarControl::setFileRequire(__DIR__ . SP . 'sidebars' . SP . 'action_category.php');

    $queryCategory = null;
    $assocCategory = null;

    if ($id > 0) {
        $queryCategory = QueryFactory::createInstance(env('database.tables.category'));
        $queryCategory->setCommand(QueryAbstract::COMMAND_SELECT);
        $queryCategory->addWhere('id', QueryAbstract::escape($id));
        $queryCategory->setLimit(1);

        if ($queryCategory->execute() !== false && $queryCategory->rows() > 0)
            $assocCategory = $queryCategory->assoc();
        else
            Alert::danger(lng('control.edit_category.alert.category_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    } else {
        Alert::danger(lng('control.edit_category.alert.category_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    }

    $actionForm = rewrite('url.control.edit_category', [
        'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
        'id'        => Strings::urlencode($id)
    ]);

    $forwardUrl = null;

    if ($assocCategory['is_parent'] == false) {
        $forwardUrl = rewrite('url.control.list_category', [
            'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
            'id'        => Strings::urlencode($assocCategory['id_parent'])
        ]);
    } else {
        $forwardUrl = rewrite('url.control.list_category');
    }

    $title       = $assocCategory['title'];
    $description = $assocCategory['description'];
    $seo         = $assocCategory['seo'];
    $url         = $assocCategory['url'];
    $hidden      = boolval($assocCategory['is_hidden']);
    $autoSeo     = true;

    if (isset($_POST['save'])) {
        $title       = Strings::escape($_POST['title']);
        $description = Strings::escape($_POST['description']);
        $seo         = Strings::escape($_POST['seo']);
        $url         = Strings::escape($_POST['url']);

        if (isset($_POST['hidden']))
            $hidden = boolval(Strings::escape($_POST['hidden']));
        else
            $hidden = false;

        if (isset($_POST['auto_seo']))
            $autoSeo = boolval(Strings::escape($_POST['auto_seo']));
        else
            $autoSeo = false;

        if (empty($title)) {
            Alert::danger(lng('control.edit_category.alert.not_input_title'));
        } else if (
            Strings::equals($title, $assocCategory['title']) &&
            Strings::equals($seo, $assocCategory['seo']) &&
            Strings::equals($description, $assocCategory['description']) &&
            Strings::equals($url, $assocCategory['url']) &&
            Booleans::equals($hidden, $assocCategory['is_hidden'])
        ) {
            Alert::warning(lng('control.edit_category.alert.not_changed'));
        } else {
            if ($autoSeo)
                $seoTitle = QueryAbstract::escape(Uri::seo($title));
            else
                $seoTitle = $seo;

            $idParent  = 0;
            $queryEdit = QueryFactory::createInstance(env('database.tables.category'));

            if ($assocCategory != null)
                $idParent = $assocCategory['id'];

            $queryEdit->setCommand(QueryAbstract::COMMAND_SELECT);
            $queryEdit->addSelect('id');
            $queryEdit->addSelect('title');
            $queryEdit->addSelect('seo');
            $queryEdit->addWhere('id', $id, QueryAbstract::OPERATOR_NOT_EQUAL);
            $queryEdit->addWhere('title', $title);
            $queryEdit->addWhere('id', $id, QueryAbstract::OPERATOR_NOT_EQUAL, QueryAbstract::WHERE_OR);
            $queryEdit->addWhere('seo', $seoTitle);
            $queryEdit->setLimit(1);

            if ($queryEdit->execute() === false) {
                Alert::danger(lng('control.edit_category.alert.edit_category_failed'));
            } else if ($queryEdit->rows() > 0) {
                $assocEdit = $queryEdit->assoc();

                if (strcasecmp($title, $assocEdit['title']) === 0)
                    Alert::danger(lng('control.edit_category.alert.title_category_exists'));
                else if (strcasecmp($seoTitle, $assocEdit['seo']) === 0)
                    Alert::danger(lng('control.edit_category.alert.seo_category_exists'));
                else
                    Alert::danger(lng('control.edit_category.alert.create_category_failed'));
            } else {
                $queryEdit->clear();
                $queryEdit->setCommand(QueryAbstract::COMMAND_UPDATE);
                $queryEdit->addWhere('id', QueryAbstract::escape($id));
                $queryEdit->setLimit(1);

                $queryEdit->addDataArray([
                    'id_modify'   => QueryAbstract::escape(User::getAssocId()),
                    'is_hidden'   => $hidden,
                    'title'       => $title,
                    'description' => $description,
                    'seo'         => $seoTitle,
                    'url'         => $url,
                    'modify_at'   => time()
                ]);

                if ($queryEdit->execute() == false)
                    Alert::danger(lng('control.edit_category.alert.edit_category_failed'));
                else
                    Alert::success(lng('control.edit_category.alert.edit_category_success', 'name', $title), ALERT_CONTROL_LIST_CATEGORY, $forwardUrl);
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
                'default_url' => rewrite('url.control.list_category'),

                'begin_url' => rewrite('url.control.list_category', [
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
                            <span><?php echo lng('control.edit_category.input.label.title'); ?></span>
                            <input type="text" name="title" value="<?php echo Strings::enhtml($title); ?>" placeholder="<?php echo lng('control.edit_category.input.placeholder.input_title'); ?>" autofocus />
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.edit_category.input.label.description'); ?></span>
                            <input type="text" name="description" value="<?php echo Strings::enhtml($description); ?>" placeholder="<?php echo lng('control.edit_category.input.placeholder.input_description'); ?>" autofocus />
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.edit_category.input.label.seo'); ?></span>
                            <input type="text" name="seo" value="<?php echo Strings::enhtml($seo); ?>" placeholder="<?php echo lng('control.edit_category.input.placeholder.input_seo'); ?>" />
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.edit_category.input.label.url'); ?></span>
                            <input type="text" name="url" value="<?php echo Strings::enhtml($url); ?>" placeholder="<?php echo lng('control.edit_category.input.placeholder.input_url'); ?>" />
                        </li>
                        <li class="checkbox">
                            <span><?php echo lng('control.edit_category.input.checkbox.options.title'); ?></span>

                            <ul>
                                <li>
                                    <input type="checkbox" id="label_hidden" name="hidden" value="1"<?php if ($hidden) { ?> checked="checked" <?php } ?> />
                                    <label for="label_hidden">
                                        <span><?php echo lng('control.edit_category.input.checkbox.options.hidden_category'); ?></span>
                                    </label>
                                </li>
                                <li>
                                    <input type="checkbox" id="label_auto_seo" name="auto_seo" value="1"<?php if ($autoSeo) { ?> checked="checked" <?php } ?> />
                                    <label for="label_auto_seo">
                                        <span><?php echo lng('control.edit_category.input.checkbox.options.auto_seo_category'); ?></span>
                                    </label>
                                </li>
                            </ul>
                        </li>
                        <li class="button">
                            <button type="submit" name="save">
                                <span><?php echo lng('control.edit_category.button.save'); ?></span>
                            </button>
                            <a href="<?php echo $forwardUrl; ?>">
                                <span><?php echo lng('control.edit_category.button.cancel'); ?></span>
                            </a>
                        </li>
                    </ul>
                </form>
            </div>
        </div>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <?php get_sidebar_list_action(rewrite('url.control.edit_category')); ?>
                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
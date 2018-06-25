<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\Http\Uri;
    use Librarys\UI\Alert;
    use Librarys\Util\Booleans;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);

    require_once('global.php');
    require_header(lng('control.remove_category.title'), ALERT_CONTROL_REMOVE_CATEGORY);

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
            Alert::danger(lng('control.remove_category.alert.category_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    } else {
        Alert::danger(lng('control.remove_category.alert.category_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    }

    $actionForm = rewrite('url.control.remove_category', [
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

    if (isset($_POST['trash'])) {

    } else if (isset($_POST['remove'])) {

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
                        <li class="message-accept">
                            <span><?php echo lng('control.remove_category.message', 'name', Strings::enhtml($assocCategory['title'])); ?></span>
                        </li>
                        <li class="button">
                            <button type="submit" name="trash">
                                <span><?php echo lng('control.remove_category.button.trash'); ?></span>
                            </button>
                            <button type="submit" name="remove">
                                <span><?php echo lng('control.remove_category.button.remove'); ?></span>
                            </button>
                            <a href="<?php echo $forwardUrl; ?>">
                                <span><?php echo lng('control.remove_category.button.cancel'); ?></span>
                            </a>
                        </li>
                    </ul>
                </form>
            </div>
        </div>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <?php get_control_sidebar_list_action(rewrite('url.control.remove_category')); ?>
                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
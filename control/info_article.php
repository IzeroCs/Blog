<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);

    require_once('global.php');
    require_header(lng('control.info_article.title'), ALERT_CONTROL_INFO_ARTICLE);

    $id = 0;

    if (isset($_GET[PARAMETER_CONTROL_ARTICLE_ID]))
        $id = intval(Strings::urldecode($_GET[PARAMETER_CONTROL_ARTICLE_ID]));

    SidebarControl::setFileRequire(__DIR__ . SP . 'sidebars' . SP . 'action_article.php');

    $queryArticle = null;
    $assocArticle = null;
    $actionForm   = null;
    $forwardUrl   = null;

    if ($id > 0) {
        $queryArticle = QueryFactory::createInstance(env('database.tables.article'));
        $queryArticle->setCommand(QueryAbstract::COMMAND_SELECT);
        $queryArticle->addSelect('id');
        $queryArticle->addSelect('id_category');
        $queryArticle->addSelect('title');
        $queryArticle->addWhere('id', QueryAbstract::escape($id));
        $queryArticle->setLimit(1);

        if ($queryArticle->execute() !== false && $queryArticle->rows() > 0)
            $assocArticle = $queryArticle->assoc();
        else
            Alert::danger(lng('control.info_article.alert.article_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    } else {
        Alert::danger(lng('control.info_article.alert.article_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    }

    $actionForm = rewrite('url.control.info_article', [
        'p' => '?' . PARAMETER_CONTROL_ARTICLE_ID . '=',
        'id'        => Strings::urlencode($id)
    ]);

    $forwardUrl = rewrite('url.control.list_category', [
        'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
        'id'        => Strings::urlencode($assocArticle['id_category'])
    ]);

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
                'id_category' => $assocArticle['id_category']
            ])->display(); ?>
            <?php Alert::display(); ?>

            <div class="form">
                <form action="<?php echo $actionForm; ?>" method="post">
                    <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>" />

                    <ul class="element">
                        <li class="button">
                            <a href="<?php echo $forwardUrl; ?>">
                                <span><?php echo lng('control.info_article.button.back'); ?></span>
                            </a>
                        </li>
                    </ul>
                </form>
            </div>
        </div>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <?php get_control_sidebar_list_action(rewrite('url.control.info_article')); ?>
                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
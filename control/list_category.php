<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);

    require_once('global.php');
    require_header(lng('control.list_category.title'), ALERT_CONTROL_LIST_CATEGORY);

    $id = 0;

    if (isset($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID]))
        $id = intval(Strings::urldecode($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID]));

    $queryList    = QueryFactory::createInstance(env('database.tables.category'));
    $queryParent  = QueryFactory::createInstance(env('database.tables.category'));
    $queryArticle = null;

    $assocList    = null;
    $assocParent  = null;
    $assocArticle = null;

    $queryParent->setCommand(QueryAbstract::COMMAND_SELECT);
    $queryList->setCommand(QueryAbstract::COMMAND_SELECT);
    $queryList->addWhere('is_trash', 0);
    $queryList->setOrderBy('id');

    if ($id > 0) {
        $queryList->addWhere('id_parent', QueryAbstract::escape($id));
        $queryList->addWhere('is_parent', false);

        $queryParent->addWhere('id', QueryAbstract::escape($id));
        $queryParent->setLimit(1);
        $queryParent->execute();

        if ($queryParent->rows() <= 0)
            Alert::danger(lng('control.list_category.alert.category_not_exists'), null, rewrite('url.control.list_category'));

        $assocParent  = $queryParent->assoc();
        $queryArticle = QueryFactory::createInstance(env('database.tables.article'));
        $queryArticle->addSelect('id');
        $queryArticle->addSelect('title');
        $queryArticle->addSelect('is_hidden');
        $queryArticle->addWhere('id_category', QueryAbstract::escape($assocParent['id']));
        $queryArticle->addOrderBy('id');
    } else {
        $queryList->addWhere('is_parent', true);

        $assocParent = [
            'title'     => lng('control.list_category.list.title_root'),
            'is_parent' => true,
            'is_hidden' => false
        ];
    }

    $rowsList    = 0;
    $rowsArticle = 0;

    if ($queryList->execute() !== false)
        $rowsList = $queryList->rows();

    if ($queryArticle !== null && $queryArticle->execute() !== false)
        $rowsArticle = $queryArticle->rows();

    if ($assocParent['is_parent']) {
        $assocParent['url_forward'] = rewrite('url.control.list_category');
    } else {
        $queryTmp = QueryFactory::createInstance(env('database.tables.category'));
        $queryTmp->setCommand(QueryAbstract::COMMAND_SELECT);
        $queryTmp->addSelect('id');
        $queryTmp->addWhere('id', QueryAbstract::escape($assocParent['id_parent']));
        $queryTmp->setLimit(1);

        if ($queryTmp->execute() !== false && $queryTmp->rows() > 0) {
            $assocTmp = $queryTmp->assoc();

            $assocParent['url_forward'] = rewrite('url.control.list_category', [
                'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
                'id'        => $assocTmp['id']
            ]);
        }
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

            <ul class="list category">
                <li class="title<?php if ($assocParent['is_hidden']) { ?> name-hidden<?php } ?>">
                    <span class="icomoon icon-layer"></span>
                    <a href="<?php echo $assocParent['url_forward']; ?>">
                        <span><?php echo Strings::enhtml($assocParent['title']); ?></span>
                    </a>
                </li>

                <?php if ($rowsList <= 0) { ?>
                    <li class="empty">
                        <span class="icomoon icon-trash"></span>
                        <span><?php echo lng('control.list_category.list.empty_category'); ?></span>
                    </li>
                <?php } else { ?>
                    <?php while ($assocList = $queryList->assoc()) { ?>
                        <?php $urlCategory = rewrite('url.control.list_category', [
                            'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
                            'id'        => Strings::urlencode($assocList['id'])
                        ]); ?>

                        <?php $urlActionCategory = rewrite('url.control.edit_category', [
                            'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
                            'id'        => Strings::urlencode($assocList['id'])
                        ]); ?>

                        <li class="entry">
                            <p class="link<?php if ($assocList['is_hidden']) { ?> name-hidden<?php } ?>">
                                <a href="<?php echo $urlActionCategory; ?>">
                                    <span class="icomoon icon-layer"></span>
                                </a>
                                <a href="<?php echo $urlCategory; ?>">
                                    <span><?php echo Strings::enhtml($assocList['title']); ?></span>
                                </a>
                            </p>
                            <?php if (empty($assocList['description']) == false) { ?>
                                <p class="description">
                                    <span><?php echo $assocList['description']; ?></span>
                                </p>
                            <?php } ?>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>

            <?php if ($queryArticle !== null) { ?>
                <ul class="list article">
                    <li class="title">
                        <span class="icomoon icon-news"></span>
                        <span><?php echo lng('control.list_category.list.title_article'); ?></span>
                    </li>

                    <?php if ($rowsArticle <= 0) { ?>
                        <li class="empty">
                            <span class="icomoon icon-trash"></span>
                            <span><?php echo lng('control.list_category.list.empty_article'); ?></span>
                        </li>
                    <?php } else { ?>
                        <?php while ($assocArticle = $queryArticle->assoc()) { ?>
                            <?php $urlActionArticle = rewrite('url.control.info_article', [
                                'p' => '?' . PARAMETER_CONTROL_ARTICLE_ID . '=',
                                'id'        => Strings::urlencode($assocArticle['id'])
                            ]); ?>

                            <?php $urlEditArticle = rewrite('url.control.edit_article', [
                                'p' => '?' . PARAMETER_CONTROL_ARTICLE_ID . '=',
                                'id'        => Strings::urlencode($assocArticle['id'])
                            ]); ?>

                             <li class="entry">
                                <p class="link<?php if ($assocArticle['is_hidden']) { ?> name-hidden<?php } ?>">
                                    <a href="<?php echo $urlActionArticle; ?>">
                                        <span class="icomoon icon-news"></span>
                                    </a>
                                    <a href="<?php echo $urlEditArticle; ?>">
                                        <span><?php echo Strings::enhtml($assocArticle['title']); ?></span>
                                    </a>
                                </p>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <?php get_control_sidebar_list_action(rewrite('url.control.list_category')); ?>
                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
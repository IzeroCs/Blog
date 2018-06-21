<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\Http\Request;
    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);
    define('LOADER_QUILL', 1);
    define('NOT_LOADER_QUILL_SCRIPT', 1);
    require_once('global.php');

    $id  = 0;
    $seo = null;

    if (isset($_GET['id']))
        $id = intval(Strings::escape($_GET['id']));

    if (isset($_GET['seo']))
        $seo = Strings::urldecode(Strings::escape($_GET['seo']));

    if ($id <= 0 || empty($seo))
        Request::exitResponseCode(404);

    $queryArticle = QueryFactory::createInstance(env('database.tables.article'));
    $queryArticle->setCommand(QueryAbstract::COMMAND_SELECT);
    $queryArticle->addWhere('id', $id);
    $queryArticle->addWhere('seo', $seo);
    $queryArticle->addWhere('is_hidden', 0);
    $queryArticle->addWhere('is_trash', 0);
    $queryArticle->setLimit(1);

    if ($queryArticle->rows() <= 0)
        Request::exitResponseCode(404);

    $assocArticle = $queryArticle->assoc();

    $queryParent = QueryFactory::createInstance(env('database.tables.category'));
    $queryParent->setCommand(QueryAbstract::COMMAND_SELECT);
    $queryParent->setSelect('id');
    $queryParent->setSelect('seo');
    $queryParent->setSelect('title');
    $queryParent->addWhere('id', Strings::escape($assocArticle['id_category']));
    $queryParent->addWhere('is_hidden', 0);
    $queryParent->addWhere('is_trash', 0);

    if ($queryParent->execute() === false || $queryParent->rows() <= 0)
        Request::exitResponseCode(404);

    $assocParent                 = $queryParent->assoc();
    $assocArticle['url_forward'] = rewrite('url.category', [
        'p_seo' => '?seo=',
        'p_id'  => '&id=',

        'seo' => Strings::urlencode($assocParent['seo']),
        'id'  => Strings::urlencode($assocParent['id'])
    ]);

    $breadcrumbs = HomeCategoryBreadcrumbs::createInstance([
        'id_category' => $assocArticle['id_category'],
        'end_link'    => true
    ], function() {
        Request::exitResponseCode(404);
    })->display(false);

    $queryCategory = QueryFactory::createInstance(env('database.tables.category'));
    $queryCategory->setCommand(QueryAbstract::COMMAND_SELECT);
    $queryCategory->addSelect('id');
    $queryCategory->addSelect('title');
    $queryCategory->addSelect('seo');
    $queryCategory->addSelect('description');
    $queryCategory->addSelect('url');
    $queryCategory->addWhere('id_parent', QueryAbstract::escape($assocParent['id']));
    $queryCategory->addWhere('is_hidden', 0);
    $queryCategory->addWhere('is_trash', 0);
    $queryCategory->setOrderBy('id', QueryAbstract::ORDER_ASC);

    $rowsCategory = 0;

    if ($queryCategory->execute() !== false)
        $rowsCategory = $queryCategory->rows();

    require_header($assocArticle['title'], ALERT_ARTICLE);

    $urlArticle = null;

    if (empty($assocArticle['url']) == false) {
        $urlArticle = $assoc['url'];
    } else {
        $urlArticle = rewrite('url.article', [
            'p_seo' => '?seo=',
            'p_id'  => '?id=',

            'seo' => Strings::urlencode($assocArticle['seo']),
            'id'  => Strings::urlencode($assocArticle['id'])
        ]);
    }

    $assocUser = null;
    $queryUser = QueryFactory::createInstance(env('database.tables.user'));
    $queryUser->setCommand(QueryAbstract::COMMAND_SELECT);
    $queryUser->setSelect('id');
    $queryUser->setSelect('username');
    $queryUser->addWhere('id', QueryAbstract::escape($assocArticle['id_create']));
    $queryUser->setLimit(1);

    if ($queryUser->execute() && $queryUser->rows() > 0) {
        $assocUser                 = $queryUser->assoc();
        $assocUser['link_profile'] = rewrite('url.profile', [
            'p_user' => '?user=',
            'user'   => Strings::urlencode($assocUser['username'])
        ]);
    } else {
        $assocUser = [
            'id'           => 0,
            'username'     => lng('article.user_unknown'),
            'link_profile' => null
        ];
    }
?>

    <div id="content">
        <div id="content-wrapper">
            <?php echo $breadcrumbs; ?>
            <?php Alert::display(); ?>

            <div id="article-content">
                <div class="title">
                    <h1><span><?php echo Strings::enhtml($assocArticle['title']); ?></span></h1>
                </div>
                <ul class="detail">
                    <li>
                        <span class="icomoon icon-user"></span>
                        <?php if ($assocUser['id'] > 0) { ?>
                            <a href="<?php echo $assocUser['link_profile']; ?>"><span><?php echo $assocUser['username']; ?></span></a>
                        <?php } else { ?>
                            <span><?php echo $assocUser['username']; ?></span>
                        <?php } ?>
                    </li>
                    <li>
                        <span class="icomoon icon-date"></span>
                        <span><?php echo date(env('date.format'), intval($assocArticle['create_at'])); ?></span>
                    </li>
                </ul>
                <div class="content ql-snow"><span class="ql-editor"><?php echo Article::processContentGet($assocArticle['content']); ?></span></div>
                <div class="divider"></div>
                <div class="action">
                    <?php $socials = SettingSystem::getSocialShare(); ?>

                    <?php if (is_array($socials) && count($socials) > 0) { ?>
                        <ul class="share">

                            <?php foreach ($socials AS $socialIcon => $socialUrl) { ?>
                                <?php $socialUrl = str_replace('{$url}', Strings::urlencode($urlArticle), $socialUrl); ?>
                                <?php $socialUrl = str_replace('{$title}', Strings::urlencode($assocArticle['title']), $socialUrl); ?>

                                <li>
                                    <a href="<?php echo $socialUrl; ?>">
                                        <span class="icomoon icon-<?php echo $socialIcon; ?>"></span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <div class="entry">
                    <ul class="list-action">
                        <li>
                            <span class="icomoon icon-layer"></span>
                            <a href="<?php echo $assocArticle['url_forward']; ?>">
                                <span><?php echo Strings::enhtml($assocParent['title']); ?></span>
                            </a>
                        </li>

                        <?php if ($rowsCategory <= 0) { ?>
                            <li class="empty">
                                <span class="icomoon icon-trash"></span>
                                <span><?php echo lng('control.list_category.list.empty_category'); ?></span>
                            </li>
                        <?php } else { ?>
                            <?php while ($assocCategory = $queryCategory->assoc()) { ?>
                                <?php
                                $urlArticle = null;

                                if (empty($assocCategory['url'])) {
                                    $urlArticle = rewrite('url.category', [
                                        'p_seo' => '?seo=',
                                        'p_id'  => '?id=',

                                        'seo' => Strings::urlencode($assocCategory['seo']),
                                        'id'  => Strings::urlencode($assocCategory['id'])
                                    ]);
                                } else {
                                    $urlArticle = $assocCategory['url'];
                                }
                                ?>

                                <li class="entry">
                                    <p class="link">
                                        <span class="icomoon icon-rectange"></span>
                                        <a href="<?php echo $urlArticle; ?>">
                                            <span><?php echo Strings::enhtml($assocCategory['title']); ?></span>
                                        </a>
                                    </p>

                                    <?php if (empty($assocCategory['description']) == false) { ?>
                                        <p class="description">
                                            <span><?php echo Strings::enhtml($assocCategory['description']); ?></span>
                                        </p>
                                    <?php } ?>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>

                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
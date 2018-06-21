<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\Http\Request;
    use Librarys\UI\Alert;
    use Librarys\UI\Paging;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);
    require_once('global.php');

    $id  = 0;
    $seo = null;

    if (isset($_GET['id']))
        $id = intval(Strings::escape($_GET['id']));

    if (isset($_GET['seo']))
        $seo = Strings::urldecode(Strings::escape($_GET['seo']));

    if ($id <= 0 || empty($seo))
        Request::exitResponseCode(404);

    $queryCategory = QueryFactory::createInstance(env('database.tables.category'));
    $queryCategory->setCommand(QueryAbstract::COMMAND_SELECT);
    $queryCategory->addSelect('id');
    $queryCategory->addSelect('id_parent');
    $queryCategory->addSelect('is_parent');
    $queryCategory->addSelect('title');
    $queryCategory->addWhere('id', $id);
    $queryCategory->addWhere('seo', $seo);
    $queryCategory->addWhere('is_hidden', 0);
    $queryCategory->addWhere('is_trash', 0);
    $queryCategory->setLimit(1);

    if ($queryCategory->rows() <= 0)
        Request::exitResponseCode(404);

    $breadcrumbs = HomeCategoryBreadcrumbs::createInstance([
        'id_category' => $id
    ], function() {
        Request::exitResponseCode(404);
    })->display(false);

    $assocCategory = $queryCategory->assoc();

    if ($assocCategory['is_parent']) {
        $assocCategory['url_forward'] = env('app.http_host');
    } else {
        $queryParent = $queryCategory;
        $queryParent->clear();
        $queryParent->setCommand(QueryAbstract::COMMAND_SELECT);
        $queryParent->setSelect('id');
        $queryParent->setSelect('seo');
        $queryParent->addWhere('id', Strings::escape($assocCategory['id_parent']));
        $queryParent->addWhere('is_hidden', 0);
        $queryParent->addWhere('is_trash', 0);

        if ($queryParent->execute() === false || $queryParent->rows() <= 0)
            Request::exitResponseCode(404);

        $assocParent                  = $queryParent->assoc();
        $assocCategory['url_forward'] = rewrite('url.category', [
            'p_seo' => '?seo=',
            'p_id'  => '&id=',

            'seo' => Strings::urlencode($assocParent['seo']),
            'id'  => Strings::urlencode($assocParent['id'])
        ]);
    }

    $queryList = QueryFactory::createInstance(env('database.tables.category'));
    $queryList->setCommand(QueryAbstract::COMMAND_SELECT);
    $queryList->addSelect('id');
    $queryList->addSelect('title');
    $queryList->addSelect('seo');
    $queryList->addSelect('description');
    $queryList->addSelect('url');
    $queryList->addWhere('id_parent', QueryAbstract::escape($assocCategory['id']));
    $queryList->addWhere('is_hidden', 0);
    $queryList->addWhere('is_trash', 0);
    $queryList->setOrderBy('id', QueryAbstract::ORDER_ASC);

    $rowsList = 0;

    if ($queryList->execute() !== false)
        $rowsList = $queryList->rows();

    require_header($assocCategory['title'], ALERT_CATEGORY);

    $rows  = 0;
    $query = QueryFactory::createInstance(env('database.tables.article'));
    $query->setCommand(QueryAbstract::COMMAND_SELECT);
    $query->addWhere('id_category', QueryAbstract::escape($id));
    $query->addWhere('is_hidden', 0);
    $query->addWhere('is_trash', 0);
    $query->addOrderBy('id', 'DESC');

    if ($query->execute() != false)
        $rows = $query->rows();

    $pageCurrent = 1;

    if (isset($_GET['page']) && empty($_GET['page']) == false)
        $pageCurrent = intval(Strings::escape($_GET['page']));

    if ($pageCurrent <= 0)
        $pageCurrent = 1;

    $pageCount = 3;
    $pageTotal = 0;

    $limitStart = 0;
    $limitEnd   = $pageCount;

    if ($rows > $pageCount) {
        $pageTotal = ceil($rows / $pageCount);

        if ($pageTotal <= 0 || $pageCurrent > $pageTotal)
            $pageCurrent = 1;

        $limitStart = ($pageCurrent * $pageCount) - $pageCount;
        $limitEnd   = $pageCount;
    }

    $query->setLimit($limitStart, $limitEnd);
    $query->execute(true);

    $users     = [];
    $paging    = null;
    $queryUser = QueryFactory::createInstance(env('database.tables.user'));

    if ($rows > 0) {
        $paging = new Paging(
            rewrite('url.category', [
                'p_seo' => '?seo=',
                'p_id'  => '&id=',

                'seo' => Strings::urlencode($seo),
                'id'  => Strings::urlencode($id)
            ]),

            rewrite('url.category_page', [
                'p_seo'  => '?seo=',
                'p_id'   => '&id=',
                'p_page' => '&page=',

                'seo'  => Strings::urlencode($seo),
                'id'   => Strings::urlencode($id),
                'page' => '[$page]'
            ]),

            '[$page]'
        );
    }
?>

    <div id="content">
        <div id="content-wrapper">
            <?php echo $breadcrumbs; ?>
            <?php Alert::display(); ?>

            <ul id="article-list">
                <?php if ($rows <= 0) { ?>
                    <li class="empty">
                        <span class="icomoon icon-trash"></span>
                        <span><?php echo lng('home.empty_article'); ?></span>
                    </li>
                <?php } else while (($assoc = $query->assoc()) != null) { ?>
                    <?php
                    $url = null;

                    if (empty($assoc['url']) == false) {
                        $url = $assoc['url'];
                    } else {
                        $url = rewrite('url.article', [
                            'p_seo' => '?seo=',
                            'p_id'  => '?id=',

                            'seo' => Strings::urlencode($assoc['seo']),
                            'id'  => Strings::urlencode($assoc['id'])
                        ]);
                    }
                    ?>

                    <li>
                        <?php if (empty($assoc['thumb'])) { ?>
                            <span class="icomoon icon-camera-off no-thumb"></span>
                        <?php } else { ?>
                            <div class="thumb">
                                <a href="<?php echo $url; ?>">
                                    <img src="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/uploads/thumbs/<?php echo $assoc['thumb']; ?>" class="resolution" />
                                </a>
                            </div>
                        <?php } ?>

                        <div class="title">
                            <a href="<?php echo $url; ?>">
                                <span><?php echo $assoc['title']; ?></span>
                            </a>
                        </div>
                        <ul class="detail">
                            <?php
                                if (isset($users[$assoc['id_create']]) == false) {
                                    $queryUser->clear();
                                    $queryUser->setCommand(QueryAbstract::COMMAND_SELECT);
                                    $queryUser->setSelect('id');
                                    $queryUser->setSelect('username');
                                    $queryUser->addWhere('id', QueryAbstract::escape($assoc['id_create']));
                                    $queryUser->setLimit(1);

                                    $assocUser = null;

                                    if ($queryUser->execute() && $queryUser->rows() > 0) {
                                        $assocUser                 = $queryUser->assoc();
                                        $assocUser['link_profile'] = rewrite('url.profile', [
                                            'p_user' => '?user=',
                                            'user'   => Strings::urlencode($assocUser['username'])
                                        ]);
                                    } else {
                                        $assocUser = [
                                            'id'           => 0,
                                            'username'     => lng('home.user_unknown'),
                                            'link_profile' => null
                                        ];
                                    }

                                    $users[$assoc['id_create']] = $assocUser;
                                }
                            ?>

                            <li>
                                <span class="icomoon icon-user"></span>
                                <?php if ($users[$assoc['id_create']]['id'] > 0) { ?>
                                    <a href="<?php echo $users[$assoc['id_create']]['link_profile']; ?>">
                                        <span><?php echo $users[$assoc['id_create']]['username']; ?></span>
                                    </a>
                                <?php } else { ?>
                                    <span><?php echo $users[$assoc['id_create']]['username']; ?></span>
                                <?php } ?>
                            </li>
                            <li>
                                <span class="icomoon icon-date"></span>
                                <span><?php echo date(env('date.format'), $assoc['create_at']); ?></span>
                            </li>
                        </ul>
                        <div class="content">
                            <span><?php echo Article::processContentDetail($assoc['content']); ?></span>
                        </div>
                    </li>
                <?php } ?>
            </ul>

            <?php if ($pageTotal > 0) { ?>
                <?php $paging->display($pageCurrent, $pageTotal); ?>
            <?php } ?>
        </div>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <div class="entry">
                    <ul class="list-action">
                        <li>
                            <span class="icomoon icon-layer"></span>
                            <a href="<?php echo $assocCategory['url_forward']; ?>">
                                <span><?php echo Strings::enhtml($assocCategory['title']); ?></span>
                            </a>
                        </li>

                        <?php if ($rowsList <= 0) { ?>
                            <li class="empty">
                                <span class="icomoon icon-trash"></span>
                                <span><?php echo lng('control.list_category.list.empty_category'); ?></span>
                            </li>
                        <?php } else { ?>
                            <?php while ($assocList = $queryList->assoc()) { ?>
                                <?php
                                $url = null;

                                if (empty($assocList['url'])) {
                                    $url = rewrite('url.category', [
                                        'p_seo' => '?seo=',
                                        'p_id'  => '?id=',

                                        'seo' => Strings::urlencode($assocList['seo']),
                                        'id'  => Strings::urlencode($assocList['id'])
                                    ]);
                                } else {
                                    $url = $assocList['url'];
                                }
                                ?>

                                <li class="entry">
                                    <p class="link">
                                        <span class="icomoon icon-rectange"></span>
                                        <a href="<?php echo $url; ?>">
                                            <span><?php echo Strings::enhtml($assocList['title']); ?></span>
                                        </a>
                                    </p>

                                    <?php if (empty($assocList['description']) == false) { ?>
                                        <p class="description">
                                            <span><?php echo Strings::enhtml($assocList['description']); ?></span>
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
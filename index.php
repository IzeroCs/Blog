<?php

    define('LOADED', 1);
    require_once('global.php');
    require_header(lng('home.title'), ALERT_HOME);

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\UI\Alert;
    use Librarys\UI\Paging;
    use Librarys\Util\Text\Strings;

    $pageCurrent = 1;

    if (isset($_GET['page']) && empty($_GET['page']) == false)
        $pageCurrent = intval(Strings::escape($_GET['page']));

    if ($pageCurrent <= 0)
        $pageCurrent = 1;

    $rows  = 0;
    $query = QueryFactory::createInstance(env('database.tables.article'));
    $query->setCommand(QueryAbstract::COMMAND_SELECT);
    $query->addWhere('is_hidden', 0);
    $query->addWhere('is_trash', 0);
    $query->addOrderBy('id', 'DESC');

    if ($query->execute() != false)
        $rows = $query->rows();

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
            env('app.http_host'),
            rewrite('url.index', [
                'p_page' => '?page=',
                'page'   => '[$page]'
            ]),

            '[$page]'
        );
    }
?>

    <div id="content">
        <div id="content-wrapper">
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
                <?php
                    $query->clear();
                    $query->setCommand(QueryAbstract::COMMAND_SELECT);
                    $query->setTable(env('database.tables.category'));
                    $query->addSelect('id');
                    $query->addSelect('title');
                    $query->addSelect('seo');
                    $query->addSelect('url');
                    $query->addSelect('description');
                    $query->addWhere('is_hidden', 0);
                    $query->addWhere('is_trash', 0);
                    $query->addWhere('is_parent', 1);
                    $query->addOrderBy('id', QueryAbstract::ORDER_ASC);
                    $query->setLimit(0, 10);
                ?>

                <?php if ($query->execute() != false && $query->rows() > 0) { ?>
                    <div class="entry">
                        <ul class="list-action">
                            <li>
                                <span class="icomoon icon-layer"></span>
                                <span><?php echo lng('home.title_sidebar'); ?></span>
                            </li>

                            <?php while ($assoc = $query->assoc()) { ?>
                                <?php
                                $url = null;

                                if (empty($assoc['url'])) {
                                    $url = rewrite('url.category', [
                                        'p_seo' => '?seo=',
                                        'p_id'  => '?id=',

                                        'seo' => Strings::urlencode($assoc['seo']),
                                        'id'  => Strings::urlencode($assoc['id'])
                                    ]);
                                } else {
                                    $url = $assoc['url'];
                                }
                                ?>

                                <li class="entry">
                                    <p class="link">
                                        <span class="icomoon icon-rectange"></span>
                                        <a href="<?php echo $url; ?>">
                                            <span><?php echo Strings::enhtml($assoc['title']); ?></span>
                                        </a>
                                    </p>

                                    <?php if (empty($assoc['description']) == false) { ?>
                                        <p class="description">
                                            <span><?php echo Strings::enhtml($assoc['description']); ?></span>
                                        </p>
                                    <?php } ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
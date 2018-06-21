<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;
    use Librarys\File\FileSystem;

    define('LOADED', 1);

    require_once('global.php');
    require_header(lng('control.upload_thumb_article.title'), ALERT_CONTROL_UPLOAD_THUMB_ARTICLE);

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
        $queryArticle->addSelect('thumb');
        $queryArticle->addWhere('id', QueryAbstract::escape($id));
        $queryArticle->setLimit(1);

        if ($queryArticle->execute() !== false && $queryArticle->rows() > 0)
            $assocArticle = $queryArticle->assoc();
        else
            Alert::danger(lng('control.upload_thumb_article.alert.article_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    } else {
        Alert::danger(lng('control.upload_thumb_article.alert.article_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    }

    $actionForm = rewrite('url.control.upload_thumb_article', [
        'p' => '?' . PARAMETER_CONTROL_ARTICLE_ID . '=',
        'id'        => Strings::urlencode($id)
    ]);

    $forwardUrl = rewrite('url.control.list_category', [
        'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
        'id'        => Strings::urlencode($assocArticle['id_category'])
    ]);

    $_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID] = $assocArticle['id_category'];

    if (isset($_POST['upload'])) {
        $thumb = null;

        if (isset($_FILES['thumb']))
            $thumb = $_FILES['thumb'];

        if ($thumb == null || empty($thumb['tmp_name'])) {
            Alert::danger(lng('control.upload_thumb_article.alert.not_input_image'));
        } else if ($thumb['size'] > SettingSystem::getMaxSizeThumbUpload()) {
            Alert::danger(lng('control.upload_thumb_article.alert.file_size_large'));
        } else if (in_array($thumb['type'], SettingSystem::getFileMimeThumbUpload()) == false) {
            Alert::danger(lng('control.upload_thumb_article.alert.file_mime_wrong'));
        } else {
            $thumb['name'] = Article::generatorThumbName($thumb['name']);

            if (Article::updateThumb($id, $thumb['tmp_name'], $thumb['name']) == false) {
                Alert::danger(lng('control.upload_thumb_article.alert.update_thumb_failed'));
            } else {
                $assocArticle['thumb'] = $thumb['name'];
                Alert::success(lng('control.upload_thumb_article.alert.update_thumb_success'));
            }
        }
    } else if (isset($_POST['remove'])) {
        if (Article::removeThumb($id) == false) {
            Alert::danger(lng('control.upload_thumb_article.alert.remove_thumb_failed'));
        } else {
            $assocArticle['thumb'] = null;
            Alert::danger(lng('control.upload_thumb_article.alert.remove_thumb_success'));
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
                'id_category' => $assocArticle['id_category']
            ])->display(); ?>
            <?php Alert::display(); ?>

            <div class="form">
                <form action="<?php echo $actionForm; ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>" />
                    <input type="file" name="thumb" id="input-thumb" style="display: none" accept="image/*" data-type="image"/>

                    <ul class="element">
                        <li class="text center" style="display: none" id="notice-choose">
                            <span class="value"></span>
                        </li>
                        <li class="thumb-drag">
                            <label for="input-thumb">
                                <div class="thumb resolution">
                                    <span class="icomoon icon-camera-off"></span>
                                    <?php if (empty($assocArticle['thumb'])) { ?>
                                        <img src=""
                                             alt="<?php echo Strings::enhtml($assocArticle['title']); ?>"
                                             style="display: none"
                                             class="empty-thumb resolution"/>
                                    <?php } else { ?>
                                        <img src="<?php env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/uploads/thumbs/<?php echo $assocArticle['thumb']; ?>"
                                            alt="<?php echo Strings::enhtml($assocArticle['title']); ?>"
                                            class="resolution"/>
                                    <?php } ?>

                                    <div class="tips">
                                        <span><?php echo lng('control.upload_thumb_article.tips'); ?></span>
                                        <span id="default"><?php echo lng('control.upload_thumb_article.default_img'); ?></span>
                                    </div>
                                </div>
                            </label>
                        </li>
                        <li class="button">
                            <button type="submit" name="upload">
                                <span><?php echo lng('control.upload_thumb_article.button.upload'); ?></span>
                            </button>

                            <?php if (empty($assocArticle['thumb']) == false) { ?>
                                <button type="submit" name="remove">
                                    <span><?php echo lng('control.upload_thumb_article.button.remove'); ?></span>
                                </button>
                            <?php } ?>

                            <a href="<?php echo $forwardUrl; ?>">
                                <span><?php echo lng('control.upload_thumb_article.button.cancel'); ?></span>
                            </a>
                        </li>
                    </ul>
                </form>
            </div>
        </div>

        <script type="text/javascript">
            onloads.addLoad(function () {
                var dragElement = document.querySelector("div.thumb");
                var dragInput = document.querySelector("input[type=file]#input-thumb");

                if (dragElement === null || dragInput === null)
                    return;

                var mimes = <?php echo Strings::unescape(json_encode(SettingSystem::getFileMimeThumbUpload())); ?>;
                var sizeMax = <?php echo intval(SettingSystem::getMaxSizeThumbUpload()); ?>;
                var notices = {
                    file_error_mime: "<?php echo lng('control.upload_thumb_article.alert.file_mime_wrong'); ?>",
                    file_error_size: "<?php echo lng('control.upload_thumb_article.alert.file_size_large', 'size', FileSystem::sizeToString(SettingSystem::getMaxSizeThumbUpload())); ?>"
                };

                var imgElement = dragElement.querySelector("img");
                var imgOrigin = imgElement.cloneNode(true);
                var inputOrigin = dragInput.cloneNode(true);
                var defaultButton = dragElement.querySelector("span#default");
                var noticeElement = document.querySelector("li#notice-choose");
                var noticeTextElement = noticeElement.querySelector("span.value");

                function previewImage(files) {
                    var type = files[0].type.toLowerCase();
                    var size = files[0].size;
                    var errorType = true;

                    if (type !== "") {
                        for (var i = 0; i < mimes.length; ++i) {
                            if (mimes[i].toLowerCase().indexOf(type) !== -1) {
                                errorType = false;
                                break;
                            }
                        }
                    }

                    if (errorType || size > sizeMax) {
                        if (errorType)
                            noticeTextElement.innerHTML = notices.file_error_mime;
                        else
                            noticeTextElement.innerHTML = notices.file_error_size;

                        noticeElement.style.display = "block";
                        defaultButton.style.display = "none";
                        imgElement.src = imgOrigin.src;

                        if (imgOrigin.className.indexOf("empty-thumb") === -1)
                            imgElement.style.display = "block";
                        else
                            imgElement.style.display = "none";

                        dragInput.files = inputOrigin.files;

                        return;
                    } else {
                        noticeElement.style.display = "none";
                        noticeTextElement.innerHTML = "";
                    }

                    var reader = new FileReader();

                    reader.onload = function (e) {
                        imgElement.src = e.target.result;
                        imgElement.style.display = "block";
                        defaultButton.style.display = "block";
                    };

                    reader.readAsDataURL(files[0]);
                    dragInput.files = files;
                }

                function enterover(e) {
                    if (e.type === "dragenter") {
                        noticeElement.style.display = "none";
                        noticeTextElement.innerHTML = "";
                    }

                    e.preventDefault();
                    e.stopPropagation();
                }

                function leavedrop(e) {
                    if (e.type === "drop") {
                        var files = e.dataTransfer.files;

                        if (files.length && files.length > 0)
                            previewImage(files);
                    }

                    e.preventDefault();
                    e.stopPropagation();
                }

                apps.addEvent(dragElement, "dragenter", function (e) {
                    enterover(e);
                }, true);

                apps.addEvent(dragElement, "dragleave", function (e) {
                    leavedrop(e);
                }, true);

                apps.addEvent(dragElement, "dragover", function (e) {
                    enterover(e);
                }, true);

                apps.addEvent(dragElement, "drop", function (e) {
                    leavedrop(e);
                }, true);

                apps.addEvent(defaultButton, "click", function (e) {
                    imgElement.src = imgOrigin.src;

                    if (imgOrigin.className.indexOf("empty-thumb") === -1)
                        imgElement.style.display = "block";
                    else
                        imgElement.style.display = "none";

                    dragInput.files = inputOrigin.files;
                    defaultButton.style.display = "none";

                    e.preventDefault();
                    e.stopPropagation();
                }, true);

                apps.addEvent(dragInput, "click", function (e) {
                    noticeElement.style.display = "none";
                    noticeTextElement.innerHTML = "";
                });

                apps.addEvent(dragInput, "change", function (e) {
                    if (dragInput.files.length > 0)
                        previewImage(dragInput.files);

                    e.preventDefault();
                    e.stopPropagation();
                });
            });
        </script>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <?php get_sidebar_list_action(rewrite('url.control.upload_thumb_article')); ?>
                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
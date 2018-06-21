<?php

    use Librarys\Database\QueryAbstract;
    use Librarys\Database\QueryFactory;
    use Librarys\Http\Uri;
    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;

    define('LOADED', 1);
    define('LOADER_QUILL', 1);

    require_once('global.php');
    require_header(lng('control.create_article.title'), ALERT_CONTROL_CREATE_ARTICLE);

    $id = 0;

    if (isset($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID]))
        $id = intval(Strings::urldecode($_GET[PARAMETER_CONTROL_LIST_CATEGORY_ID]));

    $queryParent = null;
    $assocParent = null;
    $actionForm  = null;
    $forwardUrl  = null;

    if ($id > 0) {
        $queryParent = QueryFactory::createInstance(env('database.tables.category'));
        $queryParent->setCommand(QueryAbstract::COMMAND_SELECT);
        $queryParent->addSelect('id');
        $queryParent->addSelect('title');
        $queryParent->addWhere('id', QueryAbstract::escape($id));
        $queryParent->setLimit(1);

        if ($queryParent->execute() !== false && $queryParent->rows() > 0)
            $assocParent = $queryParent->assoc();
        else
            Alert::danger(lng('control.create_article.alert.category_container_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    } else {
        Alert::danger(lng('control.create_article.alert.category_container_not_exists'), ALERT_CONTROL_LIST_CATEGORY, rewrite('url.control.list_category'));
    }

    if ($assocParent != null) {
        $actionForm = rewrite('url.control.create_artcile', [
            'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
            'id'        => Strings::urlencode($id)
        ]);

        $forwardUrl = rewrite('url.control.list_category', [
            'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
            'id'        => Strings::urlencode($id)
        ]);
    } else {
        $actionForm = rewrite('url.control.create_article');
        $forwardUrl = rewrite('url.control.list_category');
    }

    $title    = null;
    $seo      = null;
    $url      = null;
    $contents = null;
    $hidden   = false;
    $length   = 0;

    if (isset($_POST['create'])) {
        $title    = Strings::escape($_POST['title']);
        $seo      = Strings::escape($_POST['seo']);
        $url      = Strings::escape($_POST['url']);
        $contents = $_POST['contents'];

        if (isset($_POST['hidden']))
            $hidden = boolval(Strings::escape($_POST['hidden']));
        else
            $hidden = false;

        if (empty($title)) {
            Alert::danger(lng('control.create_article.alert.not_input_title'));
        } else if (empty($contents)) {
            Alert::danger(lng('control.create_article.alert.not_input_content'));
        } else {
            $seoTitle    = QueryAbstract::escape(Uri::seo($title));
            $queryCreate = QueryFactory::createInstance(env('database.tables.article'));

            $queryCreate->setCommand(QueryAbstract::COMMAND_SELECT);
            $queryCreate->addSelect('id');
            $queryCreate->addSelect('title');
            $queryCreate->addSelect('seo');
            $queryCreate->addWhere('title', $title);
            $queryCreate->addWhere('seo', $seoTitle, QueryAbstract::OPERATOR_EQUAL, QueryAbstract::WHERE_OR);
            $queryCreate->setLimit(1);

            if ($queryCreate->execute() === false) {
                Alert::danger(lng('control.create_article.alert.create_category_failed'));
            } else if ($queryCreate->rows() > 0) {
                $assocCreate = $queryCreate->assoc();

                if (strcasecmp($title, $assocCreate['title']) === 0)
                    Alert::danger(lng('control.create_article.alert.title_category_exists'));
                else if (strcasecmp($seoTitle, $assocCreate['seo']) === 0)
                    Alert::danger(lng('control.create_article.alert.seo_category_exists'));
                else
                    Alert::danger(lng('control.create_article.alert.create_category_failed'));
            } else {
                $contents = Article::processQuillContentPost($contents, $title);
                $contents = Strings::escape($contents);

                $queryCreate->clear();
                $queryCreate->setCommand(QueryAbstract::COMMAND_INSERT_INTO);
                $queryCreate->setLimit(1);

                $queryCreate->addDataArray([
                    'id_create'   => QueryAbstract::escape(User::getAssocId()),
                    'id_category' => QueryAbstract::escape($assocParent['id']),
                    'id_modify'   => 0,
                    'is_hidden'   => $hidden,
                    'is_trash'    => 0,
                    'title'       => $title,
                    'seo'         => $seoTitle,
                    'url'         => $url,
                    'content'     => $contents,
                    'thumb'       => null,
                    'view'        => 0,
                    'create_at'   => time(),
                    'modify_at'   => 0
                ]);

                if ($queryCreate->execute() !== false) {
                    $urlGoto = rewrite('url.control.upload_thumb_article', [
                        'p' => '?' . PARAMETER_CONTROL_ARTICLE_ID . '=',
                        'id'        => $queryCreate->insertId()
                    ]);

                    Alert::success(lng('control.create_article.alert.create_article_success', 'name', $title), ALERT_CONTROL_UPLOAD_THUMB_ARTICLE, $urlGoto);
                } else {
                    $contents = Strings::unescape($contents);
                }

                Alert::danger(lng('control.create_article.alert.create_article_failed'));
            }
        }

        $title    = Strings::unescape($title);
        $seo      = Strings::unescape($seo);
        $url      = Strings::unescape($url);
    }
?>

    <div id="content">
        <div id="content-wrapper">
            <?php ControlCategoryBreadcrumbs::createInstance([
                'default_url' => rewrite('url.control.create_article'),

                'begin_url' => rewrite('url.control.create_article', [
                    'p' => '?' . PARAMETER_CONTROL_LIST_CATEGORY_ID . '=',
                    'id'        => null
                ]),

                'end_url'     => null,
                'id_category' => $id
            ])->display(); ?>
            <?php Alert::display(); ?>

            <form action="<?php echo $actionForm; ?>" method="post" id="form-editor" class="not-loaded">
                <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>" />
                <input type="hidden" name="create" value="<?php echo lng('control.create_article.button.create'); ?>"/>

                <div class="form padding-zero" id="editor-quill">
                    <textarea name="contents" id="contents" style="display: none"></textarea>
                    <div id="editor-wrapper"></div>
                </div>
                <div class="form" id="form-detail">
                    <ul class="element">
                        <li class="text">
                            <span class="label"><?php echo lng('control.create_article.category_container'); ?></span>
                            <span class="value"><?php echo Strings::enhtml($assocParent['title']); ?></span>
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.create_article.input.label.title'); ?></span>
                            <input type="text" name="title" value="<?php echo Strings::enhtml($title); ?>" placeholder="<?php echo lng('control.create_article.input.placeholder.input_title'); ?>" />
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.create_article.input.label.seo'); ?></span>
                            <input type="text" name="seo" value="<?php echo Strings::enhtml($seo); ?>" placeholder="<?php echo lng('control.create_article.input.placeholder.input_seo'); ?>" />
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.create_article.input.label.url'); ?></span>
                            <input type="text" name="url" value="<?php echo Strings::enhtml($url); ?>" placeholder="<?php echo lng('control.create_article.input.placeholder.input_url'); ?>" />
                        </li>
                        <li class="checkbox">
                            <span><?php echo lng('control.create_article.input.checkbox.options.title'); ?></span>

                            <ul>
                                <li>
                                    <input type="checkbox" id="label_hidden" name="hidden" value="1"<?php if ($hidden) { ?> checked="checked" <?php } ?> />
                                    <label for="label_hidden">
                                        <span><?php echo lng('control.create_article.input.checkbox.options.hidden_article'); ?></span>
                                    </label>
                                </li>
                            </ul>
                        </li>
                        <li class="button">
                            <button type="submit" name="create">
                                <span><?php echo lng('control.create_article.button.create'); ?></span>
                            </button>
                            <a href="<?php echo $forwardUrl; ?>">
                                <span><?php echo lng('control.create_article.button.cancel'); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </form>
            <script type="text/javascript">
                if (typeof onloads !== "undefined") {
                    onloads.addLoad(function () {
                        var quillWrapper = document.getElementById("editor-wrapper");

                        if (quillWrapper === null)
                            return;

                        var quill = apps.quill.create(quillWrapper);
                        var editorQuill = document.getElementById("editor-quill");
                        var detailForm = document.querySelector("div#form-detail");

                        if (detailForm === null || editorQuill === null)
                            return;

                        var buttonCreate = detailForm.querySelector("button[name=create]");
                        var textarea = editorQuill.querySelector("textarea#contents");
                        var qlEditor = editorQuill.querySelector("div.ql-editor");
                        var formEditor = document.getElementById("form-editor");

                        var buttonHandle = function (e) {
                            if (textarea !== null && qlEditor !== null) {
                                var imgs = qlEditor.getElementsByTagName("img");

                                var handle = function () {
                                    textarea.innerHTML = qlEditor.innerHTML;
                                    buttonCreate.removeEventListener("click", buttonHandle);
                                    formEditor.submit();
                                };

                                if (imgs.length > 0) {
                                    var length = imgs.length;
                                    var datas = {};
                                    var ajax = null;

                                    datas["<?php echo cfsrTokenName(); ?>"] = "<?php echo cfsrTokenValue(); ?>";
                                    datas["upload"] = "Upload";
                                    datas["contents"] = null;

                                    for (var i = 0; i < length; ++i) {
                                        datas.contents = imgs[i].src;

                                        if (imgs[i].src.indexOf("base64") !== -1) {
                                            ajax = apps.ajax.open({
                                                url: "<?php echo env('app.http_host'); ?>/control/upload_image_tmp.php",
                                                method: "POST",
                                                datas: datas,

                                                params: {
                                                    img: imgs[i],
                                                    index: i,
                                                    length: length,
                                                    handle: handle
                                                },

                                                error: function (params, xhr) {
                                                    if (params.index + 1 >= params.length)
                                                        params.handle();
                                                },

                                                success: function (data, params, xhr) {
                                                    try {
                                                        var object = JSON.parse(data);

                                                        if (object.success) {
                                                            params.img.src = object.fileurl;
                                                            params.img.setAttribute("filename", object.filename);
                                                        }
                                                    } catch (e) {

                                                    }

                                                    if (params.index + 1 >= params.length)
                                                        params.handle();
                                                }
                                            });
                                        } else if (i + 1 >= length) {
                                            handle();
                                        }
                                    }
                                } else {
                                    handle();
                                }
                            }

                            e.stopPropagation();
                            e.preventDefault();

                            return false;
                        };

                        apps.addEvent(buttonCreate, "click", buttonHandle, true);

                        if (qlEditor !== null)
                            qlEditor.innerHTML = "<?php echo Strings::escape($contents); ?>";

                        var elementContent = document.querySelector("div#content");
                        var elementToolbar = editorQuill.querySelector("div.ql-toolbar");
                        var elementHeader = document.querySelector("div#header");
                        var elementEditorWrapper = document.querySelector("div#editor-wrapper");
                        var toolbarClone = null;

                        var eventScroll = function (e) {
                            console.log(e);

                            var rectHeader = elementHeader.getBoundingClientRect();
                            var rectToolbar = elementToolbar.getBoundingClientRect();
                            var rectEditorWrapper = elementEditorWrapper.getBoundingClientRect();

                            if (rectEditorWrapper.top - rectToolbar.height < rectHeader.height) {
                                if (elementToolbar.style.position !== "fixed") {
                                    toolbarClone = document.createElement("div");
                                    toolbarClone.style.height = rectToolbar.height + "px";

                                    elementToolbar.parentNode.insertBefore(toolbarClone, elementToolbar);
                                    elementToolbar.style.position = "fixed";
                                    elementToolbar.style.left = rectEditorWrapper.left + "px";
                                    elementToolbar.style.width = rectEditorWrapper.width + "px";
                                    elementToolbar.style.top = rectHeader.height + "px";
                                    elementToolbar.classList.add("shadow");
                                }
                            } else if (toolbarClone !== null) {
                                toolbarClone.remove();
                                elementToolbar.style.display = "block";
                                elementToolbar.style.position = "relative";
                                elementToolbar.style.top = 0;
                                elementToolbar.style.left = 0;
                                elementToolbar.classList.remove("shadow");
                            }
                        };

                        if (elementContent !== null && elementToolbar !== null && elementHeader !== null)
                            apps.addEvent(elementContent, "scroll", eventScroll, true);
                    });
                }
            </script>
        </div>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <?php get_sidebar_list_action(rewrite('url.control.create_article')); ?>
                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
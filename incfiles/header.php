<?php use Librarys\UI\Alert; ?>

<!DOCTYPE html>
<html>
<head>
    <?php $titleGlobal = null; ?>
    <?php $titleSub = SettingSystem::getSubTitle(); ?>
    <?php if (isset($GLOBALS['titleHeader'])) $titleGlobal = $GLOBALS['titleHeader']; ?>

    <?php if (empty($titleGlobal)) { ?>
        <?php $titleGlobal = $titleSub; ?>
    <?php } else if (empty($titleSub) == false) { ?>
        <?php $titleGlobal = $titleGlobal . $titleSub; ?>
    <?php } ?>

    <title><?php echo $titleGlobal; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <style type="text/css">
        body {
            opacity: 0;
        }
    </style>

    <?php if (defined('ROBOTS') && ROBOTS == false) { ?>
        <meta name="robots" content="noindex, nofollow, noodp, nodir" />
    <?php } else { ?>
        <meta name="robots" content="index, follow, noodp, nodir" />
    <?php } ?>

    <meta http-equiv="Cache-Control" content="private, max-age=0, no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="Thu, 01 Jan 1970 00:00:00 GMT" />

    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/images/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/images/favicon/favicon-16x16.png" />
    <link rel="icon" type="image/x-icon" href="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/images/favicon/favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/images/favicon/favicon.ico" />

    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Noto+Sans" />
    <link rel="stylesheet" type="text/css" href="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/themes/style.css?<?php echo time(); ?>" media="all,handheld" />

    <script type="text/javascript">
        var onloads = (function () {
            var onls = [];
            var ivs = [];
            var loopCount = 0;
            var loopInterval = null;

            function runHandle(arrays) {
                if (loopInterval !== null) {
                    clearInterval(loopInterval);
                    loopInterval = null;
                }

                var removes = [];
                var i = 0;

                for (i = 0; i < arrays.length; ++i) {
                    var handle = arrays[i];

                    if (typeof handle === "function") {
                        // Result is remove element, false = remove
                        var result = handle();

                        if (typeof result !== "undefined" && result === false)
                            removes.push(i);
                    }
                }

                if (removes.length <= 0)
                    return;

                for (i = removes.length - 1; i >= 0; --i)
                    arrays.splice(removes[i], 1);
            }

            return {
                run: function () {
                    loopInterval = setInterval(function () {
                        if (++loopCount >= 1000) {
                            clearInterval(loopInterval);
                            throw 'Crash loop';
                        } else {
                            console.log("Success load, not loop");
                        }
                    }, 10);

                    if (window.addEventListener)
                        window.addEventListener("load", onloads.runLoad);
                    else
                        window.attachEvent("load", onloads.runLoad);
                },

                addLoad: function (handle) {
                    onls.push(handle);
                },

                addInvoke: function (handle) {
                    ivs.push(handle);
                },

                runLoad: function () {
                    runHandle(onls);
                },

                runInvoke: function () {
                    runHandle(ivs);
                }
            };
        })();

        onloads.addLoad(function () {
            apps.init({
                http_host: "<?php echo env('app.http_host'); ?>",
                search_url: "search.php",

                search_rewrite_urls: {
                    article: "<?php echo rewrite('url.article', [
                        'p_seo' => '?seo=',
                        'p_id'  => '&id'
                    ], false); ?>"
                },

                alert_types: {
                    danger: "<?php echo Alert::DANGER; ?>",
                    success: "<?php echo Alert::SUCCESS; ?>",
                    warning: "<?php echo Alert::WARNING; ?>",
                    info: "<?php echo Alert::INFO; ?>"
                }
            });

            apps.reloadCfsrToken();
            apps.initHistoryScript("javascripts/history.js");
            apps.loaded.init();
            apps.loaded.reinitLoadTagA();
            apps.loaded.reinitLoadTagForm();
            apps.search.init();

            return false;
        });

        onloads.addInvoke(function () {
            apps.reloadCfsrToken();
            apps.loaded.reinitLoadTagA();
            apps.loaded.reinitLoadTagForm();
            apps.search.reinit();
        });

        onloads.run();
    </script>

    <?php if (defined('LOADER_QUILL')) { ?>
        <link rel="stylesheet" type="text/css" href="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/themes/quill.css?<?php echo time(); ?>" media="all,handheld" />

    <?php if (defined('NOT_LOADER_QUILL_SCRIPT') == false) { ?>
        <script src="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/javascripts/quill.js" type="text/javascript"></script>
    <?php } ?>
    <?php } ?>

    <script type="text/javascript" src="<?php echo env('app.http_host'); ?>/resource/<?php echo cfsrTokenValue(); ?>/javascripts/app.js?<?php echo time(); ?>"></script>
</head>
<body>
<div id="progressbar" data-token-name="<?php echo cfsrTokenName(); ?>" data-token-value="<?php echo cfsrTokenValue(); ?>"></div>
<div id="master">
    <div id="header">
        <span class="icomoon icon-menu" id="menu"></span>
        <ul id="action">
            <?php if (User::isSignIn()) { ?>
                <li>
                    <a href="<?php echo rewrite('url.control.home'); ?>">
                        <span>CONTROL</span>
                    </a>
                </li>
            <?php } else { ?>
                <?php if (defined('SIGN') == false || SIGN !== 1) { ?>
                    <li>
                        <a href="<?php echo rewrite('url.sign_in'); ?>">
                            <span>SIGN IN</span>
                        </a>
                    </li>
                <?php } ?>

                <?php if ((defined('SIGN') == false || SIGN !== 2) && SettingSystem::isEnableSignUp()) { ?>
                    <li>
                        <a href="<?php echo rewrite('url.sign_up'); ?>">
                            <span>SIGN UP</span>
                        </a>
                    </li>
                <?php } ?>
            <?php } ?>
            <li class="search">
                <span class="icomoon icon-search" id="button-toggle-search"></span>
                <div class="box" id="box-search">
                    <div class="form-search">
                        <form action="#" method="post" class="not-loaded" onsubmit="return false">
                            <input type="text" id="search-input" name="keyword" value="" placeholder="<?php echo lng('search.input.placeholder.input_keyword'); ?>" />
                            <button type="submit" name="search">
                                <span class="icomoon icon-search"></span>
                            </button>
                        </form>
                        <ul class="result">
                            <li class="empty">
                                <span><?php echo lng('search.alert.empty_result'); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
            <?php if (User::isSignIn()) { ?>
                <li class="user">
                    <a href="#">
                        <span class="icomoon icon-user"></span>
                    </a>
                </li>
                <li class="user">
                    <a href="<?php echo rewrite('url.sign_out'); ?>">
                        <span class="icomoon icon-shutdown"></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
    <div id="container">
        <div id="top-section">
            <a href="<?php echo env('app.http_host'); ?>">
                <h1 class="logo">IzeroCs</h1>
            </a>
        </div>
        <div id="wrapper">
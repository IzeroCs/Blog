<?php

    define('LOADED', 1);

    use Librarys\UI\Alert;
    use Librarys\Util\Text\Strings;
    use Librarys\File\FileSystem;

    require_once('global.php');
    require_header('Cài đặt hệ thống', ALERT_CONTROL_SETTING_SYSTEM);

    $actionForm = rewrite('url.control.setting_system');
    $forwardUrl = rewrite('url.control.home');

    $subtitle            = SettingSystem::getSubTitle();
    $description         = SettingSystem::getDescription();
    $keyword             = SettingSystem::getKeyword();
    $maxSizeThumbUpload  = SettingSystem::getMaxSizeThumbUpload();
    $fileMimeThumbUpload = SettingSystem::getFileMimeThumbUpload();
    $splitFileMimeThumbUpload = ',';

    if (is_array($fileMimeThumbUpload) && count($fileMimeThumbUpload) > 0) {
        $bufferMimeThumbUpload = null;
        $countMimeThumbUpload = count($fileMimeThumbUpload);

        foreach ($fileMimeThumbUpload AS $index => $mime) {
            $bufferMimeThumbUpload .= $mime;

            if ($index + 1 < $countMimeThumbUpload)
                $bufferMimeThumbUpload .= $splitFileMimeThumbUpload;
        }

        $fileMimeThumbUpload = $bufferMimeThumbUpload;
    } else {
        $fileMimeThumbUpload = null;
    }

    if (isset($_POST['change'])) {
        $subtitle    = Strings::escape($_POST['subtitle']);
        $description = Strings::escape($_POST['description']);
        $keyword     = Strings::escape($_POST['keyword']);

        $subtitle    = Strings::unescape($subtitle);
        $description = Strings::unescape($description);
        $keyword     = Strings::unescape($keyword);
    }
?>

    <div id="content">
        <div id="content-wrapper">
            <?php Alert::display(); ?>

            <div class="form">
                <form action="<?php echo $actionForm; ?>" method="post">
                    <input type="hidden" name="<?php echo cfsrTokenName(); ?>" value="<?php echo cfsrTokenValue(); ?>" />

                    <ul class="element">
                        <li class="input">
                            <span><?php echo lng('control.setting_system.input.label.subtitle'); ?></span>
                            <input type="text" name="subtitle" value="<?php echo $subtitle; ?>" placeholder="<?php echo lng('control.setting_system.input.placeholder.input_subtitle'); ?>" />
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.setting_system.input.label.description'); ?></span>
                            <input type="text" name="description" value="<?php echo $description; ?>" placeholder="<?php echo lng('control.setting_system.input.placeholder.input_description'); ?>" />
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.setting_system.input.label.keyword'); ?></span>
                            <input type="text" name="keyword" value="<?php echo $keyword; ?>" placeholder="<?php echo lng('control.setting_system.input.placeholder.input_keyword'); ?>" />
                        </li>
                        <li class="select">
                            <span><?php echo lng('control.setting_system.input.label.max_size_thumb_upload'); ?></span>
                            <div class="select">
                                <select name="max_size_thumb_upload">
                                    <?php $sizeOrigin = 1024 * 1024; ?>

                                    <?php for ($i = 1; $i <= 5; ++$i) { ?>
                                        <?php $sizeValue = $sizeOrigin * $i; ?>
                                        <option value="<?php echo $sizeValue; ?>"><?php echo FileSystem::sizeToString($sizeValue); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </li>
                        <li class="input">
                            <span><?php echo lng('control.setting_system.input.label.file_mime_thumb_upload', 'symbol', $splitFileMimeThumbUpload); ?></span>
                            <input type="text" name="file_mime_thumb_upload" value="<?php echo $fileMimeThumbUpload; ?>" placeholder="<?php echo lng('control.setting_system.input.placeholder.input_file_mime_thumb_upload'); ?>" />
                        </li>
                        <li class="button">
                            <button type="submit" name="change">
                                <span><?php echo lng('control.setting_system.button.change'); ?></span>
                            </button>
                            <a href="<?php echo $forwardUrl; ?>">
                                <span><?php echo lng('control.setting_system.button.cancel'); ?></span>
                            </a>
                        </li>
                    </ul>
                </form>
            </div>
        </div>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <?php get_sidebar_list_action(); ?>
                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
<?php

    use Librarys\Http\Request;

    define('LOADED', 1);

    require_once('global.php');
    Request::redirect('list_category.php');
    require_header('Bảng điều khiển');
?>

    <div id="content">
        <div id="content-wrapper">

        </div>

        <div id="sidebar-wrapper">
            <div class="sidebar">
                <?php get_control_sidebar_list_action(); ?>
                <?php get_sidebar_about_development(); ?>
                <?php get_sidebar_info(); ?>
            </div>
        </div>
    </div>

<?php require_footer(); ?>
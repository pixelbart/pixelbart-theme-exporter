<?php

defined('ABSPATH') or exit;

$active_tab = 'themes';

if (isset($_GET['tab']) && $_GET['tab'] === 'plugins') {
    $active_tab = 'plugins';
}
?>


<div class="wrap">
    <h1><?php echo esc_html__('Pixelbart Theme Exporter', 'pixelbart-theme-exporter'); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="<?= esc_url(add_query_arg('tab', 'themes')) ?>" class="nav-tab <?php echo $active_tab == 'themes' ? 'nav-tab-active' : ''; ?>">
            Themes
        </a>
        <a href="<?= esc_url(add_query_arg('tab', 'plugins')) ?>" class="nav-tab <?php echo $active_tab == 'plugins' ? 'nav-tab-active' : ''; ?>">
            Plugins
        </a>
    </h2>

    <div class="tabs-content">

        <?php
        switch ($active_tab) {
            case 'plugins':
                include PIXELBART_THEME_EXPORTER_PATH . 'templates/plugins.php';
                break;
            default:
                include PIXELBART_THEME_EXPORTER_PATH . 'templates/themes.php';
        }
        ?>

    </div>
</div>

<script>
(function($) {
    $('a[data-download]').each(function() {
        var downloadUrl = $(this).attr('href');
        $(this).removeAttr('href');
        $(this).on('click', function() {
            window.open(downloadUrl, '_blank');
        }).trigger('click');
    });
})(jQuery);
</script>
<?php

defined('ABSPATH') or exit;

$themes = wp_get_themes();
$defaultVersion = sprintf('%d.%d.%d.%d', 2, date('Y'), date('W'), 1);
?>
<form method="post">
    <?php wp_nonce_field('pixelbart-theme-exporter-nonce'); ?>
    <input type="hidden" name="action" value="export_theme">

    <p>
        <label for="id"><?php echo esc_html__('Theme:', 'pixelbart-theme-exporter'); ?></label>
        <select name="id" id="id" required>
            <?php foreach ($themes as $id => $theme) : ?>
                <option value="<?= esc_attr($id) ?>"><?= esc_html($theme->get('Name')) ?> [<?= esc_html($theme->get('Version')) ?>]</option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="version"><?php echo esc_html__('New version:', 'pixelbart-theme-exporter'); ?></label>
        <input type="text" name="version" id="version" value="<?=$_POST['version'] ?? $defaultVersion?>" required>
    </p>

    <p>
        <input type="submit" value="<?php echo esc_html__('Export theme', 'pixelbart-theme-exporter'); ?>" class="button-primary" />
    </p>
</form>

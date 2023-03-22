<?php

defined('ABSPATH') or exit;

$plugins = get_plugins();
?>
<form method="post">
    <?php wp_nonce_field('pixelbart-theme-exporter-nonce'); ?>
    <input type="hidden" name="action" value="export_plugin">

    <p>
        <label for="id"><?php echo esc_html__('Plugin:', 'pixelbart-theme-exporter'); ?></label>
        <select name="id" id="id" required>
            <?php foreach ($plugins as $id => $plugin) : ?>
                <option value="<?= esc_attr($id) ?>"><?= esc_html($plugin['Name']) ?> [<?= esc_html($plugin['Version']) ?>]</option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="version"><?php echo esc_html__('New version:', 'pixelbart-theme-exporter'); ?></label>
        <input type="text" name="version" id="version" value="" required>
    </p>

    <p>
        <input type="submit" value="<?php echo esc_html__('Export plugin', 'pixelbart-theme-exporter'); ?>" class="button-primary" />
    </p>
</form>
<?php
/*
Plugin Name: Pixelbart Theme Exporter
Plugin URI: https://pixelbart.de
Description: This plugin allows you to export the currently active WordPress theme as a ZIP file and optionally update the version number in its style.css file. It adds a tool to the WordPress admin area under Tools.
Version: 2.4.0
Tested up to: WordPress 6.1.1
Requires at least: WordPress 4.0
Author: Pixelbart
Author URI: https://pixelbart.de
License: MIT License
License URI: https://opensource.org/licenses/MIT
Text Domain: pixelbart-theme-exporter
*/

defined('ABSPATH') or exit;

define('PIXELBART_THEME_EXPORTER_PATH', plugin_dir_path( __FILE__ ));

/**
 * This function adds a new submenu page to the WordPress admin area under "Tools", using the
 * `add_submenu_page()` function. The new submenu page is titled "Theme Exporter" and has a slug
 * of "pxbt-theme-exporter". The page callback function is defined as `pxbt_exporter_adminpage()`,
 * which generates the content for the page. The function is wrapped in an anonymous function to
 * use as a callback for the `admin_menu` action hook.
 *
 * @return void
 */
add_action('admin_menu', function () {
    $name = esc_html__('Theme Exporter', 'pixelbart-theme-exporter');
    add_submenu_page('tools.php', $name, $name, 'manage_options', 'pxbt-theme-exporter', 'pxbt_exporter_adminpage');
});

/**
 * Retrieve the version number of the active WordPress theme.
 *
 * This function uses the wp_get_theme() function to retrieve the active theme
 * object, and then returns its version number. If the active theme version is not
 * available, an empty string will be returned.
 *
 * @return string The version number of the active WordPress theme, or an empty string.
 */
function pxbt_exporter_theme_version()
{
    $theme = wp_get_theme();
    return $theme->get('Version');
}

/**
 * Increment the version number of a WordPress theme by 0.0.1, following the format major.minor.patch.build.
 *
 * @param string $version The current version number to increment.
 *
 * @return string The updated version number with the build number incremented by 1.
 */
function pxbt_exporter_next_theme_version($version)
{
    $parts = explode('.', $version);
    $last_index = count($parts) - 1;
    $parts[$last_index]++;

    if ($parts[$last_index] == 10) {
        $parts[$last_index] = 0;

        if ($last_index > 0) {
            $prev_index = $last_index - 1;
            $parts[$prev_index]++;

            for ($i = $prev_index + 1; $i < $last_index; $i++) {
                if ($parts[$i] > 9) {
                    $parts[$i] = 0;
                }
            }
        }
    }

    $updated_version = implode('.', $parts);

    if (count($parts) == 2) {
        $updated_version .= '.0';
    }

    return $updated_version;
}

/**
 * Exports a WordPress theme with the specified name and version to a ZIP archive.
 *
 * @param string $theme_name    The name of the theme to export.
 * @param string $theme_version The version of the theme to export.
 *
 * @return void
 */
function pxbt_export_theme(string $theme_name, string $theme_version)
{
    $theme_dir = get_theme_root() . '/' . $theme_name;
    $temp_theme_dir = plugin_dir_path(__FILE__) . 'temp_theme/' . $theme_name;
    $temp_zip_dir = plugin_dir_path(__FILE__) . 'temp_zip/';

    if (!file_exists($temp_theme_dir)) {
        mkdir($temp_theme_dir, 0755, true);
    } elseif (!is_writable($temp_theme_dir)) {
        chmod($temp_theme_dir, 0755);
    }

    pxbt_exporter_recursive_remove_directory($temp_theme_dir);
    pxbt_exporter_recursive_copy($theme_dir, $temp_theme_dir);

    $style_css = file_get_contents($temp_theme_dir . '/style.css');
    $style_css = preg_replace('/(Version:\s*)[0-9]+(\.[0-9]+){0,4}/', '${1}' . $theme_version, $style_css);

    file_put_contents($temp_theme_dir . '/style.css', $style_css);

    if (!file_exists($temp_zip_dir)) {
        mkdir($temp_zip_dir, 0755, true);
    } elseif (!is_writable($temp_zip_dir)) {
        chmod($temp_zip_dir, 0755);
    }

    $zip_filename = $temp_zip_dir . $theme_name . '.zip';

    if (file_exists($zip_filename)) {
        unlink($zip_filename);
    }

    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($temp_theme_dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($temp_theme_dir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();

    $download_url = plugins_url('temp_zip/' . $theme_name . '.zip', __FILE__);
    $message = __('The current WordPress theme has been exported as %s and can be downloaded here: <a href="%s" target="_blank" data-download>Download archiv</a>.', 'pixelbart-theme-exporter');
    $message = sprintf($message, esc_html(basename($zip_filename)), esc_url($download_url));

    echo '<div class="notice notice-success is-dismissible"><p>';
    echo $message;
    echo '</p></div>';
}

/**
 * Exports a WordPress plugin to a ZIP file with the specified version number.
 *
 * @param string $plugin_file The file path relative to the plugins directory of the plugin to export.
 * @param string $plugin_version The new version number to use in the plugin header.
 *
 * @return void
 */
function pxbt_export_plugin(string $plugin_file, string $plugin_version)
{
    $plugin_dir = WP_PLUGIN_DIR . '/' . dirname($plugin_file);
    $temp_plugin_dir = plugin_dir_path(__FILE__) . 'temp_plugin/' . dirname($plugin_file);
    $temp_zip_dir = plugin_dir_path(__FILE__) . 'temp_zip/';

    if (!file_exists($temp_plugin_dir)) {
        mkdir($temp_plugin_dir, 0755, true);
    } elseif (!is_writable($temp_plugin_dir)) {
        chmod($temp_plugin_dir, 0755);
    }

    pxbt_exporter_recursive_remove_directory($temp_plugin_dir);
    pxbt_exporter_recursive_copy($plugin_dir, $temp_plugin_dir);

    $plugin_file_path = $temp_plugin_dir . '/' . basename($plugin_file);
    $plugin_php = file_get_contents($plugin_file_path);

    // Use a regular expression to find and replace the version number
    $plugin_php = preg_replace('/^(\s*\*?\s*Version:\s+)[0-9.]+/m', '${1}' . $plugin_version, $plugin_php);

    file_put_contents($plugin_file_path, $plugin_php);

    if (!file_exists($temp_zip_dir)) {
        mkdir($temp_zip_dir, 0755, true);
    } elseif (!is_writable($temp_zip_dir)) {
        chmod($temp_zip_dir, 0755);
    }

    $zip_filename = $temp_zip_dir . basename($plugin_file, '.php') . '.zip';

    if (file_exists($zip_filename)) {
        unlink($zip_filename);
    }

    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($temp_plugin_dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($temp_plugin_dir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();

    $download_url = plugins_url('temp_zip/' . basename($plugin_file, '.php') . '.zip', __FILE__);
    $message = __('The current WordPress plugin has been exported as %s and can be downloaded here: <a href="%s" target="_blank" data-download>Download archive</a>.', 'pixelbart-plugin-exporter');
    $message = sprintf($message, esc_html(basename($zip_filename)), esc_url($download_url));

    echo '<div class="notice notice-success is-dismissible"><p>';
    echo $message;
    echo '</p></div>';
}

/**
 * Renders the exporter admin page.
 * If nonce is verified, handles form submissions by calling the corresponding
 * export function based on the selected action.
 * 
 * @return void
 */
function pxbt_exporter_adminpage()
{
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'pixelbart-theme-exporter-nonce')) {
        $version = sanitize_text_field($_POST['version']);
        $action = (isset($_POST['action'])) ? $_POST['action'] : 'export_theme';
        $id = (isset($_POST['id'])) ? $_POST['id'] : null;
    
        switch ($action) {
            case 'export_theme':
                pxbt_export_theme($id, $version);
                break;
            case 'export_plugin':
                pxbt_export_plugin($id, $version);
                break;
        }
    }
       
    pxbt_exporter_the_form();
}

/**
 * This function generates the HTML form used to input the desired version number for the theme export,
 * and displays the current theme version as well. It first retrieves the current version number using
 * the `pxbt_exporter_theme_version()` function, and then generates the form fields, including a nonce
 * field for security. The function also includes some HTML formatting and internationalization functions.
 */
function pxbt_exporter_the_form()
{
    include PIXELBART_THEME_EXPORTER_PATH . 'templates/index.php';
}

/**
 * This function recursively copies a directory from the source path to the destination path,
 * including all subdirectories and files. It uses PHP's built-in `opendir()` and `readdir()`
 * functions to traverse the directory structure, and copies each file or folder individually
 * using PHP's `copy()` function. If the destination folder does not exist, it is created using
 * `mkdir()`. The function does not return a value, but modifies the file system.
 *
 * @param string $src The path to the source directory to copy from.
 * @param string $dst The path to the destination directory to copy to.
 * @return void
 */
function pxbt_exporter_recursive_copy($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst);

    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..' && $file != 'temp_plugin' && $file != 'temp_theme') { // Exclude the temporary plugin directory
            if (is_dir($src . '/' . $file)) {
                pxbt_exporter_recursive_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }

    closedir($dir);
}


/**
 * This function recursively removes a directory and all its contents, including all subdirectories
 * and files. It uses PHP's built-in `scandir()` function to list all files in the directory, and then
 * iterates over each file, removing them recursively using either the `pxbt_exporter_recursive_remove_directory()`
 * function (if the file is a directory), or the `unlink()` function (if the file is a regular file). If the
 * `$exclusions` parameter is set, any files or directories in that array will be skipped during the removal
 * process. The function returns `false` if the directory does not exist, or `true` if the directory was
 * successfully removed.
 *
 * @param string $dir The path to the directory to remove.
 * @param array $exclusions An optional array of files or directories to exclude from the removal process.
 * @return bool Returns `true` if the directory was successfully removed, `false` otherwise.
 */
function pxbt_exporter_recursive_remove_directory($dir, $exclusions = [])
{
    if (!is_dir($dir)) {
        return false;
    }

    // Remove any excluded files or directories from the list of files to be removed.
    $files = array_diff(scandir($dir), ['.', '..'], $exclusions);

    foreach ($files as $file) {
        $path = $dir . '/' . $file;

        if (is_dir($path)) {
            pxbt_exporter_recursive_remove_directory($path, $exclusions);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}
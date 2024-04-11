# Pixelbart Theme Exporter

This plugin allows you to export a desired theme or plugin by specifying a version number. The version number is automatically replaced in the style.css file of the theme or in the plugin header of the plugin file.

## Installation

1. Upload the `pixelbart-theme-exporter` directory to the `/wp-content/plugins/` directory of your WordPress installation.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

1. Go to the 'Theme Exporter' tool under the 'Tools' menu in the WordPress admin area.
2. Select the 'Themes' or 'Plugins' tab, depending on which type of file you want to export.
3. Choose the theme or plugin you want to export from the dropdown menu.
4. Enter a new version number (optional) for the file in the input field.
5. Click the 'Export' button to export the file as a ZIP file with the updated version number.

## Security

This plugin includes security measures such as input validation, data sanitization, and nonce verification to prevent unauthorized access or malicious code injection.

## License

This plugin is released under the MIT License. See the `LICENSE` file for details.

## Support and Contributions

If you encounter any issues or have any suggestions for improvement, please submit an issue or pull request on the GitHub repository. Contributions are welcome and appreciated.

## Credits

- Plugin author: Pixelbart
- Plugin author URL: https://pixelbart.de/
- Plugin version: 2.4.1
- Tested up to: WordPress 6.1.1
- Requires at least: WordPress 4.0

## Changelog

- 2.4.1 Set default version on WordPress Themes.
- 2.0.0 The export of both themes and plugins is now possible.
- 1.0.0 Initial release.

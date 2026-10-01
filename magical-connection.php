<?php
/**
 * Plugin Name: Magical connection
 * Description: Magical Connection the Most Powerful Toll for Managing FTP servers in WordPress
 * Version: 1.0.0
 * Author: Code Art
 * Author URI: 
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Text Domain: magical-connection
 * Domain Path: /languages/
 */


use MagicalConnection\Core\Activation;
use MagicalConnection\Core\Application;

defined('ABSPATH') || exit;

define('MAGICAL_CONNECTION_PATH', plugin_dir_path(__FILE__));
define('MAGICAL_CONNECTION_URL', plugin_dir_url(__FILE__));
define('MAGICAL_CONNECTION_VERSION', "1.0.0");
define('MAGICAL_CONNECTION_PATH_TEMPLATE', plugin_dir_path(__FILE__) . "/includes/template");
define('MAGICAL_CONNECTION_TEXT_DOMAIN', 'magical-connection');
define('MAGICAL_LANG_PATH' , dirname(plugin_basename(__FILE__)) . '/languages');

if (file_exists(plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
}

require_once MAGICAL_CONNECTION_PATH . 'includes/Functions/app.php';

register_activation_hook(
    __FILE__,
    fn() => (new Activation())->activate()
);

Application::instance()->boot();

load_plugin_textdomain(
    MAGICAL_CONNECTION_TEXT_DOMAIN ,
    false,
    MAGICAL_LANG_PATH
);
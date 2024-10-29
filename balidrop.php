<?php
/*
 Plugin Name: Balidrop
 Plugin URI: https://www.balidrop.com/
 Description: Sell Abundant Chinese Productswithout Inventory Pressure
 Version: 1.0.1
 Author: ysun-tech
 License: GPLv2 or later
 Text Domain: Balidrop
*/


defined('ABSPATH') or die("Direct access to this script is denied");

if (!defined('BALIDROP_PATH')) define('BALIDROP_PATH', plugin_dir_path(__FILE__));
function balidrop_activated_plugins()
{

    $plugins_local = (array)get_option('active_plugins', []);
    $plugins_global = (array)get_site_option('active_sitewide_plugins', []);

    if (in_array('woocommerce/woocommerce.php', $plugins_local) ||
        (is_multisite() && array_key_exists('woocommerce/woocommerce.php', $plugins_global)))
        return 'woocommerce';

    return false;
}

function balidrop_check_server()
{
    if (!BALIDROP_PLUGIN)
        return __('None of the required plugins has been found: please install and activate WooCommerce plugin.', 'BALIDROP');

    return false;
}

function balidrop_admin_notice_error()
{

    if (BALIDROP_ERROR) {
        printf('<div class="notice notice-error"><p>%s</p></div>', BALIDROP_ERROR);
    }
}

if (!defined('BALIDROP_VERSION')) define('BALIDROP_VERSION', '1.0.0');
if (!defined('BALIDROP_URL')) define('BALIDROP_URL', str_replace(['https:', 'http:'], '', plugins_url('balidrop')));
if (!defined('BALIDROP_PLUGIN')) define('BALIDROP_PLUGIN', balidrop_activated_plugins());
if (!defined('BALIDROP_ERROR')) define('BALIDROP_ERROR', balidrop_check_server());

//加载php
if (!BALIDROP_ERROR) {

    require(BALIDROP_PATH . 'includes/api/balidrop_api.php');
    require(BALIDROP_PATH . 'includes/api/upImages_api.php');
    require(BALIDROP_PATH . 'includes/core/core.php');

    if (class_exists('Balidrop_Plugin')) {
        $balidropplugin = new Balidrop_Plugin();
    }

    // activation
    register_activation_hook(BALIDROP_PATH, array($balidropplugin, 'activation'));

    //deactivation
    register_deactivation_hook(BALIDROP_PATH, array($balidropplugin, 'deactivation'));

    // uninstall
    register_uninstall_hook(BALIDROP_PATH, array($balidropplugin, 'uninstall'));

}
add_action('admin_notices', 'balidrop_admin_notice_error');









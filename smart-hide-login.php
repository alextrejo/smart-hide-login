<?php
/**
 * Plugin Name: Smart Hide Login
 * Plugin URI: https://smartcode.net/hide-login/
 * Description: Hide WP login page with customizable redirect and welcome page
 * Version: 1.1.1
 * Author: Alexander Trejo @ Smartcode.net
 * Author URI: https://smartcode.net
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smart-hide-login
 * Domain Path: /languages
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define('SMRT_HIDE_LOGIN_VERSION', '1.1.0');
define('SMRT_HIDE_LOGIN_URL',plugin_dir_url( __FILE__ ));

// Load the appropriate class based on multisite status.
if (is_multisite()) {
    require_once dirname( __FILE__ ) . '/classes/hideLoginMultisite.class.php';
} else {
    require_once dirname( __FILE__ ) . '/classes/hideLogin.class.php';
}

//Activation hook
register_activation_hook( __FILE__, ['SMRT\SMRT_Hide_Login\HideLogin', 'activate'] );
// Deactivation hook.
register_deactivation_hook( __FILE__, ['SMRT\SMRT_Hide_Login\HideLogin', 'deactivate'] );

// Initialize the plugin.
SMRT\SMRT_Hide_Login\HideLogin::get_instance();
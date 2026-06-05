<?php
/**
 * Plugin Name: WP Role Menu Manager
 * Description: An enterprise-grade WordPress plugin to professionally manage Admin Menu visibility, core Database Capabilities, and Advanced UI restrictions. Features granular role-based controls, powerful per-user exceptions via an AJAX-powered search, strict content ownership rules (Posts/Media), WooCommerce integration, and a premium "Modern Organic" glassmorphism interface with real-time saving.
 * Version: 1.0.0
 * Author: Ravi Raj
 * Text Domain: wrmm
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WRMM_VERSION', '1.4.0' );
define( 'WRMM_DIR', plugin_dir_path( __FILE__ ) );
define( 'WRMM_URL', plugin_dir_url( __FILE__ ) );

require_once WRMM_DIR . 'includes/class-wrmm-core.php';
require_once WRMM_DIR . 'includes/class-wrmm-admin.php';
require_once WRMM_DIR . 'includes/class-wrmm-capabilities.php';
require_once WRMM_DIR . 'includes/class-wrmm-advanced.php';
require_once WRMM_DIR . 'includes/class-wrmm-users.php';
require_once WRMM_DIR . 'includes/class-wrmm-guide.php'; // NEW FILE

function wrmm_init() {
    new WRMM_Core();
    new WRMM_Advanced(); 
    
    if ( is_admin() ) {
        new WRMM_Admin();
        new WRMM_Capabilities();
        new WRMM_Users();
        new WRMM_Guide(); // Initialize Guide UI
    }
}
add_action( 'plugins_loaded', 'wrmm_init' );

register_activation_hook( __FILE__, 'wrmm_activate' );
function wrmm_activate() {
    if ( ! get_option( 'wrmm_hidden_menus' ) ) add_option( 'wrmm_hidden_menus', array() );
    if ( ! get_option( 'wrmm_advanced_settings' ) ) add_option( 'wrmm_advanced_settings', array() );
    if ( ! get_option( 'wrmm_user_exceptions' ) ) add_option( 'wrmm_user_exceptions', array() );
}
<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRMM_Core {

    private $hidden_settings;

    public function __construct() {
        $this->hidden_settings = get_option( 'wrmm_hidden_menus', array() );
        
        add_action( 'admin_menu', array( $this, 'hide_menus' ), 9999 );
        add_action( 'admin_init', array( $this, 'block_direct_access' ), 9999 );
    }

    public function hide_menus() {
        if ( current_user_can( 'manage_options' ) && ! apply_filters('wrmm_restrict_admins', false) ) return;

        $user = wp_get_current_user();
        if( ! $user ) return;
        
        $roles = (array) $user->roles;
        $hidden_menus = array();

        // 1. Gather default hidden menus for the user's roles
        foreach ( $roles as $role ) {
            if ( isset( $this->hidden_settings[$role] ) ) {
                $hidden_menus = array_merge($hidden_menus, $this->hidden_settings[$role]);
            }
        }

        // 2. Apply User-Specific Overrides (Exceptions)
        $user_exceptions = get_option('wrmm_user_exceptions', array());
        $user_id = $user->ID;

        if ( isset($user_exceptions[$user_id]) ) {
            $force_show = isset($user_exceptions[$user_id]['show']) ? (array)$user_exceptions[$user_id]['show'] : array();
            $force_hide = isset($user_exceptions[$user_id]['hide']) ? (array)$user_exceptions[$user_id]['hide'] : array();

            // Remove forced "show" items from the hidden list
            $hidden_menus = array_diff($hidden_menus, $force_show);
            // Add forced "hide" items to the hidden list
            $hidden_menus = array_merge($hidden_menus, $force_hide);
        }

        $hidden_menus = array_unique($hidden_menus);

        // 3. Execute removal
        foreach ( $hidden_menus as $menu_slug ) {
            if ( strpos( $menu_slug, '|' ) !== false ) {
                list( $parent, $submenu ) = explode( '|', $menu_slug );
                remove_submenu_page( $parent, $submenu );
            } else {
                remove_menu_page( $menu_slug );
            }
        }
    }

    public function block_direct_access() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
        if ( current_user_can( 'manage_options' ) && ! apply_filters('wrmm_restrict_admins', false) ) return;

        global $pagenow;
        $user = wp_get_current_user();
        if( ! $user ) return;
        
        $roles = (array) $user->roles;
        $hidden_menus = array();
        $current_page = isset( $_GET['page'] ) ? $_GET['page'] : $pagenow;

        foreach ( $roles as $role ) {
            if ( isset( $this->hidden_settings[$role] ) ) {
                $hidden_menus = array_merge($hidden_menus, $this->hidden_settings[$role]);
            }
        }

        $user_exceptions = get_option('wrmm_user_exceptions', array());
        $user_id = $user->ID;

        if ( isset($user_exceptions[$user_id]) ) {
            $force_show = isset($user_exceptions[$user_id]['show']) ? (array)$user_exceptions[$user_id]['show'] : array();
            $force_hide = isset($user_exceptions[$user_id]['hide']) ? (array)$user_exceptions[$user_id]['hide'] : array();
            $hidden_menus = array_diff($hidden_menus, $force_show);
            $hidden_menus = array_merge($hidden_menus, $force_hide);
        }

        $hidden_menus = array_unique($hidden_menus);

        foreach ( $hidden_menus as $hidden_slug ) {
            $check_slug = strpos( $hidden_slug, '|' ) !== false ? explode( '|', $hidden_slug )[1] : $hidden_slug;
            if ( $current_page === $check_slug ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'wrmm' ) );
            }
        }
    }
}
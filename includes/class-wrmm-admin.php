<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRMM_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_wrmm_save_settings', array( $this, 'ajax_save_settings' ) );
    }

    public function add_settings_page() {
        add_menu_page( 'Role Menu Manager', 'Menu Manager', 'manage_options', 'wrmm-settings', array( $this, 'render_page' ), 'dashicons-visibility', 80 );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos($hook, 'wrmm') === false ) return;

        wp_enqueue_style( 'wrmm-admin-css', WRMM_URL . 'assets/css/wrmm-admin.css', array(), WRMM_VERSION );
        wp_enqueue_script( 'wrmm-admin-js', WRMM_URL . 'assets/js/wrmm-admin.js', array( 'jquery' ), WRMM_VERSION, true );
        wp_localize_script( 'wrmm-admin-js', 'wrmm_obj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wrmm_save_nonce' )
        ));
    }

    public function render_page() {
        global $menu, $submenu;
        $roles = wp_roles()->get_names();
        $saved_settings = get_option( 'wrmm_hidden_menus', array() );
        ?>
        <div class="wrmm-organic-bg"></div>
        <div class="wrap wrmm-wrap">
            
            <div class="wrmm-glass-panel wrmm-header-panel">
                <div class="wrmm-header-text">
                    <h1><?php _e( 'Menu Visibility', 'wrmm' ); ?></h1>
                    <p>Toggle visibility for user roles. Menus the role lacks database capability for are automatically hidden to keep the UI clean.</p>
                </div>
                <div class="wrmm-role-selector-wrap">
                    <select id="wrmm-role-select" class="wrmm-role-selector">
                        <?php foreach ( $roles as $role_key => $role_name ) : ?>
                            <option value="<?php echo esc_attr( $role_key ); ?>"><?php echo esc_html( $role_name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="wrmm-content-area">
                <?php foreach ( $roles as $role_key => $role_name ) : 
                    $role_obj = get_role( $role_key );
                ?>
                    <div class="wrmm-glass-panel wrmm-role-wrapper" id="wrmm-wrapper-<?php echo esc_attr( $role_key ); ?>">
                        <form class="wrmm-form" data-role="<?php echo esc_attr( $role_key ); ?>" data-action="wrmm_save_settings">
                            <ul class="wrmm-list wrmm-menu-list">
                                <?php 
                                $visible_count = 0;
                                foreach ( $menu as $m ) {
                                    if ( empty( $m[0] ) ) continue; 
                                    
                                    $menu_slug = $m[2];
                                    $menu_cap  = $m[1];
                                    $menu_name = strip_tags( $m[0] ) ?: $menu_slug;
                                    
                                    $has_cap = $role_obj ? $role_obj->has_cap( $menu_cap ) : false;
                                    
                                    // Skip irrelevant options! Keeps Customer roles clean.
                                    if ( ! $has_cap ) continue; 
                                    $visible_count++;
                                    
                                    $is_hidden = isset( $saved_settings[$role_key] ) && in_array( $menu_slug, $saved_settings[$role_key] );
                                    $is_checked  = ! $is_hidden ? 'checked' : '';
                                    
                                    // Filter submenus for relevance
                                    $valid_submenus = array();
                                    if ( isset( $submenu[$menu_slug] ) ) {
                                        foreach ( $submenu[$menu_slug] as $sub ) {
                                            if ( $role_obj && $role_obj->has_cap( $sub[1] ) ) {
                                                $valid_submenus[] = $sub;
                                            }
                                        }
                                    }
                                    ?>
                                    <li class="wrmm-list-item">
                                        <div class="wrmm-list-item-header">
                                            <label class="wrmm-checkbox-label">
                                                <input type="checkbox" class="wrmm-cb" value="<?php echo esc_attr( $menu_slug ); ?>" <?php echo $is_checked; ?>>
                                                <span class="wrmm-custom-box"></span>
                                                <span class="wrmm-item-title"><?php echo esc_html( $menu_name ); ?></span> 
                                            </label>
                                            
                                            <?php if ( !empty($valid_submenus) ) : ?>
                                                <div class="wrmm-select-all-wrap">
                                                    <label class="wrmm-toggle-all">
                                                        <input type="checkbox" class="wrmm-menu-select-all"> Select All Submenus
                                                    </label>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ( !empty($valid_submenus) ) : ?>
                                            <ul class="wrmm-sublist">
                                                <?php foreach ( $valid_submenus as $sub ) : 
                                                    $sub_slug = $menu_slug . '|' . $sub[2];
                                                    $sub_name = strip_tags( $sub[0] ) ?: $sub_slug;
                                                    
                                                    $is_sub_hidden = isset( $saved_settings[$role_key] ) && in_array( $sub_slug, $saved_settings[$role_key] );
                                                    $sub_checked  = ! $is_sub_hidden ? 'checked' : '';
                                                ?>
                                                    <li class="wrmm-sublist-item">
                                                        <label class="wrmm-checkbox-label">
                                                            <input type="checkbox" class="wrmm-cb" value="<?php echo esc_attr( $sub_slug ); ?>" <?php echo $sub_checked; ?>>
                                                            <span class="wrmm-custom-box wrmm-custom-box-sm"></span>
                                                            <span class="wrmm-item-title"><?php echo esc_html( $sub_name ); ?></span> 
                                                        </label>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php } 
                                
                                if ($visible_count === 0) {
                                    echo '<li class="wrmm-list-item" style="text-align:center; padding: 30px; color: var(--wrmm-text-light);">This role natively has no backend menu capabilities.</li>';
                                }
                                ?>
                            </ul>
                            <div class="wrmm-form-footer">
                                <button type="submit" class="wrmm-submit-btn">
                                    <span><?php _e( 'Save Rules', 'wrmm' ); ?></span>
                                    <svg class="wrmm-spinner" viewBox="0 0 50 50"><circle cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="wrmm-toast" class="wrmm-toast"></div>
        <?php
    }

    public function ajax_save_settings() {
        check_ajax_referer( 'wrmm_save_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $role = sanitize_text_field( $_POST['role'] );
        $hidden_menus = isset( $_POST['data'] ) ? array_map( 'sanitize_text_field', $_POST['data'] ) : array();

        $settings = get_option( 'wrmm_hidden_menus', array() );
        $settings[$role] = $hidden_menus;
        update_option( 'wrmm_hidden_menus', $settings );

        wp_send_json_success( __( 'Menu rules saved successfully.', 'wrmm' ) );
    }
}
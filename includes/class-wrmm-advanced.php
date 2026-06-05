<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRMM_Advanced {

    // The UI automatically builds checkboxes based on this array
    private $tweaks = array(
        'hide_notices'    => 'Hide Admin Notices & Update Nags',
        'hide_profile'    => 'Hide Edit Profile (Menu & Top Bar)',
        'hide_wp_version' => 'Hide WordPress Version Footer',
        'hide_delete'     => 'Hide Delete/Trash Action Links in Lists',
        'restrict_posts'  => 'Show Only Own Posts/Pages (Restrict Content)',
        'restrict_media'  => 'Show Only Own Media Uploads (Restrict Library)'
    );

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_submenu' ), 30 );
        add_action( 'wp_ajax_wrmm_save_advanced', array( $this, 'ajax_save' ) );
        
        // Hooks to enforce UI rules
        add_action( 'admin_init', array( $this, 'apply_tweaks' ), 999 );
        add_filter( 'login_redirect', array( $this, 'apply_login_redirect' ), 99, 3 );

        // Hooks to enforce Database/Content restrictions
        add_action( 'pre_get_posts', array( $this, 'enforce_post_restriction' ) );
        add_filter( 'ajax_query_attachments_args', array( $this, 'enforce_media_grid_restriction' ) );
    }

    public function add_submenu() {
        add_submenu_page( 'wrmm-settings', 'Advanced UI Tweaks', 'Advanced Settings', 'manage_options', 'wrmm-advanced', array( $this, 'render_page' ) );
    }

    /* --------------------------------------------------------
     * CONTENT RESTRICTION LOGIC
     * -------------------------------------------------------- */
     
    // Helper function to check if a specific tweak is active for the current user's roles
    private function has_tweak_active( $tweak_key, $user ) {
        if ( empty($user->roles) ) return false;
        $settings = get_option('wrmm_advanced_settings', array());
        foreach ( $user->roles as $role ) {
            if ( isset($settings[$role]['tweaks']) && in_array($tweak_key, $settings[$role]['tweaks']) ) {
                return true;
            }
        }
        return false;
    }

    // Restricts Posts, Pages, and Media (List View) to the author
    public function enforce_post_restriction( $wp_query ) {
        if ( ! is_admin() || ! $wp_query->is_main_query() ) return;
        
        global $pagenow, $current_user;
        if ( ! in_array( $pagenow, array( 'edit.php', 'upload.php' ) ) ) return;

        // Never restrict super admins by default
        if ( current_user_can('manage_options') && ! apply_filters('wrmm_restrict_admins', false) ) return;

        // Restrict Posts & Pages
        if ( 'edit.php' === $pagenow && $this->has_tweak_active( 'restrict_posts', $current_user ) ) {
            $wp_query->set( 'author', $current_user->ID );
        }

        // Restrict Media Library (List View)
        if ( 'upload.php' === $pagenow && $this->has_tweak_active( 'restrict_media', $current_user ) ) {
            $wp_query->set( 'author', $current_user->ID );
        }
    }

    // Restricts Media Library (Grid View & AJAX Modals) to the author
    public function enforce_media_grid_restriction( $query ) {
        if ( ! is_user_logged_in() ) return $query;
        
        global $current_user;
        if ( current_user_can('manage_options') && ! apply_filters('wrmm_restrict_admins', false) ) return $query;

        if ( $this->has_tweak_active( 'restrict_media', $current_user ) ) {
            $query['author'] = $current_user->ID;
        }
        return $query;
    }

    /* --------------------------------------------------------
     * UI RESTRICTION LOGIC
     * -------------------------------------------------------- */

    public function apply_tweaks() {
        if ( current_user_can('manage_options') && ! apply_filters('wrmm_restrict_admins', false) ) return;

        $user = wp_get_current_user();
        if( ! $user ) return;
        
        $settings = get_option('wrmm_advanced_settings', array());
        
        foreach ( (array) $user->roles as $role ) {
            if ( isset($settings[$role]['tweaks']) ) {
                $rules = $settings[$role]['tweaks'];
                
                if ( in_array('hide_notices', $rules) ) {
                    remove_all_actions('admin_notices');
                    remove_action('admin_notices', 'update_nag', 3);
                    add_action('admin_head', function(){ echo '<style>.update-nag, .notice, .e-notice { display: none !important; }</style>'; });
                }
                if ( in_array('hide_profile', $rules) ) {
                    remove_submenu_page('users.php', 'profile.php');
                    remove_menu_page('profile.php');
                    add_action('wp_before_admin_bar_render', function() {
                        global $wp_admin_bar;
                        $wp_admin_bar->remove_menu('my-account');
                    });
                }
                if ( in_array('hide_wp_version', $rules) ) {
                    add_filter('update_footer', '__return_empty_string', 11);
                    add_filter('admin_footer_text', '__return_empty_string', 11);
                }
                if ( in_array('hide_delete', $rules) ) {
                    add_filter('post_row_actions', array($this, 'remove_delete_action'), 10, 1);
                    add_filter('page_row_actions', array($this, 'remove_delete_action'), 10, 1);
                }
            }
        }
    }

    public function remove_delete_action( $actions ) {
        if(isset($actions['trash'])) unset($actions['trash']);
        if(isset($actions['delete'])) unset($actions['delete']);
        return $actions;
    }

    public function apply_login_redirect( $redirect_to, $request, $user ) {
        if ( isset( $user->roles ) && is_array( $user->roles ) ) {
            $settings = get_option('wrmm_advanced_settings', array());
            foreach ( $user->roles as $role ) {
                if ( !empty($settings[$role]['redirect']) ) {
                    return esc_url_raw($settings[$role]['redirect']);
                }
            }
        }
        return $redirect_to;
    }

    /* --------------------------------------------------------
     * FRONT-END RENDER LOGIC
     * -------------------------------------------------------- */

    public function render_page() {
        $roles = wp_roles()->get_names();
        $saved_settings = get_option( 'wrmm_advanced_settings', array() );
        ?>
        <div class="wrmm-organic-bg"></div>
        <div class="wrap wrmm-wrap">
            <div class="wrmm-glass-panel wrmm-header-panel">
                <div class="wrmm-header-text">
                    <h1><?php _e( 'Advanced UI & Content Rules', 'wrmm' ); ?></h1>
                    <p>Clean up the interface, set login routing, and enforce strict content ownership restrictions.</p>
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
                    $role_tweaks = isset($saved_settings[$role_key]['tweaks']) ? $saved_settings[$role_key]['tweaks'] : array();
                    $role_redirect = isset($saved_settings[$role_key]['redirect']) ? $saved_settings[$role_key]['redirect'] : '';
                ?>
                    <div class="wrmm-glass-panel wrmm-role-wrapper" id="wrmm-wrapper-<?php echo esc_attr( $role_key ); ?>">
                        <form class="wrmm-form" data-role="<?php echo esc_attr( $role_key ); ?>" data-action="wrmm_save_advanced">
                            
                            <div class="wrmm-cap-group">
                                <h3 class="wrmm-group-title">Global Interface & Content Rules</h3>
                                <div class="wrmm-cap-grid">
                                    <?php foreach ( $this->tweaks as $tweak_key => $tweak_label ) : ?>
                                        <div class="wrmm-grid-item">
                                            <label class="wrmm-checkbox-label wrmm-cap-label">
                                                <input type="checkbox" class="wrmm-cb" value="<?php echo esc_attr( $tweak_key ); ?>" <?php checked( in_array($tweak_key, $role_tweaks), true ); ?>>
                                                <span class="wrmm-custom-box"></span>
                                                <span class="wrmm-item-title"><?php echo esc_html( $tweak_label ); ?></span> 
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="wrmm-cap-group">
                                <h3 class="wrmm-group-title">Login Routing</h3>
                                <div class="wrmm-input-group">
                                    <label>Custom Login Redirect URL</label>
                                    <input type="text" name="login_redirect" class="wrmm-text-input" placeholder="e.g. <?php echo admin_url('edit.php'); ?>" value="<?php echo esc_attr($role_redirect); ?>">
                                    <p class="wrmm-desc">Leave blank to use default WordPress behavior. Absolute URLs supported.</p>
                                </div>
                            </div>
                            
                            <div class="wrmm-form-footer">
                                <button type="submit" class="wrmm-submit-btn">
                                    <span><?php _e( 'Save Advanced Settings', 'wrmm' ); ?></span>
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

    public function ajax_save() {
        check_ajax_referer( 'wrmm_save_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $role = sanitize_text_field( $_POST['role'] );
        $tweaks = isset( $_POST['data'] ) ? array_map( 'sanitize_text_field', $_POST['data'] ) : array();
        $redirect = isset( $_POST['redirect'] ) ? esc_url_raw( $_POST['redirect'] ) : '';

        $settings = get_option( 'wrmm_advanced_settings', array() );
        $settings[$role] = array(
            'tweaks'   => $tweaks,
            'redirect' => $redirect
        );
        update_option( 'wrmm_advanced_settings', $settings );

        wp_send_json_success( __( 'Advanced settings saved successfully.', 'wrmm' ) );
    }
}
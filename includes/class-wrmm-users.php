<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRMM_Users {

    private $managed_caps = array(
        'Pages' => array( 'edit_pages' => 'Edit Pages (Drafts)', 'edit_published_pages' => 'Edit Published Pages', 'edit_others_pages' => 'Edit Others Pages', 'publish_pages' => 'Publish Pages', 'delete_pages' => 'Delete Pages (Drafts)', 'delete_published_pages' => 'Delete Published Pages', 'delete_others_pages' => 'Delete Others Pages' ),
        'Posts' => array( 'edit_posts' => 'Edit Posts (Drafts)', 'edit_published_posts' => 'Edit Published Posts', 'edit_others_posts' => 'Edit Others Posts', 'publish_posts' => 'Publish Posts', 'delete_posts' => 'Delete Posts (Drafts)', 'delete_published_posts' => 'Delete Published Posts', 'delete_others_posts' => 'Delete Others Posts' ),
        'User Management' => array( 'list_users' => 'List Users', 'create_users' => 'Create Users', 'edit_users' => 'Edit Users', 'delete_users' => 'Delete Users', 'promote_users' => 'Promote Users', 'remove_users' => 'Remove Users' ),
        'Plugins Management' => array( 'activate_plugins' => 'Activate Plugins', 'install_plugins' => 'Install Plugins', 'update_plugins' => 'Update Plugins', 'delete_plugins' => 'Delete Plugins', 'edit_plugins' => 'Edit Plugin Files' ),
        'Themes & Appearance' => array( 'switch_themes' => 'Switch Themes', 'install_themes' => 'Install Themes', 'update_themes' => 'Update Themes', 'delete_themes' => 'Delete Themes', 'edit_theme_options' => 'Edit Theme Options', 'edit_themes' => 'Edit Theme Files' ),
        'Core System & Tools' => array( 'manage_options' => 'Manage Settings (General)', 'update_core' => 'Update WordPress Core', 'export' => 'Export Data', 'import' => 'Import Data', 'unfiltered_html' => 'Allow Unfiltered HTML' ),
        'Media & Taxonomy' => array( 'upload_files' => 'Upload Media Files', 'manage_categories' => 'Manage Categories & Tags', 'manage_links' => 'Manage Links', 'moderate_comments' => 'Moderate Comments' ),
        'WooCommerce Core' => array( 'manage_woocommerce' => 'Manage WooCommerce', 'view_woocommerce_reports' => 'View Reports', 'edit_shop_orders' => 'Edit Shop Orders', 'edit_others_shop_orders' => 'Edit Others Orders', 'delete_shop_orders' => 'Delete Shop Orders' ),
        'WooCommerce Products' => array( 'edit_products' => 'Edit Products', 'edit_others_products' => 'Edit Others Products', 'publish_products' => 'Publish Products', 'read_private_products' => 'Read Private Products', 'delete_products' => 'Delete Products', 'manage_product_terms' => 'Manage Product Categories/Tags' )
    );

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_submenu' ), 25 );
        add_action( 'wp_ajax_wrmm_fetch_users', array( $this, 'ajax_fetch_users' ) );
        add_action( 'wp_ajax_wrmm_load_user_data', array( $this, 'ajax_load_user' ) );
        add_action( 'wp_ajax_wrmm_save_user_exceptions', array( $this, 'ajax_save' ) );
    }

    public function add_submenu() {
        add_submenu_page( 'wrmm-settings', 'User Exceptions', 'User Exceptions', 'manage_options', 'wrmm-users', array( $this, 'render_page' ) );
    }

    public function render_page() {
        global $menu, $submenu;
        ?>
        <div class="wrmm-organic-bg"></div>
        <div class="wrap wrmm-wrap">
            
            <div class="wrmm-glass-panel" style="padding: 30px 40px; margin-bottom: 30px;">
                <div class="wrmm-header-text" style="margin-bottom: 25px;">
                    <h1><?php _e( 'User-Specific Exceptions', 'wrmm' ); ?></h1>
                    <p>Override menus and grant specific database capabilities on a per-user basis.</p>
                </div>
                
                <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                    <select id="wrmm-user-role-select" class="wrmm-role-selector" style="flex: 1; min-width: 220px;">
                        <option value="">1. Select Role</option>
                        <?php foreach ( wp_roles()->get_names() as $role_key => $role_name ) : ?>
                            <option value="<?php echo esc_attr( $role_key ); ?>"><?php echo esc_html( $role_name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div id="wrmm-user-select-container" style="display:none; flex: 3; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; min-width: 320px;">
                         <input type="text" id="wrmm-user-search-input" class="wrmm-text-input" placeholder="Search name/email..." style="flex: 1; min-width: 150px; margin: 0;">
                         
                         <select id="wrmm-user-select" class="wrmm-role-selector" style="flex: 2; min-width: 220px;">
                             <option value="">2. Select User</option>
                         </select>
                         
                         <button type="button" id="wrmm-load-user-btn" class="wrmm-submit-btn">Load User</button>
                    </div>
                </div>
            </div>

            <div id="wrmm-user-form-container" style="display:none;">
                <div class="wrmm-glass-panel wrmm-role-wrapper active">
                    <div class="wrmm-group-header" style="padding: 25px 40px; background: rgba(248, 250, 252, 0.8); border-bottom: 1px solid rgba(0,0,0,0.04); margin: 0;">
                        <h3 class="wrmm-group-title" style="font-size: 15px; color: var(--wrmm-text-main); font-weight: 600;">
                            Managing Exceptions For: <span id="wrmm-active-user-name" style="color: var(--wrmm-primary); margin-left: 8px;"></span>
                        </h3>
                    </div>

                    <form class="wrmm-form" data-action="wrmm_save_user_exceptions">
                        <input type="hidden" id="wrmm-active-user-id" name="user_id" value="">
                        
                        <div class="wrmm-cap-group">
                            <h3 class="wrmm-group-title" style="margin-bottom: 5px;">1. User-Specific Menu Visibility</h3>
                            <p style="font-size: 13px; color: var(--wrmm-text-light); margin: 0 0 20px 0;">Menus the user lacks capability for are automatically hidden to keep UI clean.</p>
                            <ul class="wrmm-list wrmm-menu-list">
                                <?php 
                                foreach ( $menu as $m ) {
                                    if ( empty( $m[0] ) ) continue; 
                                    $menu_slug = $m[2];
                                    $menu_cap  = $m[1];
                                    $menu_name = strip_tags( $m[0] ) ?: $menu_slug;
                                    ?>
                                    <li class="wrmm-list-item" data-req-cap="<?php echo esc_attr($menu_cap); ?>">
                                        <div class="wrmm-list-item-header" style="justify-content: space-between;">
                                            <span class="wrmm-item-title" style="font-weight: 600;"><?php echo esc_html( $menu_name ); ?></span>
                                            <select class="wrmm-exception-select wrmm-text-input" style="width: 160px; padding: 6px 12px; font-size: 13px;" data-slug="<?php echo esc_attr( $menu_slug ); ?>">
                                                <option value="inherit">Inherit Role Default</option>
                                                <option value="show">Force Show</option>
                                                <option value="hide">Force Hide</option>
                                            </select>
                                        </div>
                                        
                                        <?php if ( isset( $submenu[$menu_slug] ) ) : ?>
                                            <ul class="wrmm-sublist">
                                                <?php foreach ( $submenu[$menu_slug] as $sub ) : 
                                                    $sub_slug = $menu_slug . '|' . $sub[2];
                                                    $sub_cap  = $sub[1];
                                                    $sub_name = strip_tags( $sub[0] ) ?: $sub_slug;
                                                ?>
                                                    <li class="wrmm-sublist-item" data-req-cap="<?php echo esc_attr($sub_cap); ?>" style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span class="wrmm-item-title"><?php echo esc_html( $sub_name ); ?></span>
                                                        <select class="wrmm-exception-select wrmm-text-input" style="width: 160px; padding: 4px 10px; font-size: 12px; min-height: 28px;" data-slug="<?php echo esc_attr( $sub_slug ); ?>">
                                                            <option value="inherit">Inherit Default</option>
                                                            <option value="show">Force Show</option>
                                                            <option value="hide">Force Hide</option>
                                                        </select>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>

                        <div class="wrmm-cap-group">
                            <h3 class="wrmm-group-title" style="margin-bottom: 5px;">2. User-Specific Database Capabilities</h3>
                            <p style="font-size: 13px; color: var(--wrmm-text-light); margin: 0 0 20px 0;">Directly grant or revoke explicit capabilities for this user overriding their role.</p>
                            <ul class="wrmm-list wrmm-cap-list">
                                <?php foreach ( $this->managed_caps as $group => $caps ) : ?>
                                    <li class="wrmm-list-item" style="background: rgba(0,0,0,0.02); padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                                        <strong style="text-transform: uppercase; font-size: 11px; color: var(--wrmm-text-light); letter-spacing: 1px;"><?php echo esc_html($group); ?></strong>
                                    </li>
                                    <?php foreach ( $caps as $cap_key => $cap_label ) : ?>
                                        <li class="wrmm-list-item">
                                            <div class="wrmm-list-item-header" style="justify-content: space-between;">
                                                <span class="wrmm-item-title"><?php echo esc_html( $cap_label ); ?> <code class="wrmm-code-badge" style="margin-left: 10px;"><?php echo esc_html($cap_key); ?></code></span>
                                                <select class="wrmm-user-cap-select wrmm-text-input" style="width: 160px; padding: 6px 12px; font-size: 13px;" data-cap="<?php echo esc_attr( $cap_key ); ?>">
                                                    <option value="inherit">Inherit Default</option>
                                                    <option value="grant">Grant Capability</option>
                                                    <option value="revoke">Revoke Capability</option>
                                                </select>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="wrmm-form-footer">
                            <button type="submit" class="wrmm-submit-btn">
                                <span><?php _e( 'Save User Exceptions', 'wrmm' ); ?></span>
                                <svg class="wrmm-spinner" viewBox="0 0 50 50"><circle cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="wrmm-toast" class="wrmm-toast"></div>
        <?php
    }

    public function ajax_fetch_users() {
        check_ajax_referer( 'wrmm_save_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        $args = array( 'number' => 5, 'fields' => array('ID', 'display_name', 'user_email') );
        if ( ! empty( $role ) ) $args['role'] = $role;
        
        if ( ! empty( $search ) ) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = array( 'user_login', 'user_nicename', 'user_email', 'display_name' );
            $args['number'] = 25; 
        }

        $user_query = new WP_User_Query( $args );
        $users = $user_query->get_results();

        $results = array();
        foreach ( $users as $user ) {
            $results[] = array( 'id' => $user->ID, 'name' => $user->display_name, 'email' => $user->user_email );
        }
        wp_send_json_success( $results );
    }

    public function ajax_load_user() {
        check_ajax_referer( 'wrmm_save_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $user_id = intval( $_POST['user_id'] );
        $user = get_userdata( $user_id );
        if ( ! $user ) wp_send_json_error( 'User not found.' );

        $exceptions = get_option( 'wrmm_user_exceptions', array() );
        $user_data = isset( $exceptions[$user_id] ) ? $exceptions[$user_id] : array( 'show' => array(), 'hide' => array() );

        wp_send_json_success( array(
            'user_name'  => $user->display_name . ' (' . $user->user_email . ')',
            'exceptions' => $user_data,
            'user_caps'  => $user->caps,
            'all_caps'   => $user->allcaps
        ));
    }

    public function ajax_save() {
        check_ajax_referer( 'wrmm_save_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $user_id = intval( $_POST['user_id'] );
        if ( ! $user_id ) wp_send_json_error( 'Invalid User ID.' );
        $user = get_userdata( $user_id );

        // 1. Process Menu Visibilities
        $show_menus = isset( $_POST['data']['show'] ) ? array_map( 'sanitize_text_field', $_POST['data']['show'] ) : array();
        $hide_menus = isset( $_POST['data']['hide'] ) ? array_map( 'sanitize_text_field', $_POST['data']['hide'] ) : array();

        $settings = get_option( 'wrmm_user_exceptions', array() );
        if ( empty($show_menus) && empty($hide_menus) ) {
            unset($settings[$user_id]);
        } else {
            $settings[$user_id] = array( 'show' => $show_menus, 'hide' => $hide_menus );
        }
        update_option( 'wrmm_user_exceptions', $settings );

        // 2. Process Explicit User Database Capabilities
        $submitted_caps = isset( $_POST['data']['caps'] ) ? array_map( 'sanitize_text_field', $_POST['data']['caps'] ) : array();
        $all_managed_caps = array();
        foreach ( $this->managed_caps as $group => $caps ) {
            $all_managed_caps = array_merge( $all_managed_caps, array_keys( $caps ) );
        }

        foreach ( $all_managed_caps as $cap ) {
            if ( isset($submitted_caps[$cap]) ) {
                if ($submitted_caps[$cap] === 'grant') {
                    $user->add_cap($cap, true);
                } elseif ($submitted_caps[$cap] === 'revoke') {
                    $user->add_cap($cap, false);
                }
            } else {
                $user->remove_cap($cap);
            }
        }

        wp_send_json_success( __( 'User menus and capabilities successfully overridden.', 'wrmm' ) );
    }
}
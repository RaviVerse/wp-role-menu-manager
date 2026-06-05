<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRMM_Capabilities {

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
        add_action( 'admin_menu', array( $this, 'add_submenu' ), 20 );
        add_action( 'wp_ajax_wrmm_save_capabilities', array( $this, 'ajax_save_capabilities' ) );
    }

    public function add_submenu() {
        add_submenu_page( 'wrmm-settings', 'Role Capabilities', 'Capabilities', 'manage_options', 'wrmm-capabilities', array( $this, 'render_page' ) );
    }

    public function render_page() {
        $roles = wp_roles()->roles;
        ?>
        <div class="wrmm-organic-bg"></div>
        <div class="wrap wrmm-wrap">
            
            <div class="wrmm-glass-panel wrmm-header-panel">
                <div class="wrmm-header-text">
                    <h1><?php _e( 'Database Capabilities', 'wrmm' ); ?></h1>
                    <p>Modify core capabilities, fully integrated with WooCommerce settings.</p>
                </div>
                <div class="wrmm-role-selector-wrap">
                    <select id="wrmm-role-select" class="wrmm-role-selector">
                        <?php foreach ( $roles as $role_key => $role_data ) : 
                            if ( 'administrator' === $role_key ) continue; 
                        ?>
                            <option value="<?php echo esc_attr( $role_key ); ?>"><?php echo esc_html( $role_data['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="wrmm-content-area">
                <?php foreach ( $roles as $role_key => $role_data ) : 
                    if ( 'administrator' === $role_key ) continue; 
                    $role_obj = get_role( $role_key );
                ?>
                    <div class="wrmm-glass-panel wrmm-role-wrapper" id="wrmm-wrapper-<?php echo esc_attr( $role_key ); ?>">
                        <form class="wrmm-form" data-role="<?php echo esc_attr( $role_key ); ?>" data-action="wrmm_save_capabilities">
                            
                            <?php foreach ( $this->managed_caps as $group => $caps ) : ?>
                                <div class="wrmm-cap-group">
                                    <div class="wrmm-group-header">
                                        <h3 class="wrmm-group-title"><?php echo esc_html( $group ); ?></h3>
                                        <label class="wrmm-toggle-all">
                                            <input type="checkbox" class="wrmm-cap-select-all"> Select All in Group
                                        </label>
                                    </div>
                                    <div class="wrmm-cap-grid">
                                        <?php foreach ( $caps as $cap_key => $cap_label ) : 
                                            $has_cap = $role_obj->has_cap( $cap_key );
                                        ?>
                                            <div class="wrmm-grid-item">
                                                <label class="wrmm-checkbox-label wrmm-cap-label">
                                                    <input type="checkbox" class="wrmm-cb" value="<?php echo esc_attr( $cap_key ); ?>" <?php checked( $has_cap, true ); ?>>
                                                    <span class="wrmm-custom-box"></span>
                                                    <span class="wrmm-item-title"><?php echo esc_html( $cap_label ); ?></span> 
                                                    <code class="wrmm-code-badge"><?php echo esc_html( $cap_key ); ?></code>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="wrmm-form-footer">
                                <button type="submit" class="wrmm-submit-btn wrmm-submit-btn-danger">
                                    <span><?php _e( 'Update Capabilities', 'wrmm' ); ?></span>
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

    public function ajax_save_capabilities() {
        check_ajax_referer( 'wrmm_save_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized' );

        $role_key = sanitize_text_field( $_POST['role'] );
        $submitted_caps = isset( $_POST['data'] ) ? array_map( 'sanitize_text_field', $_POST['data'] ) : array();
        $role_obj = get_role( $role_key );

        if ( ! $role_obj ) wp_send_json_error( 'Invalid Role' );

        $all_managed_caps = array();
        foreach ( $this->managed_caps as $group => $caps ) {
            $all_managed_caps = array_merge( $all_managed_caps, array_keys( $caps ) );
        }

        foreach ( $all_managed_caps as $cap ) {
            if ( in_array( $cap, $submitted_caps ) ) {
                $role_obj->add_cap( $cap );
            } else {
                $role_obj->remove_cap( $cap );
            }
        }

        wp_send_json_success( sprintf( __( 'Capabilities securely updated for %s.', 'wrmm' ), $role_key ) );
    }
}
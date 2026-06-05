<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WRMM_Guide {

    public function __construct() {
        // Priority 50 ensures it appears at the bottom of our plugin's submenu list
        add_action( 'admin_menu', array( $this, 'add_submenu' ), 50 );
    }

    public function add_submenu() {
        add_submenu_page( 
            'wrmm-settings', 
            'How to Use', 
            'Guide', 
            'manage_options', 
            'wrmm-guide', 
            array( $this, 'render_page' ) 
        );
    }

    public function render_page() {
        ?>
        <div class="wrmm-organic-bg"></div>
        <div class="wrap wrmm-wrap">
            
            <div class="wrmm-glass-panel wrmm-header-panel" style="margin-bottom: 30px;">
                <div class="wrmm-header-text">
                    <h1><?php _e( 'Plugin Guide & Documentation', 'wrmm' ); ?></h1>
                    <p>A quick reference on how to use the WP Role Menu Manager effectively and securely.</p>
                </div>
            </div>

            <div class="wrmm-content-area">
                <div class="wrmm-glass-panel wrmm-role-wrapper active" style="display: block;">
                    
                    <div class="wrmm-cap-group">
                        <h3 class="wrmm-group-title" style="margin-bottom: 15px; font-size: 16px; color: var(--wrmm-primary);">1. Menu Manager (Visibility)</h3>
                        <p style="line-height: 1.6; color: var(--wrmm-text-main); margin-bottom: 10px;">
                            <strong>What it does:</strong> Hides backend sidebar menus from specific user roles.<br>
                            <strong>How to use:</strong> Select a role from the dropdown. Uncheck any menu item you want to hide from that role. Click Save.
                        </p>
                        <p style="line-height: 1.6; color: var(--wrmm-text-light); font-size: 13px; font-style: italic;">
                            Note: If a role does not have the database permission to see a menu natively, it will automatically not appear in this list to keep the interface clean.
                        </p>
                    </div>

                    <div class="wrmm-cap-group">
                        <h3 class="wrmm-group-title" style="margin-bottom: 15px; font-size: 16px; color: var(--wrmm-primary);">2. Capabilities (Database Access)</h3>
                        <p style="line-height: 1.6; color: var(--wrmm-text-main); margin-bottom: 10px;">
                            <strong>What it does:</strong> Permanently grants or revokes native WordPress database permissions (e.g., editing published pages, managing WooCommerce, uploading files).<br>
                            <strong>How to use:</strong> Select a role. Check the box to grant a capability, uncheck to revoke it. Click Save.
                        </p>
                        <p style="line-height: 1.6; color: #ef4444; font-size: 13px; font-weight: 500;">
                            Warning: These changes are written directly to your WordPress database. Only grant capabilities to trusted roles.
                        </p>
                    </div>

                    <div class="wrmm-cap-group">
                        <h3 class="wrmm-group-title" style="margin-bottom: 15px; font-size: 16px; color: var(--wrmm-primary);">3. User Exceptions (Overrides)</h3>
                        <p style="line-height: 1.6; color: var(--wrmm-text-main);">
                            <strong>What it does:</strong> Allows you to break the rules for one specific person without changing their entire role.<br>
                            <strong>How to use:</strong> Select a role, then search for a specific user's name or email. You can "Force Show" or "Force Hide" menus, and explicitly Grant/Revoke capabilities just for them.
                        </p>
                    </div>

                    <div class="wrmm-cap-group">
                        <h3 class="wrmm-group-title" style="margin-bottom: 15px; font-size: 16px; color: var(--wrmm-primary);">4. Advanced Settings</h3>
                        <p style="line-height: 1.6; color: var(--wrmm-text-main); margin-bottom: 10px;">
                            <strong>What it does:</strong> Cleans up the WordPress interface and restricts content ownership.
                        </p>
                        <ul style="list-style-type: disc; margin-left: 20px; line-height: 1.8; color: var(--wrmm-text-main);">
                            <li><strong>Hide Notices/Update Nags:</strong> Removes annoying top-bar alerts and update warnings.</li>
                            <li><strong>Hide Delete/Trash:</strong> Removes the red "Trash" link from post/page lists.</li>
                            <li><strong>Restrict Content:</strong> Forces users to only see and edit their own Posts, Pages, or Media.</li>
                            <li><strong>Login Routing:</strong> Enter a custom URL to redirect users when they log in.</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
        <?php
    }
}
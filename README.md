# WP Role Menu Manager 🚀

An advanced, enterprise-grade WordPress plugin designed to give administrators absolute control over the WordPress backend. Manage menu visibility, modify core database capabilities, enforce strict content ownership, and create user-specific exceptions—all through a beautifully designed, AJAX-powered "Modern Organic" interface.

## ✨ Key Features

### 1. Smart Menu Visibility
*   **Role-Based Toggling:** Easily hide or show default WordPress menus, custom post types, and third-party plugin menus based on user roles.
*   **Capability Awareness:** Automatically detects and hides menus that a role natively lacks the database capability to access, keeping the interface incredibly clean for limited roles like Customers or Subscribers.
*   **Bulk Actions:** "Select All Submenus" toggles for rapid configuration.
*   **Strict URL Protection:** Prevents users from bypassing hidden menus via direct URL access.

### 2. Granular Database Capabilities
*   **Core Modification:** Permanently grant or revoke underlying database capabilities (e.g., `edit_published_pages`, `delete_others_posts`).
*   **Categorized Groupings:** Capabilities are logically grouped (Pages, Posts, User Management, Plugins, Themes, Media).
*   **WooCommerce Ready:** Natively includes full capability management for WooCommerce Core and WooCommerce Products.

### 3. User-Specific Exceptions (AJAX Powered)
*   **Multi-Level Drilldown:** Select a role, then use the AJAX-powered live search to instantly find specific users without crashing your server by loading thousands of accounts at once.
*   **Micro-Management:** Override role defaults on a per-user basis. Force-hide a menu for one specific Editor, or grant a unique capability to a single Subscriber.
*   **Live UI Refresh:** The UI intelligently adapts and reveals newly available menus the moment a new database capability is granted to a user.

### 4. Advanced UI & Content Restrictions
*   **Content Ownership:** Restrict the Posts, Pages, and Media Library (both list view and grid/upload modals) so users can only see and manage their own uploads/creations.
*   **Interface Cleanup:** One-click toggles to hide Admin Notices, the WordPress Version footer, the Edit Profile screen, and the red "Delete/Trash" row action links.
*   **Login Routing:** Set custom login redirect URLs based on user roles.

### 5. Premium "Modern Organic" Architecture
*   **Glassmorphism UI:** Features a stunning frosted-glass aesthetic, custom SVG checkboxes, symmetric grid layouts, and smooth CSS transitions.
*   **Zero Page Reloads:** 100% AJAX-powered saving with animated SVG loading spinners and modern floating toast notifications.
*   **Clean OOP Codebase:** Built using strict Object-Oriented PHP, WordPress Coding Standards, and modular classes.

---

## 📸 Screenshots

*(Replace these placeholder links with actual images of your plugin once uploaded to your repo)*

*   `![Menu Visibility Manager](docs/screenshot-menu-manager.png)`
*   `![Database Capabilities](docs/screenshot-capabilities.png)`
*   `![User Exceptions AJAX Search](docs/screenshot-user-exceptions.png)`
*   `![Advanced Settings](docs/screenshot-advanced-settings.png)`

---

## 🛠️ Installation

1.  Download the latest release `.zip` file from the [Releases](#) tab.
2.  Log in to your WordPress admin dashboard.
3.  Navigate to **Plugins > Add New > Upload Plugin**.
4.  Upload the `.zip` file and click **Install Now**.
5.  Click **Activate Plugin**.
6.  Locate the new **Menu Manager** tab in your left-hand admin sidebar to begin configuring rules.

---

## 📖 Usage Guide

*   **Menu Manager:** Select a role from the dropdown. Uncheck any menu or submenu you want to hide from that role. Click "Save Rules".
*   **Capabilities:** Navigate to *Menu Manager > Capabilities*. Select a role to grant them new native WordPress database permissions (like the ability to edit published posts or manage WooCommerce).
*   **User Exceptions:** Navigate to *Menu Manager > User Exceptions*. Select a role, search for a specific user, and override their menus or capabilities independently of their role group.
*   **Advanced Settings:** Navigate to *Menu Manager > Advanced Settings*. Apply global interface cleanups (like hiding nags/notices) and set up strict content ownership rules so authors only see their own media and posts.

---

## 💻 Technical Stack

*   **Backend:** Core PHP (OOP), WordPress Plugin API, WordPress AJAX Admin API, MySQL (via WP Options).
*   **Frontend:** HTML5, CSS3 (CSS Variables, Flexbox, CSS Grid, Backdrop-Filter), Vanilla JavaScript & jQuery.
*   **Compatibility:** Requires WordPress 5.0+ and PHP 7.4+. Fully compatible with WooCommerce 8.0+.

---

## 🤝 Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change. Ensure that your code follows WordPress Coding Standards.

## 📄 License

This project is licensed under the GPL-2.0+ License - see the [LICENSE.md](LICENSE.md) file for details.
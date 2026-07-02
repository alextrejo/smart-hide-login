# Smart Hide Login

Hide WP login page with customizable redirect and welcome page.

## Description

Smart Hide Login is a powerful lightweight WordPress security plugin that helps protect your website by hiding the default WordPress login page. By changing the default /wp-admin and /wp-login.php URLs to a custom slug, you can significantly reduce the risk of brute force attacks and unauthorized access attempts.

### Features

* Lightweight plugin that keeps your WordPress website fast and responsive
* Enhanced Security - Hide default WordPress login URLs (/wp-admin, /wp-login.php)
* Customizable login slug (default: /dashboard)
* Prevents unauthorized access attempts
* Reduces brute force attack surface
* Multi-Site Support - Works seamlessly with WordPress Multisite installations
* Network-wide settings management
* Individual site configuration options
* Fully translation-ready (English and Spanish included)
* Compatible with WordPress localization
* Professional Design - Custom welcome page for unauthorized visitors
* Responsive design that matches your theme
* Supports custom logos from your WordPress theme
* Easy Configuration - Simple settings page in WordPress admin
* Real-time slug changes with automatic rewrite rule updates
* Nonce protection for form submissions

### Installation

1. Upload the `hide-login` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Settings → Hidden (or Network Admin → Settings → Hidden for Multisite)
4. Set your custom login slug
5. Save changes

### Frequently Asked Questions

**Will this plugin break my existing login process?**

No, the plugin maintains WordPress's standard authentication process. Users simply access the login page through a different URL.

**Can I change the login slug after installation?**

Yes, you can change the login slug at any time through the settings page. The plugin automatically updates rewrite rules.

**Does this work with caching plugins?**

Yes, the plugin is compatible with most caching solutions. The rewrite rules ensure proper URL handling.

**Is this plugin translation-ready?**

Yes, the plugin includes translation files and is fully compatible with WordPress's internationalization system.

**What can I do if I forget my custom login URL?**

You would need access to your hosting service and delete the plugin folder: /wp-content/plugins/smart-hide-login. After plugin deletion, you can access your WordPress backend using default URL: /wp-admin

### Security Benefits

* Reduced Attack Surface - Hides WordPress from automated scanners
* Makes it harder for attackers to find your login page
* Reduces the effectiveness of targeted attacks
* Brute Force Protection - While not a complete solution, hiding the login page adds an extra layer of defense
* Works well with other security plugins and measures

### Compatibility

* WordPress Versions - Compatible with WordPress 4.0+
* Works with the latest WordPress releases
* Hosting Environments - Compatible with all standard WordPress hosting
* Works with Apache, Nginx, and other web servers
* Supports various PHP versions (5.6+)
* Other Plugins - Compatible with most WordPress plugins
* Works alongside security plugins
* No conflicts with caching plugins

### Translation Support

The plugin includes translation files for:
* English (en_US) - Default language
* Spanish (es_ES) - Full translation included

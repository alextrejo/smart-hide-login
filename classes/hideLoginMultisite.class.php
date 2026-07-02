<?php
namespace SMRT\SMRT_Hide_Login;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once dirname( __FILE__ ) . '/hideLoginAbstract.class.php';

class HideLogin extends HideLoginAbstract {
    function __construct(){
        parent::__construct();
        //Admin menu
        add_action('network_admin_menu', array($this, 'add_settings_page'));
    }

    /**
     * Get the login slug for multisite
     */
    protected function get_slug() {
        return get_site_option('hide_login_slug', 'dashboard');
    }

    /**
     * Set the login slug for multisite
     */
    protected function set_slug($slug) {
        update_site_option('hide_login_slug', $slug);
    }

    /**
     * Get nonce action for multisite
     */
    protected function get_nonce_action() {
        return 'hide_login_network_settings';
    }

    /**
     * Add settings page for multisite
     */
    public function add_settings_page() {
        add_submenu_page(
            'settings.php',
            __('Hide Login Settings', 'smart-hide-login'),
            __('Hide Login', 'smart-hide-login'),
            'manage_network_options',
            'smart-hide-login',
            array( $this, 'settings_page_callback' )
        );
    }

    protected function save_settings($slug) {
        if ( ! empty( $slug ) ) {
            $current_slug = $this->get_slug();

            if ( $slug !== $current_slug ) {
                $this->set_slug( $slug );
                $sites = get_sites( [ 'number' => 0 ] );

                foreach ( $sites as $site ) {
                    switch_to_blog( $site->blog_id );
                    $rules = get_option( 'rewrite_rules' );
                    if ( ! is_array( $rules ) ) {
                        $rules = [];
                    }

                    // Remove any existing hide_login rule
                    foreach ( $rules as $pattern => $rewrite ) {
                        if ( $rewrite === 'index.php?hide_login=1' ) {
                            unset( $rules[ $pattern ] );
                            break;
                        }
                    }

                    // Prepend new rule at top
                    $new_rule = [ '^' . $slug . '/?$' => 'index.php?hide_login=1' ];
                    $rules = $new_rule + $rules;

                    update_option( 'rewrite_rules', $rules );
                    restore_current_blog();
                }

                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully.', 'smart-hide-login' ) . '</p></div>';

            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Slug submitted is the same as before. Nothing to do.', 'smart-hide-login' ) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Please enter a valid slug.', 'smart-hide-login' ) . '</p></div>';
        }
    }

    /**
     * Flushes rewrite rules on activation for multisite.
     */
    static function activate_plugin() {
        $instance = self::get_instance();
        $sites = get_sites();
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            $instance->add_rewrite_rule();
            flush_rewrite_rules();
            restore_current_blog();
        }
    }

    /**
     * Flushes rewrite rules on deactivation for multisite.
     */
    static function deactivate() {
        $sites = get_sites();
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            flush_rewrite_rules();
            restore_current_blog();
        }
    }
}
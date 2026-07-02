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
        add_action('admin_menu', array($this, 'add_settings_page'));
    }

    /**
     * Get the login slug for single site
     */
    protected function get_slug() {
        return get_option('hide_login_slug', 'dashboard');
    }

    /**
     * Set the login slug for single site
     */
    protected function set_slug($slug) {
        update_option('hide_login_slug', $slug);
    }

    /**
     * Get nonce action for single site
     */
    protected function get_nonce_action() {
        return 'hide_login_settings';
    }

    /**
     * Add settings page for single site
     */
    public function add_settings_page() {
        add_options_page(
            __('Hide Login Settings', 'smart-hide-login'),
            __('Hide Login', 'smart-hide-login'),
            'manage_options',
            'smart-hide-login',
            array( $this, 'settings_page_callback' )
        );
    }

    protected function save_settings($slug){
        if (!empty($slug)) {
            $current_slug = $this->get_slug();
            if($slug != $current_slug){
                $this->set_slug($slug);

                $this->add_rewrite_rule();

                // Flush rewrite rules when slug changes
                flush_rewrite_rules();
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'smart-hide-login') . '</p></div>';
            }else{
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Slug submitted is the same as before Nothing to do.', 'smart-hide-login') . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Please enter a valid slug.', 'smart-hide-login') . '</p></div>';
        }
    }

    /**
     * Flushes rewrite rules on activation for single site.
     */
    static function activate_plugin() {
        $instance = self::get_instance();
        $instance->add_rewrite_rule();
        flush_rewrite_rules();
    }

    /**
     * Flushes rewrite rules on deactivation for single site.
     */
    static function deactivate() {
        flush_rewrite_rules();
    }
}
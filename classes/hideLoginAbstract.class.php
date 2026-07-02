<?php
namespace SMRT\SMRT_Hide_Login;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class HideLoginAbstract {
    /**
     * @var HideLoginAbstract $instance Singleton instance
     */
    protected static $instance;

    function __construct(){
        // Setup text domain
        add_action( 'init', array( $this, 'load_textdomain' ) );

        add_action('init', array($this, 'maybe_add_rewrite_rule'));

        add_action('login_init', array($this, 'handle_login'));

        add_action('login_form', array($this, 'add_nonce'));

        add_action('login_enqueue_scripts', array($this, 'login_scripts'));

        add_action('template_redirect', array($this, 'redirect'));

        add_action('wp_logout', array($this, 'logout'));

        add_filter('query_vars', array($this, 'set_query_var'));

        add_filter('login_errors', array($this, 'handle_error'));
    }

    /**
     * Returns the single instance of this class.
     *
     * @return HideLoginAbstract
     */
    public static function get_instance() {
        if ( ! self::$instance ) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Loads the plugin text domain.
     *
     * @since 1.0.0
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'smart-hide-login', false, 'smart-hide-login/languages/' );
    }

    /**
     * Add rewrite rule or handle form submission
     */
    public function maybe_add_rewrite_rule() {
        $settings_nonce = isset($_POST['hide_login_settings_nonce']) ? sanitize_text_field( wp_unslash($_POST['hide_login_settings_nonce']) ) : '';
        $action = isset($_GET['action']) ? sanitize_text_field( wp_unslash( $_GET['action']) ) : '';
        $plugin = isset($_GET['plugin']) ? sanitize_text_field( wp_unslash($_GET['plugin']) ) : '';
        if ($settings_nonce && wp_verify_nonce($settings_nonce, $this->get_nonce_action())) {
            $slug = sanitize_title( isset($_POST['hide_login_slug']) ? wp_unslash( $_POST['hide_login_slug']) : ''  );
            $this->save_settings($slug);
        }elseif(($action && $action == 'deactivate') && ($plugin && $plugin == 'smart-hide-login/smart-hide-login.php') && is_admin()){
            // Do nothing, plugin is being deactivated
        }else{
            $this->add_rewrite_rule();
        }
    }

    /**
     * Add rewrite rule based on settings
     */
    public function add_rewrite_rule(){
        $slug = $this->get_slug();
        if (!empty($slug)) {
            add_rewrite_rule(
                '^' . $slug . '/?$',
                'index.php?hide_login=1',
                'top'
            );
        }
    }

    public function redirect() {
        if ( get_query_var( 'hide_login' ) ) {
            $login_url = add_query_arg(
                'login_id',
                wp_create_nonce( 'hide-me' ),
                wp_login_url()
            );

            wp_safe_redirect( $login_url );
            exit;
        }
    }

    public function set_query_var($vars) {
        $vars[] = 'hide_login';
        return $vars;
    }

    public function login_scripts(){
        wp_enqueue_style('smart-hide-login', SMRT_HIDE_LOGIN_URL . 'assets/frontend.css', array(), SMRT_HIDE_LOGIN_VERSION);
    }

    /**
     * Hide login page unless accessing via allowed methods
     */
    public function handle_login(){
        $slug = $this->get_slug();
        $path = isset($_SERVER['REQUEST_URI']) ? parse_url(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])))['path'] : '';
        $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
        $allowed = false;

        // Permalink structure is not set, wp-admin override don't work
        if(get_option('permalink_structure') == ''){
            $allowed = true;
        }

        // Check if accessing via our custom slug
        elseif(isset($_GET['login_id']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['login_id'])), 'hide-me')){
            $allowed = true;
        }
        
        // Check if accessing via wp-login.php
        elseif(strpos($path, '/wp-login.php') !== false) {
            //check valid nonce (form submission)
            if(isset($_POST['form_id']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['form_id'])), 'hide-me')){
                $allowed = true;
            //Check for valid actions
            }elseif (in_array($action, [
                'confirm_admin_email',
                'postpass',
                'logout',
                'lostpassword',
                'retrievepassword',
                'resetpass',
                'rp',
                'register',
                'checkemail',
                'confirmaction',
                \WP_Recovery_Mode_Link_Service::LOGIN_ACTION_ENTERED
            ])) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            login_header();
            $this->welcome_page();
            login_footer();
            die();
        }
    }

    /**
     * Add hidden nonce field to login form
     */
    public function add_nonce(){
        echo '<input type="hidden" name="form_id" value="'. esc_attr(wp_create_nonce('hide-me')) .'">' . "\n";
    }

    /**
     * Hide error messages that might reveal login existence
     */
    public function handle_error($error) {
        // Return a generic error message to avoid revealing login page exists
        return __('Something went wrong. Please try again later.', 'smart-hide-login');
    }

    private function welcome_page(){
        // Send proper HTTP status
        status_header(404);
        // Output the welcome page HTML. Hook here to modify or add a custom page.
        $html = apply_filters('hide_login_page_html', $this->page_html());

        echo wp_kses_post($html);
    }

    private function page_html(){
        $logo = get_theme_mod('custom_logo');
        $blog_name = get_option('blogname', __('My Site', 'smart-hide-login'));
        ob_start();
        ?>
            <div class="welcome-container">
                <?php if ($logo): ?>
                    <div class="welcome-logo">
                        <img src="<?php echo esc_url(wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full')[0]); ?>" alt="<?php echo esc_attr($blog_name); ?>">
                    </div>
                <?php else: ?>
                    <h1><?php echo esc_html($blog_name); ?></h1>
                <?php endif; ?>
                <p>
                    <?php
                    /* translators: name of the WordPress Site */
                    printf(esc_html__('Welcome to %s.', 'smart-hide-login'), '<strong>' . esc_html($blog_name) . '</strong>');
                    ?>
                </p>
            </div>       
        <?php
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Settings page callback
     */
    public function settings_page_callback() {
        $nonce_action = $this->get_nonce_action();

        $slug = $this->get_slug();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('WP Hidden Settings', 'smart-hide-login');?></h1>
            <form method="post">
                <?php wp_nonce_field($nonce_action, 'hide_login_settings_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Login Page Slug', 'smart-hide-login'); ?></th>
                        <td>
                            <input type="text" name="hide_login_slug" value="<?php echo esc_attr($slug); ?>" class="regular-text" />
                            <p class="description"><?php echo esc_html__("Enter the slug to use for accessing the login page (e.g., 'dashboard', 'admin', 'login').", 'smart-hide-login');?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function logout(){
        wp_safe_redirect( home_url( $this->get_slug() ) );
        exit;
    }

    static function activate(){
        if(get_option('permalink_structure') == ''){
            $message = sprintf(__('<p>You need to select a Permalink structure in Settings > Permalinks for this plugin to work.</p><p><a href="%s">Go Back</a> | <a href="%s">Set Permalinks</a></p>', 'smart-hide-login'), admin_url('plugins.php'), admin_url('options-permalink.php') );
            deactivate_plugins(plugin_basename(dirname(__DIR__) . '/smart-hide-login.php'));
            wp_die( wp_kses_post( $message ) );
        }else{
            HideLogin::activate_plugin();
        }
    }

    // Abstract methods
    abstract public static function activate_plugin();
    abstract public static function deactivate();
    abstract public function add_settings_page();
    abstract protected function get_slug();
    abstract protected function set_slug($slug);
    abstract protected function get_nonce_action();
    abstract protected function save_settings($slug);
}

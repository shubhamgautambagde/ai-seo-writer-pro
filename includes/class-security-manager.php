<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Security Manager - Handles all security measures
 */
class Security_Manager {

    /**
     * Nonce action name
     * @var string
     */
    const NONCE_ACTION = 'ai_seo_writer_pro_nonce';

    /**
     * Nonce field name
     * @var string
     */
    const NONCE_NAME = 'ai_seo_writer_pro_security';

    /**
     * Rate limit transient prefix
     * @var string
     */
    const RATE_LIMIT_PREFIX = 'ai_seo_writer_pro_rate_limit_';

    /**
     * Initialize security hooks
     */
    public function init() {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_filter('wp_headers', [$this, 'add_security_headers']);
    }

    /**
     * Verify nonce
     * @param string $nonce
     * @return bool
     */
    public function verify_nonce($nonce) {
        return (bool) wp_verify_nonce($nonce, self::NONCE_ACTION);
    }

    /**
     * Get nonce field HTML
     * @return string
     */
    public function get_nonce_field() {
        return wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME, true, false);
    }

    /**
     * Get nonce value
     * @return string
     */
    public function get_nonce() {
        return wp_create_nonce(self::NONCE_ACTION);
    }

    /**
     * Verify AJAX request with nonce and capability
     * @param string $capability
     * @return bool
     */
    public function verify_ajax_request($capability = 'manage_options') {
        if (!isset($_POST[self::NONCE_NAME]) || !$this->verify_nonce($_POST[self::NONCE_NAME])) {
            if (!isset($_GET[self::NONCE_NAME]) || !$this->verify_nonce($_GET[self::NONCE_NAME])) {
                wp_send_json_error(['message' => __('Security check failed.', 'ai-seo-writer-pro')], 403);
                return false;
            }
        }

        if (!current_user_can($capability)) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'ai-seo-writer-pro')], 403);
            return false;
        }

        return true;
    }

    /**
     * Verify REST API request permissions
     * @param \WP_REST_Request $request
     * @param string $capability
     * @return bool|\WP_Error
     */
    public function verify_rest_permission($request, $capability = 'manage_options') {
        if (!current_user_can($capability)) {
            return new \WP_Error(
                'rest_forbidden',
                __('Insufficient permissions.', 'ai-seo-writer-pro'),
                ['status' => 403]
            );
        }
        return true;
    }

    /**
     * Check rate limit
     * @param string $action
     * @param int $limit
     * @param int $window
     * @return bool
     */
    public function check_rate_limit($action, $limit = 10, $window = 60) {
        $user_id = get_current_user_id();
        $transient_key = self::RATE_LIMIT_PREFIX . $action . '_' . $user_id;

        $data = get_transient($transient_key);
        if ($data === false) {
            $data = ['count' => 0, 'window_start' => time()];
        }

        if (time() - $data['window_start'] > $window) {
            $data = ['count' => 0, 'window_start' => time()];
        }

        if ($data['count'] >= $limit) {
            return false;
        }

        $data['count']++;
        set_transient($transient_key, $data, $window);

        return true;
    }

    /**
     * Sanitize text input
     * @param mixed $input
     * @return string
     */
    public function sanitize_text($input) {
        return sanitize_text_field(wp_unslash($input));
    }

    /**
     * Sanitize textarea input
     * @param mixed $input
     * @return string
     */
    public function sanitize_textarea($input) {
        return sanitize_textarea_field(wp_unslash($input));
    }

    /**
     * Sanitize HTML content (allows limited HTML)
     * @param mixed $input
     * @return string
     */
    public function sanitize_html($input) {
        return wp_kses_post(wp_unslash($input));
    }

    /**
     * Sanitize array of strings
     * @param array $input
     * @return array
     */
    public function sanitize_array($input) {
        if (!is_array($input)) {
            return [];
        }
        return array_map([$this, 'sanitize_text'], $input);
    }

    /**
     * Sanitize integer
     * @param mixed $input
     * @return int
     */
    public function sanitize_int($input) {
        return absint($input);
    }

    /**
     * Sanitize URL
     * @param mixed $input
     * @return string
     */
    public function sanitize_url($input) {
        return esc_url_raw(wp_unslash($input));
    }

    /**
     * Sanitize email
     * @param mixed $input
     * @return string
     */
    public function sanitize_email($input) {
        return sanitize_email(wp_unslash($input));
    }

    /**
     * Escape output for HTML display
     * @param string $text
     * @return string
     */
    public function esc_html($text) {
        return esc_html($text);
    }

    /**
     * Escape output for HTML attribute
     * @param string $text
     * @return string
     */
    public function esc_attr($text) {
        return esc_attr($text);
    }

    /**
     * Escape output for URL
     * @param string $url
     * @return string
     */
    public function esc_url($url) {
        return esc_url($url);
    }

    /**
     * Escape output for JavaScript
     * @param string $text
     * @return string
     */
    public function esc_js($text) {
        return esc_js($text);
    }

    /**
     * Encrypt sensitive data (API keys)
     * @param string $data
     * @return string
     */
    public function encrypt($data) {
        if (empty($data)) {
            return '';
        }

        $key = $this->get_encryption_key();
        $salt = wp_generate_password(16, false);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, substr($salt, 0, 16));

        return base64_encode($salt . $encrypted);
    }

    /**
     * Decrypt sensitive data
     * @param string $encrypted_data
     * @return string
     */
    public function decrypt($encrypted_data) {
        if (empty($encrypted_data)) {
            return '';
        }

        $key = $this->get_encryption_key();
        $decoded = base64_decode($encrypted_data);

        if ($decoded === false || strlen($decoded) < 16) {
            return '';
        }

        $salt = substr($decoded, 0, 16);
        $encrypted = substr($decoded, 16);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, substr($salt, 0, 16));

        return $decrypted !== false ? $decrypted : '';
    }

    /**
     * Get encryption key
     * @return string
     */
    private function get_encryption_key() {
        if (defined('AI_SEO_WRITER_PRO_ENCRYPTION_KEY') && AI_SEO_WRITER_PRO_ENCRYPTION_KEY) {
            return AI_SEO_WRITER_PRO_ENCRYPTION_KEY;
        }

        if (defined('LOGGED_IN_KEY') && LOGGED_IN_KEY) {
            return hash('sha256', LOGGED_IN_KEY);
        }

        return hash('sha256', 'ai_seo_writer_pro_default_key_change_me');
    }

    /**
     * Mask API key for display
     * @param string $key
     * @return string
     */
    public function mask_api_key($key) {
        if (empty($key)) {
            return '';
        }
        $length = strlen($key);
        if ($length <= 8) {
            return str_repeat('•', $length);
        }
        return substr($key, 0, 4) . str_repeat('•', $length - 8) . substr($key, -4);
    }

    /**
     * Log error
     * @param string $message
     * @param array $context
     */
    public function log_error($message, $context = []) {
        $settings = get_option('ai_seo_writer_pro_settings', []);
        if (empty($settings['logging_enabled'])) {
            return;
        }

        $log_entry = sprintf(
            '[%s] %s | Context: %s',
            current_time('mysql'),
            $message,
            wp_json_encode($context)
        );

        error_log('AI SEO Writer Pro: ' . $log_entry);
    }

    /**
     * Add security headers
     * @param array $headers
     * @return array
     */
    public function add_security_headers($headers) {
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        $headers['X-XSS-Protection'] = '1; mode=block';
        $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
        return $headers;
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('ai-seo-writer-pro/v1', '/nonce', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_nonce'],
            'permission_callback' => function ($request) {
                return $this->verify_rest_permission($request, 'manage_options');
            },
        ]);
    }

    /**
     * REST endpoint to get nonce
     * @return \WP_REST_Response
     */
    public function rest_get_nonce() {
        return new \WP_REST_Response([
            'nonce' => $this->get_nonce(),
        ]);
    }

    /**
     * Validate user capability
     * @param string $capability
     * @return bool
     */
    public function current_user_can($capability = 'manage_options') {
        return current_user_can($capability);
    }

    /**
     * Get required capability for plugin operations
     * @return string
     */
    public function get_required_capability() {
        return apply_filters('ai_seo_writer_pro_required_capability', 'manage_options');
    }
}

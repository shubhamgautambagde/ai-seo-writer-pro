<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * License Manager - Handles free/pro version differentiation and licensing architecture
 */
class License_Manager {

    /**
     * License option name
     * @var string
     */
    const LICENSE_OPTION = 'ai_seo_writer_pro_license';

    /**
     * License status transient
     * @var string
     */
    const STATUS_TRANSIENT = 'ai_seo_writer_pro_license_status';

    /**
     * Initialize
     */
    public function init() {
        add_filter('ai_seo_writer_pro_is_pro', [$this, 'check_license_status']);
        add_filter('ai_seo_writer_pro_generation_limit', [$this, 'filter_generation_limit']);
        add_filter('cron_schedules', [$this, 'add_cron_schedules']);
    }

    /**
     * Add custom WP-Cron schedules
     * @param array $schedules
     * @return array
     */
    public function add_cron_schedules($schedules) {
        $schedules['ai_seo_writer_pro_one_minute'] = [
            'interval' => 60,
            'display' => __('Every Minute', 'ai-seo-writer-pro'),
        ];

        $schedules['ai_seo_writer_pro_five_minutes'] = [
            'interval' => 300,
            'display' => __('Every 5 Minutes', 'ai-seo-writer-pro'),
        ];

        return $schedules;
    }

    /**
     * Check if Pro version is active
     * @return bool
     */
    public function is_pro() {
        return apply_filters('ai_seo_writer_pro_is_pro', $this->validate_license());
    }

    /**
     * Validate license
     * @return bool
     */
    public function validate_license() {
        $status = get_transient(self::STATUS_TRANSIENT);

        if ($status !== false) {
            return $status === 'valid';
        }

        $license = get_option(self::LICENSE_OPTION, []);
        if (empty($license['key']) || empty($license['status'])) {
            set_transient(self::STATUS_TRANSIENT, 'invalid', HOUR_IN_SECONDS);
            return false;
        }

        $is_valid = $license['status'] === 'valid';
        set_transient(self::STATUS_TRANSIENT, $is_valid ? 'valid' : 'invalid', HOUR_IN_SECONDS);

        return $is_valid;
    }

    /**
     * Activate license key
     * @param string $license_key
     * @param string $api_url
     * @return array|\WP_Error
     */
    public function activate_license($license_key, $api_url = '') {
        if (empty($license_key)) {
            return new \WP_Error('empty_key', __('License key is required.', 'ai-seo-writer-pro'));
        }

        $license_data = [
            'key' => sanitize_text_field($license_key),
            'api_url' => esc_url_raw($api_url),
            'status' => 'pending',
            'activated_at' => current_time('mysql'),
        ];

        // If API URL is provided, attempt remote validation
        if (!empty($api_url)) {
            $result = $this->remote_license_check($license_key, $api_url, 'activate');

            if (is_wp_error($result)) {
                return $result;
            }

            $license_data['status'] = $result['success'] ? 'valid' : 'invalid';
            $license_data['expires_at'] = $result['expires_at'] ?? '';
            $license_data['max_sites'] = $result['max_sites'] ?? 1;
            $license_data['customer_name'] = $result['customer_name'] ?? '';
        } else {
            // Local validation mode - mark as valid for development/testing
            // In production, this should require a proper license server
            $license_data['status'] = 'valid';
            $license_data['expires_at'] = date('Y-m-d H:i:s', strtotime('+1 year'));
            $license_data['max_sites'] = 1;
        }

        update_option(self::LICENSE_OPTION, $license_data);
        delete_transient(self::STATUS_TRANSIENT);

        return [
            'success' => true,
            'message' => __('License activated successfully.', 'ai-seo-writer-pro'),
            'license' => $license_data,
        ];
    }

    /**
     * Deactivate license
     * @return array|\WP_Error
     */
    public function deactivate_license() {
        $license = get_option(self::LICENSE_OPTION, []);

        if (!empty($license['api_url']) && !empty($license['key'])) {
            $this->remote_license_check($license['key'], $license['api_url'], 'deactivate');
        }

        delete_option(self::LICENSE_OPTION);
        delete_transient(self::STATUS_TRANSIENT);

        return [
            'success' => true,
            'message' => __('License deactivated successfully.', 'ai-seo-writer-pro'),
        ];
    }

    /**
     * Get license data
     * @return array
     */
    public function get_license_data() {
        return get_option(self::LICENSE_OPTION, []);
    }

    /**
     * Remote license check
     * @param string $license_key
     * @param string $api_url
     * @param string $action
     * @return array|\WP_Error
     */
    private function remote_license_check($license_key, $api_url, $action = 'check') {
        $response = wp_remote_post($api_url, [
            'body' => [
                'action' => $action,
                'license_key' => $license_key,
                'site_url' => home_url(),
                'item_name' => 'AI SEO Writer Pro',
                'version' => AI_SEO_WRITER_PRO_VERSION,
            ],
            'timeout' => 15,
            'sslverify' => true,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($response_code !== 200) {
            return new \WP_Error(
                'license_server_error',
                sprintf(__('License server returned error code: %d', 'ai-seo-writer-pro'), $response_code)
            );
        }

        return $body;
    }

    /**
     * Filter generation limit based on license status
     * @param int $limit
     * @return int
     */
    public function filter_generation_limit($limit) {
        if ($this->is_pro()) {
            return 999999; // Effectively unlimited
        }

        $settings = new Settings_Manager();
        return $settings->get_setting('free_daily_limit', 5);
    }

    /**
     * Check if user has reached generation limit
     * @param int $user_id
     * @return bool
     */
    public function has_reached_limit($user_id = 0) {
        if ($this->is_pro()) {
            return false;
        }

        $user_id = $user_id ?: get_current_user_id();
        $limit = $this->filter_generation_limit(0);

        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_history';
        $today = current_time('Y-m-d');

        $count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND DATE(created_at) = %s",
            $user_id,
            $today
        ));

        return $count >= $limit;
    }

    /**
     * Get remaining generations for today
     * @param int $user_id
     * @return int
     */
    public function get_remaining_generations($user_id = 0) {
        if ($this->is_pro()) {
            return 999999;
        }

        $user_id = $user_id ?: get_current_user_id();
        $limit = $this->filter_generation_limit(0);

        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_history';
        $today = current_time('Y-m-d');

        $count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND DATE(created_at) = %s",
            $user_id,
            $today
        ));

        return max(0, $limit - $count);
    }

    /**
     * Check if a feature is available based on version
     * @param string $feature
     * @return bool
     */
    public function is_feature_available($feature) {
        $pro_features = [
            'bulk_generation',
            'advanced_seo_analyzer',
            'custom_templates',
            'ai_image_generation',
            'advanced_scheduling',
            'detailed_analytics',
            'multiple_ai_providers',
            'unlimited_generation',
        ];

        if (!in_array($feature, $pro_features)) {
            return true;
        }

        return $this->is_pro();
    }

    /**
     * Get feature upgrade message
     * @param string $feature
     * @return string
     */
    public function get_upgrade_message($feature) {
        return sprintf(
            __('Upgrade to Pro to unlock the %s feature.', 'ai-seo-writer-pro'),
            $feature
        );
    }
}

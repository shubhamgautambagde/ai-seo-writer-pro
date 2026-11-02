<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Manager - Handles plugin settings
 */
class Settings_Manager {

    /**
     * Option name
     * @var string
     */
    const OPTION_NAME = 'ai_seo_writer_pro_settings';

    /**
     * API keys option name (stored separately for security)
     * @var string
     */
    const API_KEYS_OPTION = 'ai_seo_writer_pro_api_keys';

    /**
     * Cached settings
     * @var array|null
     */
    private $settings = null;

    /**
     * Cached API keys
     * @var array|null
     */
    private $api_keys = null;

    /**
     * Initialize
     */
    public function init() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Register WordPress settings
     */
    public function register_settings() {
        register_setting(
            'ai_seo_writer_pro_settings_group',
            self::OPTION_NAME,
            [
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => $this->get_default_settings(),
            ]
        );
    }

    /**
     * Get default settings
     * @return array
     */
    public function get_default_settings() {
        return [
            'default_ai_provider' => 'openai',
            'default_model' => 'gpt-4o',
            'default_language' => 'en',
            'default_tone' => 'professional',
            'default_content_type' => 'blog',
            'default_article_length' => 1500,
            'max_generation_tokens' => 4000,
            'temperature' => 0.7,
            'top_p' => 1.0,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.0,
            'free_daily_limit' => 5,
            'seo_analyzer_enabled' => true,
            'auto_save_drafts' => true,
            'image_generation_enabled' => false,
            'image_provider' => 'dall-e',
            'logging_enabled' => true,
            'rate_limit_per_minute' => 10,
            'delete_data_on_uninstall' => false,
            'gutenberg_integration' => true,
        ];
    }

    /**
     * Get all settings
     * @return array
     */
    public function get_settings() {
        if ($this->settings === null) {
            $defaults = $this->get_default_settings();
            $saved = get_option(self::OPTION_NAME, []);
            $this->settings = wp_parse_args($saved, $defaults);
        }
        return $this->settings;
    }

    /**
     * Get a specific setting
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get_setting($key, $default = null) {
        $settings = $this->get_settings();
        return $settings[$key] ?? $default;
    }

    /**
     * Update settings
     * @param array $new_settings
     * @return bool
     */
    public function update_settings($new_settings) {
        $this->settings = null;
        return update_option(self::OPTION_NAME, $new_settings);
    }

    /**
     * Sanitize settings
     * @param array $input
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        $defaults = $this->get_default_settings();

        $sanitized['default_ai_provider'] = sanitize_text_field($input['default_ai_provider'] ?? $defaults['default_ai_provider']);
        $sanitized['default_model'] = sanitize_text_field($input['default_model'] ?? $defaults['default_model']);
        $sanitized['default_language'] = sanitize_text_field($input['default_language'] ?? $defaults['default_language']);
        $sanitized['default_tone'] = sanitize_text_field($input['default_tone'] ?? $defaults['default_tone']);
        $sanitized['default_content_type'] = sanitize_text_field($input['default_content_type'] ?? $defaults['default_content_type']);
        $sanitized['default_article_length'] = absint($input['default_article_length'] ?? $defaults['default_article_length']);
        $sanitized['max_generation_tokens'] = absint($input['max_generation_tokens'] ?? $defaults['max_generation_tokens']);
        $sanitized['temperature'] = floatval($input['temperature'] ?? $defaults['temperature']);
        $sanitized['temperature'] = max(0, min(2, $sanitized['temperature']));
        $sanitized['top_p'] = floatval($input['top_p'] ?? $defaults['top_p']);
        $sanitized['top_p'] = max(0, min(1, $sanitized['top_p']));
        $sanitized['frequency_penalty'] = floatval($input['frequency_penalty'] ?? $defaults['frequency_penalty']);
        $sanitized['frequency_penalty'] = max(-2, min(2, $sanitized['frequency_penalty']));
        $sanitized['presence_penalty'] = floatval($input['presence_penalty'] ?? $defaults['presence_penalty']);
        $sanitized['presence_penalty'] = max(-2, min(2, $sanitized['presence_penalty']));
        $sanitized['free_daily_limit'] = absint($input['free_daily_limit'] ?? $defaults['free_daily_limit']);
        $sanitized['seo_analyzer_enabled'] = !empty($input['seo_analyzer_enabled']);
        $sanitized['auto_save_drafts'] = !empty($input['auto_save_drafts']);
        $sanitized['image_generation_enabled'] = !empty($input['image_generation_enabled']);
        $sanitized['image_provider'] = sanitize_text_field($input['image_provider'] ?? $defaults['image_provider']);
        $sanitized['logging_enabled'] = !empty($input['logging_enabled']);
        $sanitized['rate_limit_per_minute'] = absint($input['rate_limit_per_minute'] ?? $defaults['rate_limit_per_minute']);
        $sanitized['delete_data_on_uninstall'] = !empty($input['delete_data_on_uninstall']);
        $sanitized['gutenberg_integration'] = !empty($input['gutenberg_integration']);

        return $sanitized;
    }

    /**
     * Get API key for a provider
     * @param string $provider
     * @return string
     */
    public function get_api_key($provider) {
        if ($this->api_keys === null) {
            $this->api_keys = get_option(self::API_KEYS_OPTION, []);
        }

        if (empty($this->api_keys[$provider])) {
            return '';
        }

        $security = new Security_Manager();
        return $security->decrypt($this->api_keys[$provider]);
    }

    /**
     * Save API key for a provider
     * @param string $provider
     * @param string $key
     * @return bool
     */
    public function save_api_key($provider, $key) {
        if ($this->api_keys === null) {
            $this->api_keys = get_option(self::API_KEYS_OPTION, []);
        }

        $security = new Security_Manager();

        if (empty($key)) {
            unset($this->api_keys[$provider]);
        } else {
            $this->api_keys[$provider] = $security->encrypt($key);
        }

        return update_option(self::API_KEYS_OPTION, $this->api_keys);
    }

    /**
     * Get masked API key for display
     * @param string $provider
     * @return string
     */
    public function get_masked_api_key($provider) {
        if ($this->api_keys === null) {
            $this->api_keys = get_option(self::API_KEYS_OPTION, []);
        }

        if (empty($this->api_keys[$provider])) {
            return '';
        }

        $security = new Security_Manager();
        $decrypted = $security->decrypt($this->api_keys[$provider]);
        return $security->mask_api_key($decrypted);
    }

    /**
     * Check if API key exists for provider
     * @param string $provider
     * @return bool
     */
    public function has_api_key($provider) {
        if ($this->api_keys === null) {
            $this->api_keys = get_option(self::API_KEYS_OPTION, []);
        }
        return !empty($this->api_keys[$provider]);
    }

    /**
     * Get custom API endpoint settings
     * @return array
     */
    public function get_custom_api_settings() {
        return get_option('ai_seo_writer_pro_custom_api', [
            'endpoint' => '',
            'method' => 'POST',
            'headers' => '',
            'body_template' => '',
            'response_path' => '',
            'auth_type' => 'bearer',
        ]);
    }

    /**
     * Save custom API endpoint settings
     * @param array $settings
     * @return bool
     */
    public function save_custom_api_settings($settings) {
        $sanitized = [
            'endpoint' => esc_url_raw($settings['endpoint'] ?? ''),
            'method' => sanitize_text_field($settings['method'] ?? 'POST'),
            'headers' => sanitize_textarea_field($settings['headers'] ?? ''),
            'body_template' => wp_kses_post($settings['body_template'] ?? ''),
            'response_path' => sanitize_text_field($settings['response_path'] ?? ''),
            'auth_type' => sanitize_text_field($settings['auth_type'] ?? 'bearer'),
        ];
        return update_option('ai_seo_writer_pro_custom_api', $sanitized);
    }

    /**
     * Get available languages
     * @return array
     */
    public function get_languages() {
        return [
            'en' => __('English', 'ai-seo-writer-pro'),
            'es' => __('Spanish', 'ai-seo-writer-pro'),
            'fr' => __('French', 'ai-seo-writer-pro'),
            'de' => __('German', 'ai-seo-writer-pro'),
            'it' => __('Italian', 'ai-seo-writer-pro'),
            'pt' => __('Portuguese', 'ai-seo-writer-pro'),
            'nl' => __('Dutch', 'ai-seo-writer-pro'),
            'ru' => __('Russian', 'ai-seo-writer-pro'),
            'ja' => __('Japanese', 'ai-seo-writer-pro'),
            'zh' => __('Chinese', 'ai-seo-writer-pro'),
            'ko' => __('Korean', 'ai-seo-writer-pro'),
            'ar' => __('Arabic', 'ai-seo-writer-pro'),
            'hi' => __('Hindi', 'ai-seo-writer-pro'),
            'tr' => __('Turkish', 'ai-seo-writer-pro'),
            'pl' => __('Polish', 'ai-seo-writer-pro'),
            'sv' => __('Swedish', 'ai-seo-writer-pro'),
        ];
    }

    /**
     * Get available writing tones
     * @return array
     */
    public function get_tones() {
        return [
            'professional' => __('Professional', 'ai-seo-writer-pro'),
            'conversational' => __('Conversational', 'ai-seo-writer-pro'),
            'formal' => __('Formal', 'ai-seo-writer-pro'),
            'casual' => __('Casual', 'ai-seo-writer-pro'),
            'academic' => __('Academic', 'ai-seo-writer-pro'),
            'persuasive' => __('Persuasive', 'ai-seo-writer-pro'),
            'informative' => __('Informative', 'ai-seo-writer-pro'),
            'entertaining' => __('Entertaining', 'ai-seo-writer-pro'),
            'friendly' => __('Friendly', 'ai-seo-writer-pro'),
            'authoritative' => __('Authoritative', 'ai-seo-writer-pro'),
            'technical' => __('Technical', 'ai-seo-writer-pro'),
            'simplified' => __('Simplified', 'ai-seo-writer-pro'),
        ];
    }

    /**
     * Get available content types
     * @return array
     */
    public function get_content_types() {
        return [
            'blog' => __('SEO Blog Article', 'ai-seo-writer-pro'),
            'howto' => __('How-to Guide', 'ai-seo-writer-pro'),
            'informational' => __('Informational Article', 'ai-seo-writer-pro'),
            'review' => __('Product Review', 'ai-seo-writer-pro'),
            'comparison' => __('Comparison Article', 'ai-seo-writer-pro'),
            'listicle' => __('Listicle', 'ai-seo-writer-pro'),
            'news' => __('News Article', 'ai-seo-writer-pro'),
            'local_seo' => __('Local SEO Article', 'ai-seo-writer-pro'),
            'landing_page' => __('Landing Page Content', 'ai-seo-writer-pro'),
            'pillar' => __('Pillar Content', 'ai-seo-writer-pro'),
        ];
    }

    /**
     * Get search intent options
     * @return array
     */
    public function get_search_intents() {
        return [
            'informational' => __('Informational', 'ai-seo-writer-pro'),
            'navigational' => __('Navigational', 'ai-seo-writer-pro'),
            'commercial' => __('Commercial Investigation', 'ai-seo-writer-pro'),
            'transactional' => __('Transactional', 'ai-seo-writer-pro'),
        ];
    }

    /**
     * Get article length options
     * @return array
     */
    public function get_article_lengths() {
        return [
            500 => __('Short (500 words)', 'ai-seo-writer-pro'),
            1000 => __('Medium (1000 words)', 'ai-seo-writer-pro'),
            1500 => __('Standard (1500 words)', 'ai-seo-writer-pro'),
            2000 => __('Long (2000 words)', 'ai-seo-writer-pro'),
            3000 => __('Extended (3000 words)', 'ai-seo-writer-pro'),
            5000 => __('Comprehensive (5000 words)', 'ai-seo-writer-pro'),
        ];
    }
}

<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract base class for all AI providers
 */
abstract class AI_Provider_Base {

    /**
     * Provider identifier (e.g., 'openai', 'gemini')
     * @var string
     */
    protected $provider_id = '';

    /**
     * Provider display name
     * @var string
     */
    protected $provider_name = '';

    /**
     * API key
     * @var string
     */
    protected $api_key = '';

    /**
     * Settings manager instance
     * @var Settings_Manager
     */
    protected $settings;

    /**
     * Security manager instance
     * @var Security_Manager
     */
    protected $security;

    /**
     * Database manager instance
     * @var Database_Manager
     */
    protected $database;

    /**
     * Maximum number of retries for failed requests
     * @var int
     */
    protected $max_retries = 3;

    /**
     * Delay between retries in seconds
     * @var int
     */
    protected $retry_delay = 2;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = new Settings_Manager();
        $this->security = new Security_Manager();
        $this->database = new Database_Manager();
    }

    /**
     * Initialize the provider
     */
    public function init() {
        $this->api_key = $this->settings->get_api_key($this->provider_id);
    }

    /**
     * Generate text using the AI provider
     *
     * @param array $messages Array of message arrays with 'role' and 'content'
     * @param array $args Additional arguments (model, temperature, max_tokens, etc.)
     * @return array Structured response with content, tokens_used, model, error
     */
    abstract public function generate_text($messages, $args = []);

    /**
     * Test the API connection
     *
     * @return array Structured response with success status and message
     */
    abstract public function test_connection();

    /**
     * Get available models for this provider
     *
     * @return array Array of model identifiers and names
     */
    abstract public function get_models();

    /**
     * Get API usage statistics
     *
     * @param array $args Optional filter arguments
     * @return array Usage data
     */
    abstract public function get_usage($args = []);

    /**
     * Format prompt/messages for the provider's API
     *
     * @param array|string $input Raw input
     * @return array Formatted messages
     */
    public function format_prompt($input) {
        if (is_string($input)) {
            return [
                [
                    'role' => 'user',
                    'content' => $input,
                ],
            ];
        }

        if (is_array($input)) {
            $formatted = [];
            foreach ($input as $msg) {
                if (is_array($msg) && isset($msg['role'], $msg['content'])) {
                    $formatted[] = [
                        'role' => in_array($msg['role'], ['system', 'user', 'assistant']) ? $msg['role'] : 'user',
                        'content' => (string) $msg['content'],
                    ];
                }
            }
            return $formatted;
        }

        return [];
    }

    /**
     * Get provider ID
     *
     * @return string
     */
    public function get_provider_id() {
        return $this->provider_id;
    }

    /**
     * Get provider name
     *
     * @return string
     */
    public function get_provider_name() {
        return $this->provider_name;
    }

    /**
     * Check if rate limit allows a request
     *
     * @return bool
     */
    protected function check_rate_limit() {
        $limit = $this->settings->get_setting('rate_limit_per_minute', 10);
        return $this->security->check_rate_limit('ai_request_' . $this->provider_id, $limit, 60);
    }

    /**
     * Log an API call to the database
     *
     * @param array $data Log data
     */
    protected function log_api_call($data) {
        $defaults = [
            'user_id' => get_current_user_id(),
            'ai_provider' => $this->provider_id,
            'ai_model' => '',
            'endpoint' => '',
            'request_type' => 'generate',
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
            'response_time' => 0,
            'http_status' => 0,
            'is_error' => 0,
            'error_message' => '',
        ];

        $log_data = wp_parse_args($data, $defaults);
        $this->database->insert_api_log($log_data);
    }

    /**
     * Make an HTTP request with retry logic
     *
     * @param string $url Request URL
     * @param array $args WP HTTP request arguments
     * @param string $endpoint Endpoint identifier for logging
     * @param string $model Model identifier
     * @param int $prompt_tokens Estimated prompt tokens
     * @return array|\WP_Error Response body or WP_Error
     */
    protected function make_request($url, $args, $endpoint = '', $model = '', $prompt_tokens = 0) {
        if (!$this->check_rate_limit()) {
            $this->log_api_call([
                'ai_model' => $model,
                'endpoint' => $endpoint,
                'prompt_tokens' => $prompt_tokens,
                'is_error' => 1,
                'error_message' => __('Rate limit exceeded. Please try again later.', 'ai-seo-writer-pro'),
            ]);
            return new \WP_Error(
                'rate_limit_exceeded',
                __('Rate limit exceeded. Please try again later.', 'ai-seo-writer-pro')
            );
        }

        $start_time = microtime(true);
        $last_error = null;

        for ($attempt = 1; $attempt <= $this->max_retries; $attempt++) {
            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                $last_error = $response;
                if ($attempt < $this->max_retries) {
                    sleep($this->retry_delay * $attempt);
                    continue;
                }
                $this->log_api_call([
                    'ai_model' => $model,
                    'endpoint' => $endpoint,
                    'prompt_tokens' => $prompt_tokens,
                    'response_time' => microtime(true) - $start_time,
                    'is_error' => 1,
                    'error_message' => $response->get_error_message(),
                ]);
                return $response;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            // Retry on server errors (5xx) and rate limit (429)
            if ($response_code >= 500 || $response_code === 429) {
                if ($attempt < $this->max_retries) {
                    sleep($this->retry_delay * $attempt);
                    continue;
                }
            }

            $response_time = microtime(true) - $start_time;
            $decoded = json_decode($body, true);

            if ($response_code >= 400) {
                $error_msg = $this->extract_error_message($decoded, $body);
                $this->log_api_call([
                    'ai_model' => $model,
                    'endpoint' => $endpoint,
                    'prompt_tokens' => $prompt_tokens,
                    'response_time' => $response_time,
                    'http_status' => $response_code,
                    'is_error' => 1,
                    'error_message' => $error_msg,
                ]);
                return new \WP_Error(
                    'http_error_' . $response_code,
                    $error_msg,
                    ['status' => $response_code, 'body' => $decoded]
                );
            }

            // Log successful request (tokens will be updated by caller if available)
            $this->log_api_call([
                'ai_model' => $model,
                'endpoint' => $endpoint,
                'prompt_tokens' => $prompt_tokens,
                'response_time' => $response_time,
                'http_status' => $response_code,
            ]);

            return $decoded !== null ? $decoded : $body;
        }

        return $last_error;
    }

    /**
     * Extract error message from API response
     *
     * @param mixed $decoded Decoded JSON response
     * @param string $body Raw response body
     * @return string
     */
    protected function extract_error_message($decoded, $body) {
        if (is_array($decoded)) {
            if (isset($decoded['error']['message'])) {
                return $decoded['error']['message'];
            }
            if (isset($decoded['error'])) {
                if (is_string($decoded['error'])) {
                    return $decoded['error'];
                }
                if (is_array($decoded['error']) && isset($decoded['error']['code'])) {
                    return $decoded['error']['code'] . ': ' . ($decoded['error']['message'] ?? 'Unknown error');
                }
            }
            if (isset($decoded['message'])) {
                return $decoded['message'];
            }
        }
        return !empty($body) ? substr(wp_strip_all_tags($body), 0, 500) : __('Unknown API error.', 'ai-seo-writer-pro');
    }

    /**
     * Estimate token count for a given text
     * Rough estimate: ~4 chars per token for English
     *
     * @param string $text
     * @return int
     */
    protected function estimate_tokens($text) {
        if (empty($text)) {
            return 0;
        }
        $word_count = str_word_count($text);
        $char_count = mb_strlen($text);
        // Average of word-based and char-based estimates
        return (int) ceil(($word_count * 1.3 + $char_count / 4) / 2);
    }

    /**
     * Build a standardized success response
     *
     * @param string $content Generated content
     * @param int $tokens_used Total tokens used
     * @param string $model Model used
     * @return array
     */
    protected function build_success_response($content, $tokens_used = 0, $model = '') {
        return [
            'success' => true,
            'content' => $content,
            'tokens_used' => $tokens_used,
            'model' => $model,
            'error' => '',
        ];
    }

    /**
     * Build a standardized error response
     *
     * @param string $error_message Error message
     * @param string $model Model used
     * @return array
     */
    protected function build_error_response($error_message, $model = '') {
        return [
            'success' => false,
            'content' => '',
            'tokens_used' => 0,
            'model' => $model,
            'error' => $error_message,
        ];
    }
}

/**
 * AI Providers Factory - Instantiates the correct provider class
 */
class AI_Providers {

    /**
     * Provider instances cache
     * @var array
     */
    private $providers = [];

    /**
     * Initialize
     */
    public function init() {
        // Provider classes are loaded via autoloader
    }

    /**
     * Get a provider instance by ID
     *
     * @param string $provider_id Provider identifier
     * @return AI_Provider_Base|null Provider instance or null
     */
    public function get_provider($provider_id = '') {
        if (empty($provider_id)) {
            $provider_id = $this->settings()->get_setting('default_ai_provider', 'openai');
        }

        $provider_id = sanitize_key($provider_id);

        if (isset($this->providers[$provider_id])) {
            return $this->providers[$provider_id];
        }

        $provider = $this->create_provider($provider_id);

        if ($provider !== null) {
            $provider->init();
            $this->providers[$provider_id] = $provider;
        }

        return $provider;
    }

    /**
     * Create a provider instance
     *
     * @param string $provider_id
     * @return AI_Provider_Base|null
     */
    private function create_provider($provider_id) {
        switch ($provider_id) {
            case 'openai':
                return new OpenAI_Provider();

            case 'gemini':
                return new Gemini_Provider();

            case 'claude':
                return new Claude_Provider();

            case 'openrouter':
                return new OpenRouter_Provider();

            case 'custom':
                return new Custom_Provider();

            default:
                $this->security()->log_error('Unknown AI provider requested', ['provider' => $provider_id]);
                return null;
        }
    }

    /**
     * Get all available providers with their info
     *
     * @return array
     */
    public function get_available_providers() {
        $providers = [
            'openai' => [
                'id' => 'openai',
                'name' => __('OpenAI', 'ai-seo-writer-pro'),
                'description' => __('GPT-4o, GPT-4o mini, GPT-4 Turbo, GPT-3.5 Turbo', 'ai-seo-writer-pro'),
                'class' => 'OpenAI_Provider',
            ],
            'gemini' => [
                'id' => 'gemini',
                'name' => __('Google Gemini', 'ai-seo-writer-pro'),
                'description' => __('Gemini 1.5 Pro, Gemini 1.5 Flash, Gemini 1.0 Pro', 'ai-seo-writer-pro'),
                'class' => 'Gemini_Provider',
            ],
            'claude' => [
                'id' => 'claude',
                'name' => __('Anthropic Claude', 'ai-seo-writer-pro'),
                'description' => __('Claude 3.5 Sonnet, Claude 3 Opus, Claude 3 Haiku', 'ai-seo-writer-pro'),
                'class' => 'Claude_Provider',
            ],
            'openrouter' => [
                'id' => 'openrouter',
                'name' => __('OpenRouter', 'ai-seo-writer-pro'),
                'description' => __('Access multiple AI models through OpenRouter', 'ai-seo-writer-pro'),
                'class' => 'OpenRouter_Provider',
            ],
            'custom' => [
                'id' => 'custom',
                'name' => __('Custom API', 'ai-seo-writer-pro'),
                'description' => __('Connect to any custom AI API endpoint', 'ai-seo-writer-pro'),
                'class' => 'Custom_Provider',
            ],
        ];

        return apply_filters('ai_seo_writer_pro_available_providers', $providers);
    }

    /**
     * Get models for a specific provider
     *
     * @param string $provider_id
     * @return array
     */
    public function get_provider_models($provider_id) {
        $provider = $this->get_provider($provider_id);
        if ($provider === null) {
            return [];
        }
        return $provider->get_models();
    }

    /**
     * Test connection for a specific provider
     *
     * @param string $provider_id
     * @return array
     */
    public function test_provider_connection($provider_id) {
        $provider = $this->get_provider($provider_id);
        if ($provider === null) {
            return [
                'success' => false,
                'message' => sprintf(__('Provider "%s" not found.', 'ai-seo-writer-pro'), $provider_id),
            ];
        }
        return $provider->test_connection();
    }

    /**
     * Generate text using the specified provider
     *
     * @param string $provider_id
     * @param array $messages
     * @param array $args
     * @return array
     */
    public function generate_text($provider_id, $messages, $args = []) {
        $provider = $this->get_provider($provider_id);
        if ($provider === null) {
            return [
                'success' => false,
                'content' => '',
                'tokens_used' => 0,
                'model' => '',
                'error' => sprintf(__('Provider "%s" not found.', 'ai-seo-writer-pro'), $provider_id),
            ];
        }
        return $provider->generate_text($messages, $args);
    }

    /**
     * Get settings manager instance
     *
     * @return Settings_Manager
     */
    private function settings() {
        static $settings = null;
        if ($settings === null) {
            $settings = new Settings_Manager();
        }
        return $settings;
    }

    /**
     * Get security manager instance
     *
     * @return Security_Manager
     */
    private function security() {
        static $security = null;
        if ($security === null) {
            $security = new Security_Manager();
        }
        return $security;
    }
}

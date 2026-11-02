<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom Provider - Integration with any custom AI API endpoint
 */
class Custom_Provider extends AI_Provider_Base {

    /**
     * Provider identifier
     * @var string
     */
    protected $provider_id = 'custom';

    /**
     * Provider display name
     * @var string
     */
    protected $provider_name = 'Custom API';

    /**
     * Custom API settings
     * @var array
     */
    private $custom_settings = [];

    /**
     * Initialize the provider
     */
    public function init() {
        parent::init();
        $this->custom_settings = $this->settings->get_custom_api_settings();
    }

    /**
     * Get available models
     *
     * @return array
     */
    public function get_models() {
        return [
            'custom-model' => [
                'id' => 'custom-model',
                'name' => __('Custom Model', 'ai-seo-writer-pro'),
                'description' => __('Uses the configured custom API endpoint', 'ai-seo-writer-pro'),
                'max_tokens' => 4000,
                'context_window' => 8000,
            ],
        ];
    }

    /**
     * Generate text using custom API endpoint
     *
     * @param array $messages
     * @param array $args
     * @return array
     */
    public function generate_text($messages, $args = []) {
        $settings = $this->custom_settings;

        if (empty($settings['endpoint'])) {
            return $this->build_error_response(
                __('Custom API endpoint is not configured. Please add it in the plugin settings.', 'ai-seo-writer-pro')
            );
        }

        $messages = $this->format_prompt($messages);
        if (empty($messages)) {
            return $this->build_error_response(__('No messages provided for generation.', 'ai-seo-writer-pro'));
        }

        $defaults = [
            'model' => 'custom-model',
            'temperature' => $this->settings->get_setting('temperature', 0.7),
            'max_tokens' => $this->settings->get_setting('max_generation_tokens', 4000),
        ];

        $args = wp_parse_args($args, $defaults);

        $url = $settings['endpoint'];
        $method = !empty($settings['method']) ? strtoupper($settings['method']) : 'POST';

        // Build request body
        $body = $this->build_request_body($messages, $args, $settings);

        // Parse custom headers
        $headers = $this->parse_custom_headers($settings);
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        // Add authentication
        if (!empty($settings['auth_type']) && !empty($this->api_key)) {
            switch ($settings['auth_type']) {
                case 'bearer':
                    $headers['Authorization'] = 'Bearer ' . $this->api_key;
                    break;
                case 'api-key':
                    $headers['X-API-Key'] = $this->api_key;
                    break;
                case 'basic':
                    $headers['Authorization'] = 'Basic ' . base64_encode('api:' . $this->api_key);
                    break;
            }
        }

        $request_args = [
            'method' => $method,
            'headers' => $headers,
            'timeout' => 120,
            'sslverify' => true,
        ];

        if ($method === 'GET') {
            $url = add_query_arg($body, $url);
        } else {
            $request_args['body'] = is_string($body) ? $body : wp_json_encode($body);
        }

        $prompt_tokens = $this->count_messages_tokens($messages);
        $response = $this->make_request($url, $request_args, 'custom-endpoint', $args['model'], $prompt_tokens);

        if (is_wp_error($response)) {
            return $this->build_error_response($response->get_error_message(), $args['model']);
        }

        // Extract content using configured JSON path
        $content = $this->extract_response_content($response, $settings);

        if (empty($content)) {
            return $this->build_error_response(
                __('Could not extract content from the API response. Check your response path configuration.', 'ai-seo-writer-pro'),
                $args['model']
            );
        }

        $completion_tokens = $this->estimate_tokens($content);
        $total_tokens = $prompt_tokens + $completion_tokens;

        return $this->build_success_response($content, $total_tokens, $args['model']);
    }

    /**
     * Build the request body
     *
     * @param array $messages
     * @param array $args
     * @param array $settings
     * @return array|string
     */
    private function build_request_body($messages, $args, $settings) {
        // If a body template is provided, use it with variable substitution
        if (!empty($settings['body_template'])) {
            return $this->render_body_template($settings['body_template'], $messages, $args);
        }

        // Default: combine all messages into a single prompt string
        $prompt = '';
        foreach ($messages as $msg) {
            $role = ucfirst($msg['role']);
            $prompt .= "{$role}: {$msg['content']}\n\n";
        }
        $prompt = trim($prompt);

        return [
            'prompt' => $prompt,
            'temperature' => (float) $args['temperature'],
            'max_tokens' => (int) $args['max_tokens'],
            'model' => $args['model'],
        ];
    }

    /**
     * Render body template with variable substitution
     *
     * Supported variables:
     * {{prompt}} - Combined messages as text
     * {{messages}} - Messages as JSON array
     * {{system_prompt}} - First system message content
     * {{user_prompt}} - Last user message content
     * {{temperature}} - Temperature value
     * {{max_tokens}} - Max tokens value
     * {{model}} - Model name
     *
     * @param string $template
     * @param array $messages
     * @param array $args
     * @return string
     */
    private function render_body_template($template, $messages, $args) {
        $system_prompt = '';
        $user_prompt = '';
        $prompt_parts = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $system_prompt = $msg['content'];
            } elseif ($msg['role'] === 'user') {
                $user_prompt = $msg['content'];
            }
            $prompt_parts[] = ucfirst($msg['role']) . ': ' . $msg['content'];
        }

        $combined_prompt = implode("\n\n", $prompt_parts);

        $replacements = [
            '{{prompt}}' => $combined_prompt,
            '{{messages}}' => wp_json_encode($messages),
            '{{system_prompt}}' => $system_prompt,
            '{{user_prompt}}' => $user_prompt,
            '{{temperature}}' => (float) $args['temperature'],
            '{{max_tokens}}' => (int) $args['max_tokens'],
            '{{model}}' => $args['model'],
        ];

        $rendered = strtr($template, $replacements);

        // If template looks like JSON, return as string
        $trimmed = trim($rendered);
        if (($trimmed[0] === '{' && substr($trimmed, -1) === '}') ||
            ($trimmed[0] === '[' && substr($trimmed, -1) === ']')) {
            return $rendered;
        }

        return $rendered;
    }

    /**
     * Parse custom headers from settings
     *
     * @param array $settings
     * @return array
     */
    private function parse_custom_headers($settings) {
        $headers = [];

        if (empty($settings['headers'])) {
            return $headers;
        }

        $lines = explode("\n", $settings['headers']);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, ':') === false) {
                continue;
            }
            list($key, $value) = explode(':', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * Extract content from API response using configured path
     *
     * @param mixed $response
     * @param array $settings
     * @return string
     */
    private function extract_response_content($response, $settings) {
        if (is_string($response)) {
            $decoded = json_decode($response, true);
            if ($decoded !== null) {
                $response = $decoded;
            } else {
                return $response;
            }
        }

        if (!is_array($response)) {
            return (string) $response;
        }

        // If no path configured, try common patterns
        if (empty($settings['response_path'])) {
            return $this->find_content_in_response($response);
        }

        $path = $settings['response_path'];
        return $this->get_value_by_path($response, $path);
    }

    /**
     * Get value from array by dot-notation path
     *
     * @param array $array
     * @param string $path
     * @return string
     */
    private function get_value_by_path($array, $path) {
        // Support dot notation: choices.0.message.content
        // Support bracket notation: choices[0].message.content
        $path = str_replace(['[', ']'], ['.', ''], $path);
        $keys = explode('.', $path);
        $current = $array;

        foreach ($keys as $key) {
            $key = trim($key);
            if ($key === '') {
                continue;
            }
            if (is_array($current) && isset($current[$key])) {
                $current = $current[$key];
            } elseif (is_array($current) && is_numeric($key) && isset($current[(int) $key])) {
                $current = $current[(int) $key];
            } else {
                return '';
            }
        }

        if (is_array($current)) {
            return wp_json_encode($current);
        }

        return (string) $current;
    }

    /**
     * Try to find content in response using common patterns
     *
     * @param array $response
     * @return string
     */
    private function find_content_in_response($response) {
        // OpenAI-style
        if (isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        if (isset($response['choices'][0]['text'])) {
            return $response['choices'][0]['text'];
        }

        // Gemini-style
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return $response['candidates'][0]['content']['parts'][0]['text'];
        }

        // Claude-style
        if (isset($response['content'][0]['text'])) {
            return $response['content'][0]['text'];
        }

        // Common simple patterns
        if (isset($response['content'])) {
            return is_string($response['content']) ? $response['content'] : '';
        }
        if (isset($response['text'])) {
            return $response['text'];
        }
        if (isset($response['result'])) {
            return is_string($response['result']) ? $response['result'] : '';
        }
        if (isset($response['output'])) {
            return is_string($response['output']) ? $response['output'] : '';
        }
        if (isset($response['response'])) {
            return is_string($response['response']) ? $response['response'] : '';
        }
        if (isset($response['data']['content'])) {
            return is_string($response['data']['content']) ? $response['data']['content'] : '';
        }

        // If only one key with string value, return that
        if (count($response) === 1) {
            $value = reset($response);
            if (is_string($value)) {
                return $value;
            }
        }

        return '';
    }

    /**
     * Test the API connection
     *
     * @return array
     */
    public function test_connection() {
        $settings = $this->custom_settings;

        if (empty($settings['endpoint'])) {
            return [
                'success' => false,
                'message' => __('Custom API endpoint is not configured.', 'ai-seo-writer-pro'),
            ];
        }

        $test_messages = [
            [
                'role' => 'user',
                'content' => 'Hello, please respond with "OK" only.',
            ],
        ];

        $result = $this->generate_text($test_messages, [
            'max_tokens' => 50,
            'temperature' => 0.1,
        ]);

        if (!empty($result['success'])) {
            return [
                'success' => true,
                'message' => __('Successfully connected to custom API endpoint.', 'ai-seo-writer-pro'),
                'sample' => substr($result['content'], 0, 200),
            ];
        }

        return [
            'success' => false,
            'message' => sprintf(__('Connection failed: %s', 'ai-seo-writer-pro'), $result['error']),
        ];
    }

    /**
     * Get API usage statistics
     *
     * @param array $args
     * @return array
     */
    public function get_usage($args = []) {
        global $wpdb;

        $table = $wpdb->prefix . 'ai_seo_writer_pro_api_logs';
        $defaults = [
            'user_id' => get_current_user_id(),
            'days' => 30,
        ];
        $args = wp_parse_args($args, $defaults);

        $where = $wpdb->prepare(
            "WHERE ai_provider = %s AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL %d DAY)",
            $this->provider_id,
            $args['days']
        );

        if ($args['user_id'] > 0) {
            $where .= $wpdb->prepare(" AND user_id = %d", $args['user_id']);
        }

        $sql = "SELECT
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN is_error = 1 THEN 1 ELSE 0 END) as failed_requests,
                    SUM(prompt_tokens) as total_prompt_tokens,
                    SUM(completion_tokens) as total_completion_tokens,
                    SUM(total_tokens) as total_tokens_used,
                    AVG(response_time) as avg_response_time
                FROM $table $where";

        $results = $wpdb->get_row($sql, ARRAY_A);

        return [
            'total_requests' => (int) ($results['total_requests'] ?? 0),
            'failed_requests' => (int) ($results['failed_requests'] ?? 0),
            'total_prompt_tokens' => (int) ($results['total_prompt_tokens'] ?? 0),
            'total_completion_tokens' => (int) ($results['total_completion_tokens'] ?? 0),
            'total_tokens_used' => (int) ($results['total_tokens_used'] ?? 0),
            'avg_response_time' => (float) ($results['avg_response_time'] ?? 0),
            'success_rate' => $results['total_requests'] > 0
                ? round((($results['total_requests'] - $results['failed_requests']) / $results['total_requests']) * 100, 2)
                : 0,
        ];
    }

    /**
     * Count tokens in messages array
     *
     * @param array $messages
     * @return int
     */
    public function count_messages_tokens($messages) {
        $total = 0;
        foreach ($messages as $message) {
            if (isset($message['content'])) {
                $total += $this->estimate_tokens($message['content']);
            }
        }
        return $total;
    }

    /**
     * Get current custom settings
     *
     * @return array
     */
    public function get_custom_settings() {
        return $this->custom_settings;
    }
}

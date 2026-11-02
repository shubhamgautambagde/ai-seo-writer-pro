<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Claude Provider - Integration with Anthropic Claude API
 */
class Claude_Provider extends AI_Provider_Base {

    /**
     * Provider identifier
     * @var string
     */
    protected $provider_id = 'claude';

    /**
     * Provider display name
     * @var string
     */
    protected $provider_name = 'Anthropic Claude';

    /**
     * API base URL
     * @var string
     */
    private $api_base = 'https://api.anthropic.com/v1';

    /**
     * API version header
     * @var string
     */
    private $api_version = '2023-06-01';

    /**
     * Get available models
     *
     * @return array
     */
    public function get_models() {
        return [
            'claude-3-5-sonnet-20240620' => [
                'id' => 'claude-3-5-sonnet-20240620',
                'name' => __('Claude 3.5 Sonnet', 'ai-seo-writer-pro'),
                'description' => __('Most intelligent model, strong at complex tasks', 'ai-seo-writer-pro'),
                'max_tokens' => 8192,
                'context_window' => 200000,
            ],
            'claude-3-opus-20240229' => [
                'id' => 'claude-3-opus-20240229',
                'name' => __('Claude 3 Opus', 'ai-seo-writer-pro'),
                'description' => __('Highest performance for complex reasoning', 'ai-seo-writer-pro'),
                'max_tokens' => 4096,
                'context_window' => 200000,
            ],
            'claude-3-haiku-20240307' => [
                'id' => 'claude-3-haiku-20240307',
                'name' => __('Claude 3 Haiku', 'ai-seo-writer-pro'),
                'description' => __('Fastest and most compact model', 'ai-seo-writer-pro'),
                'max_tokens' => 4096,
                'context_window' => 200000,
            ],
        ];
    }

    /**
     * Generate text using Anthropic Messages API
     *
     * @param array $messages
     * @param array $args
     * @return array
     */
    public function generate_text($messages, $args = []) {
        if (empty($this->api_key)) {
            return $this->build_error_response(
                __('Anthropic Claude API key is not configured. Please add it in the plugin settings.', 'ai-seo-writer-pro')
            );
        }

        $messages = $this->format_prompt($messages);
        if (empty($messages)) {
            return $this->build_error_response(__('No messages provided for generation.', 'ai-seo-writer-pro'));
        }

        $defaults = [
            'model' => 'claude-3-5-sonnet-20240620',
            'temperature' => $this->settings->get_setting('temperature', 0.7),
            'max_tokens' => $this->settings->get_setting('max_generation_tokens', 4000),
            'top_p' => $this->settings->get_setting('top_p', 1.0),
        ];

        $args = wp_parse_args($args, $defaults);

        // Extract system message if present
        $system_prompt = '';
        $api_messages = [];
        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $system_prompt = $msg['content'];
            } else {
                $api_messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                ];
            }
        }

        // Ensure first message is from user
        if (!empty($api_messages) && $api_messages[0]['role'] !== 'user') {
            array_unshift($api_messages, [
                'role' => 'user',
                'content' => ' ',
            ]);
        }

        $url = $this->api_base . '/messages';

        $body = [
            'model' => $args['model'],
            'messages' => $api_messages,
            'max_tokens' => (int) $args['max_tokens'],
            'temperature' => (float) $args['temperature'],
            'top_p' => (float) $args['top_p'],
        ];

        if (!empty($system_prompt)) {
            $body['system'] = $system_prompt;
        }

        $request_args = [
            'method' => 'POST',
            'headers' => [
                'x-api-key' => $this->api_key,
                'anthropic-version' => $this->api_version,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 120,
            'sslverify' => true,
        ];

        $prompt_tokens = $this->count_messages_tokens($messages);
        $response = $this->make_request($url, $request_args, '/messages', $args['model'], $prompt_tokens);

        if (is_wp_error($response)) {
            return $this->build_error_response($response->get_error_message(), $args['model']);
        }

        if (!isset($response['content'][0]['text'])) {
            if (isset($response['type']) && $response['type'] === 'error') {
                $error_msg = $response['error']['message'] ?? __('Unknown API error.', 'ai-seo-writer-pro');
                return $this->build_error_response($error_msg, $args['model']);
            }
            return $this->build_error_response(
                __('Unexpected API response format: missing content.', 'ai-seo-writer-pro'),
                $args['model']
            );
        }

        $content = '';
        foreach ($response['content'] as $block) {
            if (isset($block['text'])) {
                $content .= $block['text'];
            }
        }

        $input_tokens = $response['usage']['input_tokens'] ?? $prompt_tokens;
        $output_tokens = $response['usage']['output_tokens'] ?? $this->estimate_tokens($content);
        $total_tokens = $input_tokens + $output_tokens;

        return $this->build_success_response($content, $total_tokens, $args['model']);
    }

    /**
     * Test the API connection
     *
     * @return array
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'message' => __('Anthropic Claude API key is not configured.', 'ai-seo-writer-pro'),
            ];
        }

        $url = $this->api_base . '/messages';

        $body = [
            'model' => 'claude-3-haiku-20240307',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello, respond with "OK" only.',
                ],
            ],
            'max_tokens' => 10,
        ];

        $request_args = [
            'method' => 'POST',
            'headers' => [
                'x-api-key' => $this->api_key,
                'anthropic-version' => $this->api_version,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 15,
            'sslverify' => true,
        ];

        $response = $this->make_request($url, $request_args, '/messages', 'claude-3-haiku-20240307');

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => sprintf(__('Connection failed: %s', 'ai-seo-writer-pro'), $response->get_error_message()),
            ];
        }

        return [
            'success' => true,
            'message' => __('Successfully connected to Anthropic Claude API.', 'ai-seo-writer-pro'),
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
                $total += 4; // Role overhead
            }
        }
        return $total;
    }
}

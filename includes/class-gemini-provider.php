<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gemini Provider - Integration with Google Gemini API
 */
class Gemini_Provider extends AI_Provider_Base {

    /**
     * Provider identifier
     * @var string
     */
    protected $provider_id = 'gemini';

    /**
     * Provider display name
     * @var string
     */
    protected $provider_name = 'Google Gemini';

    /**
     * API base URL
     * @var string
     */
    private $api_base = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * Get available models
     *
     * @return array
     */
    public function get_models() {
        return [
            'gemini-1.5-pro' => [
                'id' => 'gemini-1.5-pro',
                'name' => __('Gemini 1.5 Pro', 'ai-seo-writer-pro'),
                'description' => __('Highly capable for complex tasks, large context', 'ai-seo-writer-pro'),
                'max_tokens' => 8192,
                'context_window' => 1048576,
            ],
            'gemini-1.5-flash' => [
                'id' => 'gemini-1.5-flash',
                'name' => __('Gemini 1.5 Flash', 'ai-seo-writer-pro'),
                'description' => __('Fast and efficient for everyday tasks', 'ai-seo-writer-pro'),
                'max_tokens' => 8192,
                'context_window' => 1048576,
            ],
            'gemini-1.0-pro' => [
                'id' => 'gemini-1.0-pro',
                'name' => __('Gemini 1.0 Pro', 'ai-seo-writer-pro'),
                'description' => __('Reliable performance for text generation', 'ai-seo-writer-pro'),
                'max_tokens' => 2048,
                'context_window' => 30720,
            ],
        ];
    }

    /**
     * Generate text using Google Gemini API
     *
     * @param array $messages
     * @param array $args
     * @return array
     */
    public function generate_text($messages, $args = []) {
        if (empty($this->api_key)) {
            return $this->build_error_response(
                __('Google Gemini API key is not configured. Please add it in the plugin settings.', 'ai-seo-writer-pro')
            );
        }

        $messages = $this->format_prompt($messages);
        if (empty($messages)) {
            return $this->build_error_response(__('No messages provided for generation.', 'ai-seo-writer-pro'));
        }

        $defaults = [
            'model' => 'gemini-1.5-pro',
            'temperature' => $this->settings->get_setting('temperature', 0.7),
            'max_tokens' => $this->settings->get_setting('max_generation_tokens', 4000),
            'top_p' => $this->settings->get_setting('top_p', 1.0),
        ];

        $args = wp_parse_args($args, $defaults);

        $contents = $this->convert_to_gemini_contents($messages);
        $system_instruction = $this->extract_system_instruction($messages);

        $url = sprintf(
            '%s/models/%s:generateContent?key=%s',
            $this->api_base,
            $args['model'],
            $this->api_key
        );

        $body = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => (float) $args['temperature'],
                'maxOutputTokens' => (int) $args['max_tokens'],
                'topP' => (float) $args['top_p'],
            ],
        ];

        if (!empty($system_instruction)) {
            $body['systemInstruction'] = [
                'parts' => [
                    ['text' => $system_instruction],
                ],
            ];
        }

        $request_args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 120,
            'sslverify' => true,
        ];

        $prompt_tokens = $this->count_messages_tokens($messages);
        $response = $this->make_request($url, $request_args, ':generateContent', $args['model'], $prompt_tokens);

        if (is_wp_error($response)) {
            return $this->build_error_response($response->get_error_message(), $args['model']);
        }

        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            if (isset($response['candidates'][0]['finishReason']) && $response['candidates'][0]['finishReason'] === 'SAFETY') {
                return $this->build_error_response(
                    __('Content was blocked due to safety settings.', 'ai-seo-writer-pro'),
                    $args['model']
                );
            }
            return $this->build_error_response(
                __('Unexpected API response format: missing content.', 'ai-seo-writer-pro'),
                $args['model']
            );
        }

        $content = '';
        foreach ($response['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['text'])) {
                $content .= $part['text'];
            }
        }

        $prompt_tokens_used = $response['usageMetadata']['promptTokenCount'] ?? $prompt_tokens;
        $completion_tokens = $response['usageMetadata']['candidatesTokenCount'] ?? $this->estimate_tokens($content);
        $total_tokens = $response['usageMetadata']['totalTokenCount'] ?? ($prompt_tokens_used + $completion_tokens);

        return $this->build_success_response($content, $total_tokens, $args['model']);
    }

    /**
     * Convert messages array to Gemini contents format
     *
     * @param array $messages
     * @return array
     */
    private function convert_to_gemini_contents($messages) {
        $contents = [];
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                continue; // System handled separately
            }
            $role = ($message['role'] === 'assistant') ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $message['content']],
                ],
            ];
        }
        return $contents;
    }

    /**
     * Extract system instruction from messages
     *
     * @param array $messages
     * @return string
     */
    private function extract_system_instruction($messages) {
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                return $message['content'];
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
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'message' => __('Google Gemini API key is not configured.', 'ai-seo-writer-pro'),
            ];
        }

        $url = sprintf(
            '%s/models/gemini-1.5-flash:generateContent?key=%s',
            $this->api_base,
            $this->api_key
        );

        $body = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => 'Hello, respond with "OK" only.'],
                    ],
                ],
            ],
            'generationConfig' => [
                'maxOutputTokens' => 10,
            ],
        ];

        $request_args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 15,
            'sslverify' => true,
        ];

        $response = $this->make_request($url, $request_args, ':generateContent', 'gemini-1.5-flash');

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => sprintf(__('Connection failed: %s', 'ai-seo-writer-pro'), $response->get_error_message()),
            ];
        }

        return [
            'success' => true,
            'message' => __('Successfully connected to Google Gemini API.', 'ai-seo-writer-pro'),
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
}

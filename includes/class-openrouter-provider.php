<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * OpenRouter Provider - Integration with OpenRouter API
 */
class OpenRouter_Provider extends AI_Provider_Base {

    /**
     * Provider identifier
     * @var string
     */
    protected $provider_id = 'openrouter';

    /**
     * Provider display name
     * @var string
     */
    protected $provider_name = 'OpenRouter';

    /**
     * API base URL
     * @var string
     */
    private $api_base = 'https://openrouter.ai/api/v1';

    /**
     * Get available models
     *
     * @return array
     */
    public function get_models() {
        return [
            'openai/gpt-4o' => [
                'id' => 'openai/gpt-4o',
                'name' => __('OpenAI: GPT-4o', 'ai-seo-writer-pro'),
                'description' => __('Most capable GPT model via OpenRouter', 'ai-seo-writer-pro'),
                'max_tokens' => 128000,
                'context_window' => 128000,
            ],
            'openai/gpt-4o-mini' => [
                'id' => 'openai/gpt-4o-mini',
                'name' => __('OpenAI: GPT-4o Mini', 'ai-seo-writer-pro'),
                'description' => __('Fast and affordable GPT model', 'ai-seo-writer-pro'),
                'max_tokens' => 128000,
                'context_window' => 128000,
            ],
            'anthropic/claude-3.5-sonnet' => [
                'id' => 'anthropic/claude-3.5-sonnet',
                'name' => __('Anthropic: Claude 3.5 Sonnet', 'ai-seo-writer-pro'),
                'description' => __('Highly capable Claude model', 'ai-seo-writer-pro'),
                'max_tokens' => 8192,
                'context_window' => 200000,
            ],
            'anthropic/claude-3-opus' => [
                'id' => 'anthropic/claude-3-opus',
                'name' => __('Anthropic: Claude 3 Opus', 'ai-seo-writer-pro'),
                'description' => __('Highest performance Claude model', 'ai-seo-writer-pro'),
                'max_tokens' => 4096,
                'context_window' => 200000,
            ],
            'anthropic/claude-3-haiku' => [
                'id' => 'anthropic/claude-3-haiku',
                'name' => __('Anthropic: Claude 3 Haiku', 'ai-seo-writer-pro'),
                'description' => __('Fastest Claude model', 'ai-seo-writer-pro'),
                'max_tokens' => 4096,
                'context_window' => 200000,
            ],
            'google/gemini-pro-1.5' => [
                'id' => 'google/gemini-pro-1.5',
                'name' => __('Google: Gemini Pro 1.5', 'ai-seo-writer-pro'),
                'description' => __('Gemini Pro via OpenRouter', 'ai-seo-writer-pro'),
                'max_tokens' => 8192,
                'context_window' => 1048576,
            ],
            'google/gemini-flash-1.5' => [
                'id' => 'google/gemini-flash-1.5',
                'name' => __('Google: Gemini Flash 1.5', 'ai-seo-writer-pro'),
                'description' => __('Fast Gemini model via OpenRouter', 'ai-seo-writer-pro'),
                'max_tokens' => 8192,
                'context_window' => 1048576,
            ],
            'meta-llama/llama-3.1-405b-instruct' => [
                'id' => 'meta-llama/llama-3.1-405b-instruct',
                'name' => __('Meta: Llama 3.1 405B', 'ai-seo-writer-pro'),
                'description' => __('Large open source model', 'ai-seo-writer-pro'),
                'max_tokens' => 4096,
                'context_window' => 131072,
            ],
            'meta-llama/llama-3.1-70b-instruct' => [
                'id' => 'meta-llama/llama-3.1-70b-instruct',
                'name' => __('Meta: Llama 3.1 70B', 'ai-seo-writer-pro'),
                'description' => __('Balanced open source model', 'ai-seo-writer-pro'),
                'max_tokens' => 4096,
                'context_window' => 131072,
            ],
            'mistralai/mistral-large' => [
                'id' => 'mistralai/mistral-large',
                'name' => __('Mistral: Large', 'ai-seo-writer-pro'),
                'description' => __('Mistral large model', 'ai-seo-writer-pro'),
                'max_tokens' => 4096,
                'context_window' => 131072,
            ],
        ];
    }

    /**
     * Generate text using OpenRouter API
     *
     * @param array $messages
     * @param array $args
     * @return array
     */
    public function generate_text($messages, $args = []) {
        if (empty($this->api_key)) {
            return $this->build_error_response(
                __('OpenRouter API key is not configured. Please add it in the plugin settings.', 'ai-seo-writer-pro')
            );
        }

        $messages = $this->format_prompt($messages);
        if (empty($messages)) {
            return $this->build_error_response(__('No messages provided for generation.', 'ai-seo-writer-pro'));
        }

        $defaults = [
            'model' => 'openai/gpt-4o',
            'temperature' => $this->settings->get_setting('temperature', 0.7),
            'max_tokens' => $this->settings->get_setting('max_generation_tokens', 4000),
            'top_p' => $this->settings->get_setting('top_p', 1.0),
        ];

        $args = wp_parse_args($args, $defaults);

        $url = $this->api_base . '/chat/completions';

        $body = [
            'model' => $args['model'],
            'messages' => $messages,
            'temperature' => (float) $args['temperature'],
            'max_tokens' => (int) $args['max_tokens'],
            'top_p' => (float) $args['top_p'],
        ];

        $site_url = home_url();
        $site_name = get_bloginfo('name');

        $request_args = [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => $site_url,
                'X-Title' => $site_name,
            ],
            'body' => wp_json_encode($body),
            'timeout' => 120,
            'sslverify' => true,
        ];

        $prompt_tokens = $this->count_messages_tokens($messages);
        $response = $this->make_request($url, $request_args, '/chat/completions', $args['model'], $prompt_tokens);

        if (is_wp_error($response)) {
            return $this->build_error_response($response->get_error_message(), $args['model']);
        }

        if (!isset($response['choices'][0]['message']['content'])) {
            return $this->build_error_response(
                __('Unexpected API response format: missing content.', 'ai-seo-writer-pro'),
                $args['model']
            );
        }

        $content = $response['choices'][0]['message']['content'];
        $prompt_tokens_used = $response['usage']['prompt_tokens'] ?? $prompt_tokens;
        $completion_tokens = $response['usage']['completion_tokens'] ?? $this->estimate_tokens($content);
        $total_tokens = $response['usage']['total_tokens'] ?? ($prompt_tokens_used + $completion_tokens);

        // Track cost if available
        $cost_data = [];
        if (isset($response['cost'])) {
            $cost_data['cost'] = $response['cost'];
        }
        if (isset($response['usage'])) {
            $cost_data['usage_details'] = $response['usage'];
        }

        $result = $this->build_success_response($content, $total_tokens, $args['model']);
        if (!empty($cost_data)) {
            $result['cost_data'] = $cost_data;
        }

        return $result;
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
                'message' => __('OpenRouter API key is not configured.', 'ai-seo-writer-pro'),
            ];
        }

        $url = $this->api_base . '/models';

        $request_args = [
            'method' => 'GET',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 15,
            'sslverify' => true,
        ];

        $response = $this->make_request($url, $request_args, '/models', 'test');

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => sprintf(__('Connection failed: %s', 'ai-seo-writer-pro'), $response->get_error_message()),
            ];
        }

        return [
            'success' => true,
            'message' => __('Successfully connected to OpenRouter API.', 'ai-seo-writer-pro'),
        ];
    }

    /**
     * Get API usage statistics including cost tracking
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

        // Estimate cost based on average OpenRouter pricing (rough estimate)
        $prompt_tokens = (int) ($results['total_prompt_tokens'] ?? 0);
        $completion_tokens = (int) ($results['total_completion_tokens'] ?? 0);
        $estimated_cost = ($prompt_tokens * 0.0000015) + ($completion_tokens * 0.000006);

        return [
            'total_requests' => (int) ($results['total_requests'] ?? 0),
            'failed_requests' => (int) ($results['failed_requests'] ?? 0),
            'total_prompt_tokens' => $prompt_tokens,
            'total_completion_tokens' => $completion_tokens,
            'total_tokens_used' => (int) ($results['total_tokens_used'] ?? 0),
            'avg_response_time' => (float) ($results['avg_response_time'] ?? 0),
            'estimated_cost_usd' => round($estimated_cost, 4),
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
                $total += 4;
            }
        }
        $total += 2;
        return $total;
    }

    /**
     * Fetch available models from OpenRouter API
     *
     * @return array|\WP_Error
     */
    public function fetch_remote_models() {
        if (empty($this->api_key)) {
            return new \WP_Error('no_api_key', __('API key not configured.', 'ai-seo-writer-pro'));
        }

        $url = $this->api_base . '/models';

        $request_args = [
            'method' => 'GET',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 15,
            'sslverify' => true,
        ];

        $response = $this->make_request($url, $request_args, '/models', 'list');

        if (is_wp_error($response)) {
            return $response;
        }

        $models = [];
        if (isset($response['data']) && is_array($response['data'])) {
            foreach ($response['data'] as $model) {
                $models[] = [
                    'id' => $model['id'] ?? '',
                    'name' => $model['name'] ?? $model['id'] ?? '',
                    'description' => $model['description'] ?? '',
                    'context_length' => $model['context_length'] ?? 0,
                    'pricing' => $model['pricing'] ?? [],
                ];
            }
        }

        return $models;
    }
}

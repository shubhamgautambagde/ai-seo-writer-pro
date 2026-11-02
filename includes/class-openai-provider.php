<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * OpenAI Provider - Integration with OpenAI API
 */
class OpenAI_Provider extends AI_Provider_Base {

    /**
     * Provider identifier
     * @var string
     */
    protected $provider_id = 'openai';

    /**
     * Provider display name
     * @var string
     */
    protected $provider_name = 'OpenAI';

    /**
     * API base URL
     * @var string
     */
    private $api_base = 'https://api.openai.com/v1';

    /**
     * Get available models
     *
     * @return array
     */
    public function get_models() {
        return [
            'gpt-4o' => [
                'id' => 'gpt-4o',
                'name' => __('GPT-4o', 'ai-seo-writer-pro'),
                'description' => __('Most capable model, high intelligence, fast', 'ai-seo-writer-pro'),
                'max_tokens' => 128000,
                'context_window' => 128000,
            ],
            'gpt-4o-mini' => [
                'id' => 'gpt-4o-mini',
                'name' => __('GPT-4o Mini', 'ai-seo-writer-pro'),
                'description' => __('Fast and affordable for most tasks', 'ai-seo-writer-pro'),
                'max_tokens' => 128000,
                'context_window' => 128000,
            ],
            'gpt-4-turbo' => [
                'id' => 'gpt-4-turbo',
                'name' => __('GPT-4 Turbo', 'ai-seo-writer-pro'),
                'description' => __('High intelligence with large context', 'ai-seo-writer-pro'),
                'max_tokens' => 128000,
                'context_window' => 128000,
            ],
            'gpt-3.5-turbo' => [
                'id' => 'gpt-3.5-turbo',
                'name' => __('GPT-3.5 Turbo', 'ai-seo-writer-pro'),
                'description' => __('Cost-effective, good for simpler tasks', 'ai-seo-writer-pro'),
                'max_tokens' => 16384,
                'context_window' => 16384,
            ],
        ];
    }

    /**
     * Generate text using OpenAI Chat Completions API
     *
     * @param array $messages
     * @param array $args
     * @return array
     */
    public function generate_text($messages, $args = []) {
        if (empty($this->api_key)) {
            return $this->build_error_response(
                __('OpenAI API key is not configured. Please add it in the plugin settings.', 'ai-seo-writer-pro')
            );
        }

        $messages = $this->format_prompt($messages);
        if (empty($messages)) {
            return $this->build_error_response(__('No messages provided for generation.', 'ai-seo-writer-pro'));
        }

        $defaults = [
            'model' => $this->settings->get_setting('default_model', 'gpt-4o'),
            'temperature' => $this->settings->get_setting('temperature', 0.7),
            'max_tokens' => $this->settings->get_setting('max_generation_tokens', 4000),
            'top_p' => $this->settings->get_setting('top_p', 1.0),
            'frequency_penalty' => $this->settings->get_setting('frequency_penalty', 0.0),
            'presence_penalty' => $this->settings->get_setting('presence_penalty', 0.0),
            'stream' => false,
        ];

        $args = wp_parse_args($args, $defaults);

        $url = $this->api_base . '/chat/completions';

        $body = [
            'model' => $args['model'],
            'messages' => $messages,
            'temperature' => (float) $args['temperature'],
            'max_tokens' => (int) $args['max_tokens'],
            'top_p' => (float) $args['top_p'],
            'frequency_penalty' => (float) $args['frequency_penalty'],
            'presence_penalty' => (float) $args['presence_penalty'],
        ];

        if (!empty($args['stream']) && is_callable($args['stream'])) {
            return $this->generate_streaming($url, $body, $args);
        }

        $request_args = [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
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

        return $this->build_success_response($content, $total_tokens, $args['model']);
    }

    /**
     * Generate streaming response
     *
     * @param string $url
     * @param array $body
     * @param array $args
     * @return array
     */
    private function generate_streaming($url, $body, $args) {
        $body['stream'] = true;
        $callback = $args['stream'];
        $full_content = '';

        $request_args = [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 300,
            'sslverify' => true,
            'stream' => true,
            'filename' => null,
        ];

        $prompt_tokens = $this->count_messages_tokens($body['messages']);

        add_action('http_api_curl', function ($handle) use ($callback, &$full_content) {
            curl_setopt($handle, CURLOPT_WRITEFUNCTION, function ($ch, $data) use ($callback, &$full_content) {
                $lines = explode("\n", $data);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strpos($line, 'data: ') === 0) {
                        $json = substr($line, 6);
                        if ($json === '[DONE]') {
                            continue;
                        }
                        $decoded = json_decode($json, true);
                        if (isset($decoded['choices'][0]['delta']['content'])) {
                            $chunk = $decoded['choices'][0]['delta']['content'];
                            $full_content .= $chunk;
                            call_user_func($callback, $chunk);
                        }
                    }
                }
                return strlen($data);
            });
        });

        $start_time = microtime(true);
        $response = wp_remote_request($url, $request_args);
        $response_time = microtime(true) - $start_time;

        if (is_wp_error($response)) {
            $this->log_api_call([
                'ai_model' => $args['model'],
                'endpoint' => '/chat/completions',
                'prompt_tokens' => $prompt_tokens,
                'response_time' => $response_time,
                'is_error' => 1,
                'error_message' => $response->get_error_message(),
            ]);
            return $this->build_error_response($response->get_error_message(), $args['model']);
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $completion_tokens = $this->estimate_tokens($full_content);
        $total_tokens = $prompt_tokens + $completion_tokens;

        $this->log_api_call([
            'ai_model' => $args['model'],
            'endpoint' => '/chat/completions',
            'prompt_tokens' => $prompt_tokens,
            'completion_tokens' => $completion_tokens,
            'total_tokens' => $total_tokens,
            'response_time' => $response_time,
            'http_status' => $response_code,
            'is_error' => ($response_code >= 400) ? 1 : 0,
        ]);

        if ($response_code >= 400) {
            return $this->build_error_response(
                sprintf(__('Streaming request failed with status %d.', 'ai-seo-writer-pro'), $response_code),
                $args['model']
            );
        }

        return $this->build_success_response($full_content, $total_tokens, $args['model']);
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
                'message' => __('OpenAI API key is not configured.', 'ai-seo-writer-pro'),
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
            'message' => __('Successfully connected to OpenAI API.', 'ai-seo-writer-pro'),
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
                // Add overhead for role and formatting
                $total += 4;
            }
        }
        // Add base overhead for messages array
        $total += 2;
        return $total;
    }

    /**
     * Count tokens in a text string
     *
     * @param string $text
     * @return int
     */
    public function count_tokens($text) {
        return $this->estimate_tokens($text);
    }
}

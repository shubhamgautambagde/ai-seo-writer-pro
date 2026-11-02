<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Image Generator - Handles AI image generation and media library integration
 */
class Image_Generator {

    /**
     * AI Providers instance
     * @var AI_Providers
     */
    private $ai_providers;

    /**
     * Security Manager instance
     * @var Security_Manager
     */
    private $security_manager;

    /**
     * Settings Manager instance
     * @var Settings_Manager
     */
    private $settings_manager;

    /**
     * Available image providers
     * @var array
     */
    private $providers = [
        'dall-e' => [
            'name' => 'DALL-E (OpenAI)',
            'sizes' => ['256x256', '512x512', '1024x1024', '1792x1024', '1024x1792'],
            'default_size' => '1024x1024',
            'max_images' => 10,
            'qualities' => ['standard', 'hd'],
            'styles' => ['vivid', 'natural'],
        ],
        'stable-diffusion' => [
            'name' => 'Stable Diffusion',
            'sizes' => ['512x512', '768x768', '1024x1024', '1024x768', '768x1024'],
            'default_size' => '1024x1024',
            'max_images' => 4,
            'qualities' => ['standard', 'high'],
            'styles' => ['photographic', 'digital-art', 'anime', 'cinematic', '3d-render'],
        ],
        'external-api' => [
            'name' => 'External API',
            'sizes' => ['512x512', '768x768', '1024x1024'],
            'default_size' => '1024x1024',
            'max_images' => 4,
            'qualities' => ['standard'],
            'styles' => ['default'],
        ],
    ];

    /**
     * Constructor
     * @param AI_Providers $ai_providers
     */
    public function __construct($ai_providers) {
        $this->ai_providers = $ai_providers;
        $this->security_manager = new Security_Manager();
        $this->settings_manager = new Settings_Manager();
    }

    /**
     * Initialize hooks and AJAX handlers
     */
    public function init() {
        add_action('wp_ajax_ai_seo_writer_pro_generate_image', [$this, 'ajax_generate_image']);
        add_action('wp_ajax_ai_seo_writer_pro_generate_image_prompt', [$this, 'ajax_generate_image_prompt']);
        add_action('wp_ajax_ai_seo_writer_pro_upload_image_to_library', [$this, 'ajax_upload_image_to_library']);
        add_action('wp_ajax_ai_seo_writer_pro_set_image_alt_text', [$this, 'ajax_set_image_alt_text']);
        add_action('wp_ajax_ai_seo_writer_pro_get_image_providers', [$this, 'ajax_get_available_providers']);
        add_action('wp_ajax_ai_seo_writer_pro_test_image_connection', [$this, 'ajax_test_connection']);
    }

    /**
     * Generate an image using AI
     *
     * @param string $prompt Text description of the desired image
     * @param array  $params {
     *     @type string $provider         Image provider: 'dall-e', 'stable-diffusion', 'external-api'
     *     @type string $size             Image size, e.g. '1024x1024'
     *     @type string $quality          Quality: 'standard', 'hd', 'high'
     *     @type string $style            Style preset
     *     @type int    $number_of_images Number of images to generate (1-10)
     * }
     * @return array|\WP_Error Array of image URLs or WP_Error
     */
    public function generate_image($prompt, $params = []) {
        $settings = $this->settings_manager->get_settings();

        $defaults = [
            'provider'         => $settings['image_provider'] ?? 'dall-e',
            'size'             => '1024x1024',
            'quality'          => 'standard',
            'style'            => '',
            'number_of_images' => 1,
        ];

        $params = wp_parse_args($params, $defaults);

        // Validate provider
        if (!isset($this->providers[$params['provider']])) {
            return new \WP_Error(
                'invalid_provider',
                sprintf(__('Invalid image provider: %s', 'ai-seo-writer-pro'), $params['provider'])
            );
        }

        $provider_config = $this->providers[$params['provider']];

        // Validate and sanitize parameters
        $prompt = $this->sanitize_prompt($prompt);
        if (empty($prompt)) {
            return new \WP_Error(
                'empty_prompt',
                __('Image prompt cannot be empty.', 'ai-seo-writer-pro')
            );
        }

        // Validate size
        if (!in_array($params['size'], $provider_config['sizes'], true)) {
            $params['size'] = $provider_config['default_size'];
        }

        // Validate number of images
        $params['number_of_images'] = min(
            max(1, absint($params['number_of_images'])),
            $provider_config['max_images']
        );

        // Validate quality
        if (!in_array($params['quality'], $provider_config['qualities'], true)) {
            $params['quality'] = $provider_config['qualities'][0];
        }

        // Rate limit check
        if (!$this->security_manager->check_rate_limit('image_generation', 5, 60)) {
            return new \WP_Error(
                'rate_limit_exceeded',
                __('Image generation rate limit exceeded. Please try again later.', 'ai-seo-writer-pro')
            );
        }

        // Dispatch to provider-specific method
        switch ($params['provider']) {
            case 'dall-e':
                return $this->generate_with_dalle($prompt, $params);

            case 'stable-diffusion':
                return $this->generate_with_stable_diffusion($prompt, $params);

            case 'external-api':
                return $this->generate_with_external_api($prompt, $params);

            default:
                return new \WP_Error(
                    'unsupported_provider',
                    __('Unsupported image provider.', 'ai-seo-writer-pro')
                );
        }
    }

    /**
     * Generate image using DALL-E (OpenAI)
     *
     * @param string $prompt
     * @param array  $params
     * @return array|\WP_Error
     */
    private function generate_with_dalle($prompt, $params) {
        $api_key = $this->settings_manager->get_api_key('openai');

        if (empty($api_key)) {
            return new \WP_Error(
                'api_key_missing',
                __('OpenAI API key is not configured.', 'ai-seo-writer-pro')
            );
        }

        // DALL-E 3 supports max 1 image per request for certain sizes
        $n = $params['number_of_images'];
        $size = $params['size'];

        // DALL-E 3 only supports 1 image per request
        $n = min($n, 1);

        $body = [
            'model'   => 'dall-e-3',
            'prompt'  => $prompt,
            'n'       => $n,
            'size'    => $size,
            'quality' => $params['quality'],
        ];

        if (!empty($params['style']) && in_array($params['style'], ['vivid', 'natural'], true)) {
            $body['style'] = $params['style'];
        }

        $response = wp_remote_post('https://api.openai.com/v1/images/generations', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode($body),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        if ($response_code !== 200) {
            $error_message = $response_body['error']['message'] ?? __('Unknown API error.', 'ai-seo-writer-pro');
            return new \WP_Error('api_error', $error_message);
        }

        $images = [];
        if (!empty($response_body['data']) && is_array($response_body['data'])) {
            foreach ($response_body['data'] as $item) {
                $images[] = [
                    'url'           => $item['url'] ?? '',
                    'revised_prompt' => $item['revised_prompt'] ?? $prompt,
                ];
            }
        }

        if (empty($images)) {
            return new \WP_Error(
                'no_images_returned',
                __('No images were returned by the API.', 'ai-seo-writer-pro')
            );
        }

        return $images;
    }

    /**
     * Generate image using Stable Diffusion-style API
     *
     * @param string $prompt
     * @param array  $params
     * @return array|\WP_Error
     */
    private function generate_with_stable_diffusion($prompt, $params) {
        $api_key = $this->settings_manager->get_api_key('stable-diffusion');
        $api_url = $this->settings_manager->get_setting('stable_diffusion_endpoint', '');

        if (empty($api_url)) {
            return new \WP_Error(
                'endpoint_missing',
                __('Stable Diffusion API endpoint is not configured.', 'ai-seo-writer-pro')
            );
        }

        list($width, $height) = explode('x', $params['size']);

        $body = [
            'prompt'            => $prompt,
            'width'             => absint($width),
            'height'            => absint($height),
            'num_images'        => $params['number_of_images'],
            'steps'             => 30,
            'cfg_scale'         => 7.5,
            'sampler'           => 'DPM++ 2M Karras',
        ];

        if (!empty($params['style'])) {
            $body['style_preset'] = $params['style'];
        }

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (!empty($api_key)) {
            $headers['Authorization'] = 'Bearer ' . $api_key;
        }

        $response = wp_remote_post($api_url, [
            'headers' => $headers,
            'body'    => wp_json_encode($body),
            'timeout' => 120,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        if ($response_code !== 200) {
            $error_message = $response_body['error'] ?? $response_body['message'] ?? __('Unknown API error.', 'ai-seo-writer-pro');
            return new \WP_Error('api_error', $error_message);
        }

        $images = [];

        // Handle different response formats
        if (!empty($response_body['images']) && is_array($response_body['images'])) {
            foreach ($response_body['images'] as $img_data) {
                if (is_string($img_data) && strpos($img_data, 'http') === 0) {
                    $images[] = ['url' => $img_data];
                } elseif (is_array($img_data) && !empty($img_data['url'])) {
                    $images[] = ['url' => $img_data['url']];
                } elseif (is_string($img_data) && base64_decode($img_data, true)) {
                    // Base64 encoded image - upload and return media URL
                    $uploaded = $this->upload_base64_image($img_data);
                    if (!is_wp_error($uploaded)) {
                        $images[] = ['url' => $uploaded];
                    }
                }
            }
        } elseif (!empty($response_body['output']) && is_array($response_body['output'])) {
            foreach ($response_body['output'] as $url) {
                $images[] = ['url' => $url];
            }
        }

        if (empty($images)) {
            return new \WP_Error(
                'no_images_returned',
                __('No images were returned by the API.', 'ai-seo-writer-pro')
            );
        }

        return $images;
    }

    /**
     * Generate image using configurable external API
     *
     * @param string $prompt
     * @param array  $params
     * @return array|\WP_Error
     */
    private function generate_with_external_api($prompt, $params) {
        $custom_settings = $this->settings_manager->get_custom_api_settings();

        if (empty($custom_settings['endpoint'])) {
            return new \WP_Error(
                'endpoint_missing',
                __('External API endpoint is not configured.', 'ai-seo-writer-pro')
            );
        }

        $api_key = $this->settings_manager->get_api_key('external-api');

        // Build request body from template
        $body_template = $custom_settings['body_template'] ?: '{"prompt":"{{prompt}}","size":"{{size}}","n":{{n}}}';
        $body = str_replace(
            ['{{prompt}}', '{{size}}', '{{n}}', '{{quality}}', '{{style}}'],
            [
                wp_json_encode($prompt),
                $params['size'],
                $params['number_of_images'],
                $params['quality'],
                $params['style'],
            ],
            $body_template
        );

        // Parse headers
        $headers = ['Content-Type' => 'application/json'];
        if (!empty($custom_settings['headers'])) {
            $header_lines = explode("\n", $custom_settings['headers']);
            foreach ($header_lines as $line) {
                $line = trim($line);
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $headers[trim($key)] = trim(str_replace('{{api_key}}', $api_key, $value));
                }
            }
        }

        // Add auth header
        if (!empty($api_key)) {
            if ($custom_settings['auth_type'] === 'bearer') {
                $headers['Authorization'] = 'Bearer ' . $api_key;
            } elseif ($custom_settings['auth_type'] === 'x-api-key') {
                $headers['X-API-Key'] = $api_key;
            }
        }

        $method = strtoupper($custom_settings['method'] ?: 'POST');

        $request_args = [
            'headers' => $headers,
            'timeout' => 120,
        ];

        if ($method === 'POST') {
            $request_args['body'] = $body;
        }

        $response = wp_remote_request($custom_settings['endpoint'], array_merge($request_args, [
            'method' => $method,
        ]));

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        if ($response_code < 200 || $response_code >= 300) {
            return new \WP_Error(
                'api_error',
                sprintf(__('API returned error code: %d', 'ai-seo-writer-pro'), $response_code)
            );
        }

        // Extract image URLs using response path
        $images = [];
        $response_path = trim($custom_settings['response_path'], '.');

        if (!empty($response_path)) {
            $data = $this->extract_by_path($response_body, $response_path);
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (is_string($item) && filter_var($item, FILTER_VALIDATE_URL)) {
                        $images[] = ['url' => $item];
                    } elseif (is_array($item) && !empty($item['url'])) {
                        $images[] = ['url' => $item['url']];
                    }
                }
            } elseif (is_string($data) && filter_var($data, FILTER_VALIDATE_URL)) {
                $images[] = ['url' => $data];
            }
        }

        // Fallback: try common paths
        if (empty($images)) {
            $images = $this->extract_image_urls_fallback($response_body);
        }

        if (empty($images)) {
            return new \WP_Error(
                'no_images_returned',
                __('No images were returned by the API. Check your response path configuration.', 'ai-seo-writer-pro')
            );
        }

        return $images;
    }

    /**
     * Extract value from nested array using dot notation path
     *
     * @param array  $data
     * @param string $path
     * @return mixed
     */
    private function extract_by_path($data, $path) {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (is_array($current) && isset($current[$key])) {
                $current = $current[$key];
            } else {
                return null;
            }
        }

        return $current;
    }

    /**
     * Try to extract image URLs from common response formats
     *
     * @param array $data
     * @return array
     */
    private function extract_image_urls_fallback($data) {
        $images = [];

        $common_paths = [
            'images',
            'data.images',
            'output',
            'results',
            'artifacts',
        ];

        foreach ($common_paths as $path) {
            $found = $this->extract_by_path($data, $path);
            if (is_array($found)) {
                foreach ($found as $item) {
                    if (is_string($item) && filter_var($item, FILTER_VALIDATE_URL)) {
                        $images[] = ['url' => $item];
                    } elseif (is_array($item) && !empty($item['url'])) {
                        $images[] = ['url' => $item['url']];
                    }
                }
                if (!empty($images)) {
                    break;
                }
            }
        }

        return $images;
    }

    /**
     * Upload base64 encoded image to media library
     *
     * @param string $base64_data
     * @return string|\WP_Error URL of uploaded image
     */
    private function upload_base64_image($base64_data) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Remove data URI scheme if present
        if (strpos($base64_data, 'base64,') !== false) {
            $base64_data = explode('base64,', $base64_data)[1];
        }

        $image_data = base64_decode($base64_data, true);
        if ($image_data === false) {
            return new \WP_Error('invalid_base64', __('Invalid base64 image data.', 'ai-seo-writer-pro'));
        }

        $tmp_file = wp_tempnam('ai-image');
        if (!$tmp_file) {
            return new \WP_Error('tmp_file_failed', __('Failed to create temporary file.', 'ai-seo-writer-pro'));
        }

        file_put_contents($tmp_file, $image_data);

        $file_type = wp_check_filetype('image.png');
        $file_array = [
            'name'     => 'ai-generated-' . time() . '.png',
            'tmp_name' => $tmp_file,
            'type'     => $file_type['type'],
        ];

        $attachment_id = media_handle_sideload($file_array, 0);

        @unlink($tmp_file);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        return wp_get_attachment_url($attachment_id);
    }

    /**
     * Generate an image prompt from article context
     *
     * @param string $article_topic
     * @param string $keyword
     * @param array  $extra_context Optional additional context (tone, audience, etc.)
     * @return string|\WP_Error Generated prompt
     */
    public function generate_image_prompt($article_topic, $keyword, $extra_context = []) {
        $article_topic = $this->security_manager->sanitize_text($article_topic);
        $keyword = $this->security_manager->sanitize_text($keyword);

        if (empty($article_topic) && empty($keyword)) {
            return new \WP_Error(
                'missing_context',
                __('At least a topic or keyword is required to generate an image prompt.', 'ai-seo-writer-pro')
            );
        }

        // Try to use AI to generate a sophisticated prompt
        $api_key = $this->settings_manager->get_api_key('openai');

        if (!empty($api_key)) {
            $tone = $extra_context['tone'] ?? 'professional';
            $audience = $extra_context['target_audience'] ?? '';

            $system_prompt = "You are an expert at creating detailed, SEO-optimized prompts for AI image generation. 
Create a descriptive image prompt that visually represents the article topic and keyword. 
The prompt should be specific, include style details, lighting, composition, and mood. 
Keep it under 400 characters. Return ONLY the prompt text, no explanations.";

            $user_prompt = sprintf(
                "Article topic: %s\nPrimary keyword: %s\nTone: %s\n%s\n\nCreate a detailed image generation prompt:",
                $article_topic,
                $keyword,
                $tone,
                !empty($audience) ? "Target audience: {$audience}" : ''
            );

            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ],
                'body' => wp_json_encode([
                    'model'       => 'gpt-4o-mini',
                    'messages'    => [
                        ['role' => 'system', 'content' => $system_prompt],
                        ['role' => 'user', 'content' => $user_prompt],
                    ],
                    'max_tokens'  => 200,
                    'temperature' => 0.7,
                ]),
                'timeout' => 30,
            ]);

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = json_decode(wp_remote_retrieve_body($response), true);
                if (!empty($body['choices'][0]['message']['content'])) {
                    return trim($body['choices'][0]['message']['content']);
                }
            }
        }

        // Fallback: generate a basic prompt template
        $prompt_parts = [];
        $prompt_parts[] = sprintf('A high-quality, professional illustration about "%s"', $article_topic ?: $keyword);

        if (!empty($keyword) && $keyword !== $article_topic) {
            $prompt_parts[] = sprintf('featuring the concept of "%s"', $keyword);
        }

        $prompt_parts[] = 'clean composition, vibrant colors, modern design';
        $prompt_parts[] = 'suitable for a blog article featured image';
        $prompt_parts[] = 'no text, no watermarks';

        return implode(', ', $prompt_parts);
    }

    /**
     * Upload an image from URL to WordPress Media Library
     *
     * @param string $image_url
     * @param string $alt_text
     * @return int|\WP_Error Attachment ID or WP_Error
     */
    public function upload_to_media_library($image_url, $alt_text = '') {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $image_url = esc_url_raw($image_url);

        if (empty($image_url)) {
            return new \WP_Error(
                'empty_url',
                __('Image URL is required.', 'ai-seo-writer-pro')
            );
        }

        $tmp = download_url($image_url);

        if (is_wp_error($tmp)) {
            return $tmp;
        }

        $file_array = [
            'name'     => basename(parse_url($image_url, PHP_URL_PATH)) ?: 'ai-generated-image.jpg',
            'tmp_name' => $tmp,
        ];

        // Ensure proper extension
        $file_type = wp_check_filetype($file_array['name']);
        if (empty($file_type['ext'])) {
            $file_array['name'] .= '.jpg';
        }

        $attachment_id = media_handle_sideload($file_array, 0);

        if (file_exists($tmp)) {
            @unlink($tmp);
        }

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        // Set alt text
        if (!empty($alt_text)) {
            $this->set_image_alt_text($attachment_id, $alt_text);
        }

        return $attachment_id;
    }

    /**
     * Set alt text for an attachment
     *
     * @param int    $attachment_id
     * @param string $alt_text
     * @return bool
     */
    public function set_image_alt_text($attachment_id, $alt_text) {
        $alt_text = sanitize_text_field($alt_text);
        return (bool) update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
    }

    /**
     * Get available image providers with their capabilities
     *
     * @return array
     */
    public function get_available_providers() {
        $providers = [];

        foreach ($this->providers as $key => $config) {
            $has_key = false;
            switch ($key) {
                case 'dall-e':
                    $has_key = $this->settings_manager->has_api_key('openai');
                    break;
                case 'stable-diffusion':
                    $has_key = $this->settings_manager->has_api_key('stable-diffusion')
                        || !empty($this->settings_manager->get_setting('stable_diffusion_endpoint', ''));
                    break;
                case 'external-api':
                    $custom_settings = $this->settings_manager->get_custom_api_settings();
                    $has_key = !empty($custom_settings['endpoint']);
                    break;
            }

            $providers[$key] = array_merge($config, [
                'configured' => $has_key,
            ]);
        }

        return $providers;
    }

    /**
     * Test connection to an image provider
     *
     * @param string $provider
     * @return array|\WP_Error
     */
    public function test_connection($provider) {
        if (!isset($this->providers[$provider])) {
            return new \WP_Error(
                'invalid_provider',
                sprintf(__('Invalid provider: %s', 'ai-seo-writer-pro'), $provider)
            );
        }

        switch ($provider) {
            case 'dall-e':
                return $this->test_dalle_connection();

            case 'stable-diffusion':
                return $this->test_stable_diffusion_connection();

            case 'external-api':
                return $this->test_external_api_connection();

            default:
                return new \WP_Error(
                    'unsupported_provider',
                    __('Unsupported provider for connection test.', 'ai-seo-writer-pro')
                );
        }
    }

    /**
     * Test DALL-E / OpenAI connection
     *
     * @return array|\WP_Error
     */
    private function test_dalle_connection() {
        $api_key = $this->settings_manager->get_api_key('openai');

        if (empty($api_key)) {
            return new \WP_Error(
                'api_key_missing',
                __('OpenAI API key is not configured.', 'ai-seo-writer-pro')
            );
        }

        $response = wp_remote_get('https://api.openai.com/v1/models', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code === 200) {
            return [
                'success' => true,
                'message' => __('Successfully connected to OpenAI API.', 'ai-seo-writer-pro'),
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $error_message = $body['error']['message'] ?? sprintf(__('API returned error code: %d', 'ai-seo-writer-pro'), $response_code);

        return new \WP_Error('connection_failed', $error_message);
    }

    /**
     * Test Stable Diffusion connection
     *
     * @return array|\WP_Error
     */
    private function test_stable_diffusion_connection() {
        $api_url = $this->settings_manager->get_setting('stable_diffusion_endpoint', '');

        if (empty($api_url)) {
            return new \WP_Error(
                'endpoint_missing',
                __('Stable Diffusion API endpoint is not configured.', 'ai-seo-writer-pro')
            );
        }

        $api_key = $this->settings_manager->get_api_key('stable-diffusion');
        $headers = [];
        if (!empty($api_key)) {
            $headers['Authorization'] = 'Bearer ' . $api_key;
        }

        $response = wp_remote_get($api_url, [
            'headers' => $headers,
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code >= 200 && $response_code < 500) {
            return [
                'success' => true,
                'message' => sprintf(__('Successfully connected to Stable Diffusion endpoint (HTTP %d).', 'ai-seo-writer-pro'), $response_code),
            ];
        }

        return new \WP_Error(
            'connection_failed',
            sprintf(__('Failed to connect (HTTP %d).', 'ai-seo-writer-pro'), $response_code)
        );
    }

    /**
     * Test external API connection
     *
     * @return array|\WP_Error
     */
    private function test_external_api_connection() {
        $custom_settings = $this->settings_manager->get_custom_api_settings();

        if (empty($custom_settings['endpoint'])) {
            return new \WP_Error(
                'endpoint_missing',
                __('External API endpoint is not configured.', 'ai-seo-writer-pro')
            );
        }

        $response = wp_remote_get($custom_settings['endpoint'], [
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);

        return [
            'success' => true,
            'message' => sprintf(__('Endpoint reachable (HTTP %d).', 'ai-seo-writer-pro'), $response_code),
            'code'    => $response_code,
        ];
    }

    /**
     * Sanitize image prompt
     *
     * @param string $prompt
     * @return string
     */
    private function sanitize_prompt($prompt) {
        $prompt = wp_strip_all_tags($prompt);
        $prompt = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $prompt);
        $prompt = trim($prompt);
        // Limit prompt length to reasonable size
        return substr($prompt, 0, 4000);
    }

    // ==================== AJAX Handlers ====================

    /**
     * AJAX: Generate image
     */
    public function ajax_generate_image() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $prompt = isset($_POST['prompt']) ? $this->security_manager->sanitize_textarea($_POST['prompt']) : '';

        if (empty($prompt)) {
            wp_send_json_error(['message' => __('Image prompt is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $params = [
            'provider'         => isset($_POST['provider']) ? $this->security_manager->sanitize_text($_POST['provider']) : '',
            'size'             => isset($_POST['size']) ? $this->security_manager->sanitize_text($_POST['size']) : '',
            'quality'          => isset($_POST['quality']) ? $this->security_manager->sanitize_text($_POST['quality']) : '',
            'style'            => isset($_POST['style']) ? $this->security_manager->sanitize_text($_POST['style']) : '',
            'number_of_images' => isset($_POST['number_of_images']) ? absint($_POST['number_of_images']) : 1,
        ];

        $result = $this->generate_image($prompt, $params);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
            return;
        }

        wp_send_json_success([
            'message' => sprintf(__('Successfully generated %d image(s).', 'ai-seo-writer-pro'), count($result)),
            'images'  => $result,
        ]);
    }

    /**
     * AJAX: Generate image prompt
     */
    public function ajax_generate_image_prompt() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $topic   = isset($_POST['topic']) ? $this->security_manager->sanitize_text($_POST['topic']) : '';
        $keyword = isset($_POST['keyword']) ? $this->security_manager->sanitize_text($_POST['keyword']) : '';

        if (empty($topic) && empty($keyword)) {
            wp_send_json_error(['message' => __('At least a topic or keyword is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $extra_context = [
            'tone'            => isset($_POST['tone']) ? $this->security_manager->sanitize_text($_POST['tone']) : '',
            'target_audience' => isset($_POST['target_audience']) ? $this->security_manager->sanitize_text($_POST['target_audience']) : '',
        ];

        $result = $this->generate_image_prompt($topic, $keyword, $extra_context);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
            return;
        }

        wp_send_json_success([
            'prompt' => $result,
        ]);
    }

    /**
     * AJAX: Upload image to media library
     */
    public function ajax_upload_image_to_library() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
        $alt_text  = isset($_POST['alt_text']) ? $this->security_manager->sanitize_text($_POST['alt_text']) : '';

        if (empty($image_url)) {
            wp_send_json_error(['message' => __('Image URL is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $result = $this->upload_to_media_library($image_url, $alt_text);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
            return;
        }

        wp_send_json_success([
            'message'        => __('Image uploaded to media library successfully.', 'ai-seo-writer-pro'),
            'attachment_id'  => $result,
            'attachment_url' => wp_get_attachment_url($result),
        ]);
    }

    /**
     * AJAX: Set image alt text
     */
    public function ajax_set_image_alt_text() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $attachment_id = isset($_POST['attachment_id']) ? absint($_POST['attachment_id']) : 0;
        $alt_text      = isset($_POST['alt_text']) ? $this->security_manager->sanitize_text($_POST['alt_text']) : '';

        if (empty($attachment_id)) {
            wp_send_json_error(['message' => __('Attachment ID is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $result = $this->set_image_alt_text($attachment_id, $alt_text);

        if ($result) {
            wp_send_json_success(['message' => __('Alt text updated successfully.', 'ai-seo-writer-pro')]);
        } else {
            wp_send_json_error(['message' => __('Failed to update alt text.', 'ai-seo-writer-pro')]);
        }
    }

    /**
     * AJAX: Get available providers
     */
    public function ajax_get_available_providers() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $providers = $this->get_available_providers();
        wp_send_json_success(['providers' => $providers]);
    }

    /**
     * AJAX: Test connection
     */
    public function ajax_test_connection() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $provider = isset($_POST['provider']) ? $this->security_manager->sanitize_text($_POST['provider']) : '';

        if (empty($provider)) {
            wp_send_json_error(['message' => __('Provider is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $result = $this->test_connection($provider);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
            return;
        }

        wp_send_json_success($result);
    }
}

<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Article Generator - Main article generation engine
 *
 * Handles AI-powered article generation, content manipulation,
 * and AJAX endpoints for all generation operations.
 */
class Article_Generator {

    /**
     * AI Providers instance
     * @var AI_Providers
     */
    private $ai_providers;

    /**
     * SEO Optimizer instance
     * @var SEO_Optimizer
     */
    private $seo_optimizer;

    /**
     * Security Manager instance
     * @var Security_Manager
     */
    private $security;

    /**
     * Database Manager instance
     * @var Database_Manager
     */
    private $database;

    /**
     * Settings Manager instance
     * @var Settings_Manager
     */
    private $settings;

    /**
     * Constructor
     *
     * @param AI_Providers $ai_providers
     * @param SEO_Optimizer $seo_optimizer
     */
    public function __construct($ai_providers, $seo_optimizer) {
        $this->ai_providers = $ai_providers;
        $this->seo_optimizer = $seo_optimizer;

        // Ensure SEO optimizer has access to AI providers
        if (method_exists($this->seo_optimizer, 'set_ai_providers')) {
            $this->seo_optimizer->set_ai_providers($this->ai_providers);
        }

        $this->security = new Security_Manager();
        $this->database = new Database_Manager();
        $this->settings = new Settings_Manager();
    }

    /**
     * Initialize hooks and AJAX handlers
     */
    public function init() {
        // AJAX handlers for article generation
        add_action('wp_ajax_ai_seo_writer_pro_generate_article', [$this, 'ajax_generate_article']);
        add_action('wp_ajax_ai_seo_writer_pro_regenerate_paragraph', [$this, 'ajax_regenerate_paragraph']);
        add_action('wp_ajax_ai_seo_writer_pro_rewrite_text', [$this, 'ajax_rewrite_text']);
        add_action('wp_ajax_ai_seo_writer_pro_expand_content', [$this, 'ajax_expand_content']);
        add_action('wp_ajax_ai_seo_writer_pro_shorten_content', [$this, 'ajax_shorten_content']);
        add_action('wp_ajax_ai_seo_writer_pro_change_tone', [$this, 'ajax_change_tone']);
        add_action('wp_ajax_ai_seo_writer_pro_improve_readability', [$this, 'ajax_improve_readability']);
        add_action('wp_ajax_ai_seo_writer_pro_fix_grammar', [$this, 'ajax_fix_grammar']);
        add_action('wp_ajax_ai_seo_writer_pro_remove_repetition', [$this, 'ajax_remove_repetition']);

        do_action('ai_seo_writer_pro_article_generator_init', $this);
    }

    /**
     * Generate a complete article
     *
     * @param array $params Generation parameters
     * @return array|\WP_Error
     */
    public function generate_article($params) {
        $defaults = [
            'topic' => '',
            'primary_keyword' => '',
            'secondary_keywords' => [],
            'target_audience' => 'general',
            'search_intent' => 'informational',
            'language' => 'en',
            'article_length' => 1500,
            'tone' => 'professional',
            'content_type' => 'blog',
            'competitor_url' => '',
            'ai_provider' => '',
            'ai_model' => '',
        ];

        $params = wp_parse_args($params, $defaults);

        // Sanitize inputs
        $topic = $this->security->sanitize_text($params['topic']);
        $primary_keyword = $this->security->sanitize_text($params['primary_keyword']);
        $secondary_keywords = $this->security->sanitize_array((array) $params['secondary_keywords']);
        $target_audience = $this->security->sanitize_text($params['target_audience']);
        $search_intent = $this->security->sanitize_text($params['search_intent']);
        $language = $this->security->sanitize_text($params['language']);
        $article_length = $this->security->sanitize_int($params['article_length']);
        $tone = $this->security->sanitize_text($params['tone']);
        $content_type = $this->security->sanitize_text($params['content_type']);
        $competitor_url = $this->security->sanitize_url($params['competitor_url']);
        $ai_provider = $this->security->sanitize_text($params['ai_provider']);
        $ai_model = $this->security->sanitize_text($params['ai_model']);

        // Validate required fields
        if (empty($topic)) {
            return new \WP_Error('missing_topic', __('Topic is required.', 'ai-seo-writer-pro'));
        }

        if (empty($primary_keyword)) {
            return new \WP_Error('missing_keyword', __('Primary keyword is required.', 'ai-seo-writer-pro'));
        }

        // Use defaults if not specified
        if (empty($ai_provider)) {
            $ai_provider = $this->settings->get_setting('default_ai_provider', 'openai');
        }
        if (empty($ai_model)) {
            $ai_model = $this->settings->get_setting('default_model', 'gpt-4o');
        }
        if (empty($language)) {
            $language = $this->settings->get_setting('default_language', 'en');
        }
        if (empty($tone)) {
            $tone = $this->settings->get_setting('default_tone', 'professional');
        }
        if (empty($content_type)) {
            $content_type = $this->settings->get_setting('default_content_type', 'blog');
        }
        if (empty($article_length)) {
            $article_length = $this->settings->get_setting('default_article_length', 1500);
        }

        // Check if provider is configured
        if (method_exists($this->ai_providers, 'is_provider_configured')) {
            if (!$this->ai_providers->is_provider_configured($ai_provider)) {
                return new \WP_Error(
                    'provider_not_configured',
                    sprintf(
                        /* translators: %s: AI provider name */
                        __('%s API key is not configured. Please go to Settings and add your API key.', 'ai-seo-writer-pro'),
                        ucfirst($ai_provider)
                    )
                );
            }
        }

        // Check license / generation limits
        $license_manager = new License_Manager();
        if ($license_manager->has_reached_limit()) {
            return new \WP_Error(
                'limit_reached',
                __('You have reached your daily generation limit. Please upgrade to Pro or try again tomorrow.', 'ai-seo-writer-pro')
            );
        }

        // Build prompts
        $system_prompt = $this->build_system_prompt($content_type, $tone, $language, $article_length);
        $user_prompt = $this->build_user_prompt([
            'topic' => $topic,
            'primary_keyword' => $primary_keyword,
            'secondary_keywords' => $secondary_keywords,
            'target_audience' => $target_audience,
            'search_intent' => $search_intent,
            'article_length' => $article_length,
            'competitor_url' => $competitor_url,
        ]);

        // Calculate max tokens based on article length
        $max_tokens = $this->calculate_max_tokens($article_length);
        $temperature = (float) $this->settings->get_setting('temperature', 0.7);

        // Call AI provider
        $ai_result = $this->ai_providers->generate_content($ai_provider, $ai_model, $system_prompt, $user_prompt, [
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
            'top_p' => (float) $this->settings->get_setting('top_p', 1.0),
            'frequency_penalty' => (float) $this->settings->get_setting('frequency_penalty', 0.0),
            'presence_penalty' => (float) $this->settings->get_setting('presence_penalty', 0.0),
        ]);

        if (is_wp_error($ai_result)) {
            return $ai_result;
        }

        if (empty($ai_result['content'])) {
            return new \WP_Error('empty_response', __('AI returned an empty response. Please try again.', 'ai-seo-writer-pro'));
        }

        $content = $ai_result['content'];
        $tokens_used = absint($ai_result['tokens_used'] ?? 0);
        $word_count = $this->count_words($content);

        // Generate all SEO elements
        $seo_title = $this->seo_optimizer->generate_seo_title($topic, $primary_keyword);
        $meta_description = $this->seo_optimizer->generate_meta_description($topic, $primary_keyword);
        $url_slug = $this->seo_optimizer->generate_url_slug($seo_title);
        $headings = $this->seo_optimizer->generate_headings_structure($topic, $primary_keyword, 5);
        $faq = $this->seo_optimizer->generate_faq_section($topic, $primary_keyword);
        $toc = $this->seo_optimizer->generate_table_of_contents($headings);

        // Generate schema
        $schema_data = [
            'headline' => $seo_title,
            'description' => $meta_description,
            'keywords' => array_merge([$primary_keyword], $secondary_keywords),
            'author' => wp_get_current_user()->display_name,
            'date_published' => current_time('c'),
            'date_modified' => current_time('c'),
        ];
        $schema_article = $this->seo_optimizer->generate_schema_markup('article', $schema_data);
        $schema_faq = $this->seo_optimizer->generate_schema_markup('faq', ['faq' => $faq]);

        $schema = $schema_article . "\n" . $schema_faq;

        // Save to history
        $history_id = $this->database->insert_history([
            'user_id' => get_current_user_id(),
            'title' => $seo_title,
            'topic' => $topic,
            'primary_keyword' => $primary_keyword,
            'secondary_keywords' => maybe_serialize($secondary_keywords),
            'content' => $content,
            'seo_title' => $seo_title,
            'meta_description' => $meta_description,
            'url_slug' => $url_slug,
            'ai_provider' => $ai_provider,
            'ai_model' => $ai_model,
            'tokens_used' => $tokens_used,
            'content_type' => $content_type,
            'language' => $language,
            'tone' => $tone,
            'word_count' => $word_count,
            'status' => 'draft',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);

        // Update daily stats
        $this->database->update_daily_stats(get_current_user_id(), [
            'articles_generated' => 1,
            'total_tokens' => $tokens_used,
            'total_words' => $word_count,
            'api_calls' => 1,
        ]);

        // Build structured response
        $response = [
            'id' => $history_id,
            'content' => $content,
            'seo_title' => $seo_title,
            'meta_description' => $meta_description,
            'url_slug' => $url_slug,
            'headings' => $headings,
            'faq' => $faq,
            'toc' => $toc,
            'schema' => $schema,
            'tokens_used' => $tokens_used,
            'word_count' => $word_count,
            'ai_provider' => $ai_provider,
            'ai_model' => $ai_model,
            'internal_links' => $this->seo_optimizer->suggest_internal_links($content),
            'external_links' => $this->seo_optimizer->suggest_external_links($topic),
            'open_graph' => $this->seo_optimizer->generate_open_graph($seo_title, $meta_description),
            'social_description' => $this->seo_optimizer->generate_social_description($content),
        ];

        return $response;
    }

    /**
     * Regenerate a specific paragraph in content
     *
     * @param string $content Original content
     * @param int $paragraph_index Zero-based paragraph index
     * @param string $instructions Optional instructions for regeneration
     * @return string|\WP_Error
     */
    public function regenerate_paragraph($content, $paragraph_index, $instructions = '') {
        $paragraph_index = absint($paragraph_index);
        $instructions = $this->security->sanitize_textarea($instructions);

        // Split content into paragraphs
        $paragraphs = $this->split_paragraphs($content);

        if (!isset($paragraphs[$paragraph_index])) {
            return new \WP_Error('invalid_index', __('Paragraph index not found.', 'ai-seo-writer-pro'));
        }

        $original_paragraph = $paragraphs[$paragraph_index];

        $system_prompt = "You are a professional content editor. Rewrite the following paragraph to improve it while maintaining the original meaning and context.";

        $user_prompt = "Rewrite this paragraph:\n\n\"$original_paragraph\"";

        if (!empty($instructions)) {
            $user_prompt .= "\n\nAdditional instructions: $instructions";
        }

        $user_prompt .= "\n\nReturn ONLY the rewritten paragraph, no explanations or quotes.";

        $result = $this->call_ai_for_edit($system_prompt, $user_prompt);

        if (is_wp_error($result)) {
            return $result;
        }

        $paragraphs[$paragraph_index] = $result;
        return implode("\n\n", $paragraphs);
    }

    /**
     * Rewrite text based on instructions
     *
     * @param string $text
     * @param string $instructions
     * @return string|\WP_Error
     */
    public function rewrite_text($text, $instructions) {
        $text = $this->security->sanitize_textarea($text);
        $instructions = $this->security->sanitize_textarea($instructions);

        if (empty($text)) {
            return new \WP_Error('empty_text', __('No text provided to rewrite.', 'ai-seo-writer-pro'));
        }

        if (empty($instructions)) {
            return new \WP_Error('empty_instructions', __('Please provide rewrite instructions.', 'ai-seo-writer-pro'));
        }

        $system_prompt = "You are a professional content editor. Rewrite the provided text according to the given instructions. Maintain the core message while applying the requested changes.";

        $user_prompt = "Original text:\n$text\n\nInstructions: $instructions\n\nReturn ONLY the rewritten text.";

        return $this->call_ai_for_edit($system_prompt, $user_prompt);
    }

    /**
     * Expand content to add more detail
     *
     * @param string $text
     * @return string|\WP_Error
     */
    public function expand_content($text) {
        $text = $this->security->sanitize_textarea($text);

        if (empty($text)) {
            return new \WP_Error('empty_text', __('No text provided to expand.', 'ai-seo-writer-pro'));
        }

        $system_prompt = "You are a professional content writer. Expand the following text by adding more details, examples, explanations, and depth. Make it more comprehensive while keeping the same tone and meaning.";

        $user_prompt = "Expand this text with more detail, examples, and depth:\n\n$text\n\nReturn ONLY the expanded content.";

        return $this->call_ai_for_edit($system_prompt, $user_prompt, 1500);
    }

    /**
     * Shorten content to be more concise
     *
     * @param string $text
     * @return string|\WP_Error
     */
    public function shorten_content($text) {
        $text = $this->security->sanitize_textarea($text);

        if (empty($text)) {
            return new \WP_Error('empty_text', __('No text provided to shorten.', 'ai-seo-writer-pro'));
        }

        $system_prompt = "You are a professional content editor. Condense the following text to be more concise and direct. Remove fluff and redundancy while preserving all key information and meaning.";

        $user_prompt = "Shorten this text to be more concise:\n\n$text\n\nReturn ONLY the shortened version.";

        return $this->call_ai_for_edit($system_prompt, $user_prompt);
    }

    /**
     * Change the tone of text
     *
     * @param string $text
     * @param string $new_tone
     * @return string|\WP_Error
     */
    public function change_tone($text, $new_tone) {
        $text = $this->security->sanitize_textarea($text);
        $new_tone = $this->security->sanitize_text($new_tone);

        if (empty($text)) {
            return new \WP_Error('empty_text', __('No text provided.', 'ai-seo-writer-pro'));
        }

        if (empty($new_tone)) {
            return new \WP_Error('empty_tone', __('Please specify the new tone.', 'ai-seo-writer-pro'));
        }

        $system_prompt = "You are a professional content editor. Rewrite the following text in a $new_tone tone. Preserve all original information, facts, and meaning - only change the writing style and tone.";

        $user_prompt = "Rewrite this text in a $new_tone tone:\n\n$text\n\nReturn ONLY the rewritten text.";

        return $this->call_ai_for_edit($system_prompt, $user_prompt);
    }

    /**
     * Improve readability of text
     *
     * @param string $text
     * @return string|\WP_Error
     */
    public function improve_readability($text) {
        $text = $this->security->sanitize_textarea($text);

        if (empty($text)) {
            return new \WP_Error('empty_text', __('No text provided.', 'ai-seo-writer-pro'));
        }

        $system_prompt = "You are a professional content editor. Improve the readability of the following text by: using shorter sentences, simpler words, better transitions, breaking up long paragraphs, and ensuring clear logical flow. Preserve all original meaning and key information.";

        $user_prompt = "Improve readability of this text:\n\n$text\n\nReturn ONLY the improved version.";

        return $this->call_ai_for_edit($system_prompt, $user_prompt);
    }

    /**
     * Fix grammar and spelling
     *
     * @param string $text
     * @return string|\WP_Error
     */
    public function fix_grammar($text) {
        $text = $this->security->sanitize_textarea($text);

        if (empty($text)) {
            return new \WP_Error('empty_text', __('No text provided.', 'ai-seo-writer-pro'));
        }

        $system_prompt = "You are a professional proofreader. Correct all grammar, spelling, punctuation, and syntax errors in the following text. Preserve the original meaning, tone, and style exactly - only fix errors.";

        $user_prompt = "Fix grammar and spelling in this text:\n\n$text\n\nReturn ONLY the corrected text.";

        return $this->call_ai_for_edit($system_prompt, $user_prompt);
    }

    /**
     * Remove repetition from text
     *
     * @param string $text
     * @return string|\WP_Error
     */
    public function remove_repetition($text) {
        $text = $this->security->sanitize_textarea($text);

        if (empty($text)) {
            return new \WP_Error('empty_text', __('No text provided.', 'ai-seo-writer-pro'));
        }

        $system_prompt = "You are a professional content editor. Remove repetitive words, phrases, and ideas from the following text. Make it concise and varied while preserving all unique information and the original meaning.";

        $user_prompt = "Remove repetition from this text:\n\n$text\n\nReturn ONLY the cleaned-up version.";

        return $this->call_ai_for_edit($system_prompt, $user_prompt);
    }

    /**
     * Build system prompt for article generation
     *
     * @param string $content_type
     * @param string $tone
     * @param string $language
     * @param int $article_length
     * @return string
     */
    private function build_system_prompt($content_type, $tone, $language, $article_length) {
        $content_type_descriptions = [
            'blog' => 'a well-structured SEO-optimized blog article',
            'howto' => 'a step-by-step how-to guide with clear instructions',
            'informational' => 'a comprehensive informational article',
            'review' => 'a detailed product review with pros and cons',
            'comparison' => 'a thorough comparison article analyzing multiple options',
            'listicle' => 'an engaging listicle with numbered items',
            'news' => 'a professional news article',
            'local_seo' => 'a local SEO-focused article targeting a specific geographic area',
            'landing_page' => 'persuasive landing page content with clear calls to action',
            'pillar' => 'a comprehensive pillar page covering a topic in great depth',
        ];

        $type_description = $content_type_descriptions[$content_type] ?? 'a well-structured article';

        $languages = $this->settings->get_languages();
        $language_name = $languages[$language] ?? 'English';

        $prompt = "You are an expert SEO content writer and copywriter. "
            . "Write $type_description in $language_name. "
            . "Target length: approximately $article_length words. "
            . "Tone: $tone. "
            . "Structure the content with proper HTML headings (H2, H3). "
            . "Include the primary keyword naturally in the first paragraph, "
            . "throughout the content, and in headings where appropriate. "
            . "Use secondary keywords naturally. "
            . "Write in short paragraphs (2-4 sentences each) for readability. "
            . "Include practical examples, actionable advice, and data points where relevant. "
            . "Ensure the content is original, engaging, and provides real value to readers. "
            . "Do not include a title or meta description - those will be generated separately. "
            . "Start directly with the introduction.";

        return $prompt;
    }

    /**
     * Build user prompt for article generation
     *
     * @param array $params
     * @return string
     */
    private function build_user_prompt($params) {
        $prompt = "Topic: " . $params['topic'] . "\n";
        $prompt .= "Primary Keyword: " . $params['primary_keyword'] . "\n";

        if (!empty($params['secondary_keywords'])) {
            $prompt .= "Secondary Keywords: " . implode(', ', (array) $params['secondary_keywords']) . "\n";
        }

        $prompt .= "Target Audience: " . $params['target_audience'] . "\n";
        $prompt .= "Search Intent: " . $params['search_intent'] . "\n";
        $prompt .= "Target Word Count: " . $params['article_length'] . "\n";

        if (!empty($params['competitor_url'])) {
            $prompt .= "Competitor URL for reference (outperform this content): " . $params['competitor_url'] . "\n";
        }

        $prompt .= "\nPlease write a comprehensive, SEO-optimized article that thoroughly covers this topic. "
            . "Structure it logically with clear sections. Make it engaging and valuable for readers.";

        return $prompt;
    }

    /**
     * Calculate max tokens based on article length
     *
     * @param int $word_count
     * @return int
     */
    private function calculate_max_tokens($word_count) {
        // Rough estimate: 1 word ≈ 1.3 tokens, add buffer
        $tokens = (int) ceil($word_count * 1.5);
        $max_setting = (int) $this->settings->get_setting('max_generation_tokens', 4000);
        return min($tokens, max($max_setting, 1000));
    }

    /**
     * Count words in content
     *
     * @param string $content
     * @return int
     */
    private function count_words($content) {
        $content = wp_strip_all_tags($content);
        $content = preg_replace('/\s+/', ' ', trim($content));
        if (empty($content)) {
            return 0;
        }
        $words = preg_split('/\s+/', $content);
        return count($words);
    }

    /**
     * Split content into paragraphs
     *
     * @param string $content
     * @return array
     */
    private function split_paragraphs($content) {
        // Strip HTML tags for splitting
        $text = preg_replace('/<br\s*\/?>/i', "\n", $content);
        $text = preg_replace('/<\/p>\s*<p>/i', "\n\n", $text);
        $text = wp_strip_all_tags($text);
        $paragraphs = preg_split('/\n\s*\n+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_filter($paragraphs, function ($p) {
            return trim($p) !== '';
        }));
    }

    /**
     * Call AI for text editing operations
     *
     * @param string $system_prompt
     * @param string $user_prompt
     * @param int $max_tokens
     * @return string|\WP_Error
     */
    private function call_ai_for_edit($system_prompt, $user_prompt, $max_tokens = 1000) {
        $ai_provider = $this->settings->get_setting('default_ai_provider', 'openai');
        $ai_model = $this->settings->get_setting('default_model', 'gpt-4o');

        // Check if provider is configured
        if (method_exists($this->ai_providers, 'is_provider_configured')) {
            if (!$this->ai_providers->is_provider_configured($ai_provider)) {
                return new \WP_Error(
                    'provider_not_configured',
                    sprintf(
                        /* translators: %s: AI provider name */
                        __('%s API key is not configured. Please go to Settings and add your API key.', 'ai-seo-writer-pro'),
                        ucfirst($ai_provider)
                    )
                );
            }
        }

        $result = $this->ai_providers->generate_content($ai_provider, $ai_model, $system_prompt, $user_prompt, [
            'max_tokens' => $max_tokens,
            'temperature' => 0.7,
        ]);

        if (is_wp_error($result)) {
            return $result;
        }

        if (empty($result['content'])) {
            return new \WP_Error('empty_response', __('AI returned an empty response.', 'ai-seo-writer-pro'));
        }

        return trim($result['content']);
    }

    // =========================================================================
    // AJAX Handlers
    // =========================================================================

    /**
     * AJAX: Generate article
     */
    public function ajax_generate_article() {
        if (!$this->security->verify_ajax_request()) {
            return;
        }

        $params = [
            'topic' => isset($_POST['topic']) ? wp_unslash($_POST['topic']) : '',
            'primary_keyword' => isset($_POST['primary_keyword']) ? wp_unslash($_POST['primary_keyword']) : '',
            'secondary_keywords' => isset($_POST['secondary_keywords']) ? (array) $_POST['secondary_keywords'] : [],
            'target_audience' => isset($_POST['target_audience']) ? wp_unslash($_POST['target_audience']) : 'general',
            'search_intent' => isset($_POST['search_intent']) ? wp_unslash($_POST['search_intent']) : 'informational',
            'language' => isset($_POST['language']) ? wp_unslash($_POST['language']) : '',
            'article_length' => isset($_POST['article_length']) ? absint($_POST['article_length']) : 0,
            'tone' => isset($_POST['tone']) ? wp_unslash($_POST['tone']) : '',
            'content_type' => isset($_POST['content_type']) ? wp_unslash($_POST['content_type']) : '',
            'competitor_url' => isset($_POST['competitor_url']) ? wp_unslash($_POST['competitor_url']) : '',
            'ai_provider' => isset($_POST['ai_provider']) ? wp_unslash($_POST['ai_provider']) : '',
            'ai_model' => isset($_POST['ai_model']) ? wp_unslash($_POST['ai_model']) : '',
        ];

        $result = $this->generate_article($params);

        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
                'code' => $result->get_error_code(),
            ]);
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Regenerate paragraph
     */
    public function ajax_regenerate_paragraph() {
        if (!$this->security->verify_ajax_request()) {
            return;
        }

        $content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';
        $paragraph_index = isset($_POST['paragraph_index']) ? absint($_POST['paragraph_index']) : 0;
        $instructions = isset($_POST['instructions']) ? sanitize_textarea_field(wp_unslash($_POST['instructions'])) : '';

        $result = $this->regenerate_paragraph($content, $paragraph_index, $instructions);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['content' => $result]);
    }

    /**
     * AJAX: Rewrite text
     */
    public function ajax_rewrite_text() {
        if (!$this->security->verify_ajax_request()) {
            return;
        }

        $text = isset($_POST['text']) ? wp_kses_post(wp_unslash($_POST['text'])) : '';
        $instructions = isset($_POST['instructions']) ? sanitize_textarea_field(wp_unslash($_POST['instructions'])) : '';

        $result = $this->rewrite_text($text, $instructions);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['text' => $result]);
    }

    /**
     * AJAX: Expand content
     */
    public function ajax_expand_content() {
        if (!$this->security->verify_ajax_request()) {
            return;
        }

        $text = isset($_POST['text']) ? wp_kses_post(wp_unslash($_POST['text'])) : '';

        $result = $this->expand_content($text);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['text' => $result]);
    }

    /**
     * AJAX: Shorten content
     */
    public function ajax_shorten_content() {
        if (!$this->security->verify_ajax_request()) {
            return;
        }

        $text = isset($_POST['text']) ? wp_kses_post(wp_unslash($_POST['text'])) : '';

        $result = $this->shorten_content($text);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['text' => $result]);
    }

    /**
     * AJAX: Change tone
     */
    public function ajax_change_tone() {
        if (!$this->security->verify_ajax_request()) {
            return;
        }

        $text = isset($_POST['text']) ? wp_kses_post(wp_unslash($_POST['text'])) : '';
        $new_tone = isset($_POST['tone']) ? sanitize_text_field(wp_unslash($_POST['tone'])) : '';

        $result = $this->change_tone($text, $new_tone);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['text' => $result]);
    }

    /**
     * AJAX: Improve readability
     */
    public function ajax_improve_readability() {
        if (!$this->security->verify_ajax_request()) {
            return;
        }

        $text = isset($_POST['text']) ? wp_kses_post(wp_unslash($_POST['text'])) : '';

        $result = $this->improve_readability($text);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['text' => $result]);
    }

    /**
     * AJAX: Fix grammar
     */
    public function ajax_fix_grammar() {
        if (!$this->security->verify_ajax_request()) {
            return;
        }

        $text = isset($_POST['text']) ? wp_kses_post(wp_unslash($_POST['text'])) : '';

        $result = $this->fix_grammar($text);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['text' => $result]);
    }

    /**
     * AJAX: Remove repetition
     */
    public function ajax_remove_repetition() {
        if (!$this->security->verify_ajax_request()) {
            return;
        }

        $text = isset($_POST['text']) ? wp_kses_post(wp_unslash($_POST['text'])) : '';

        $result = $this->remove_repetition($text);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['text' => $result]);
    }
}

<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Publishing Manager - Handles WordPress post creation, publishing, and formatting
 */
class Publishing_Manager {

    /**
     * Security Manager instance
     * @var Security_Manager
     */
    private $security_manager;

    /**
     * Constructor
     */
    public function __construct() {
        $this->security_manager = new Security_Manager();
    }

    /**
     * Initialize hooks and AJAX handlers
     */
    public function init() {
        add_action('wp_ajax_ai_seo_writer_pro_save_draft', [$this, 'ajax_save_draft']);
        add_action('wp_ajax_ai_seo_writer_pro_publish_now', [$this, 'ajax_publish_now']);
        add_action('wp_ajax_ai_seo_writer_pro_schedule_publication', [$this, 'ajax_schedule_publication']);
        add_action('wp_ajax_ai_seo_writer_pro_set_featured_image', [$this, 'ajax_set_featured_image']);
        add_action('wp_ajax_ai_seo_writer_pro_update_post_content', [$this, 'ajax_update_post_content']);
        add_action('wp_ajax_ai_seo_writer_pro_get_categories', [$this, 'ajax_get_categories']);
        add_action('wp_ajax_ai_seo_writer_pro_get_tags', [$this, 'ajax_get_tags']);
    }

    /**
     * Save article as a WordPress draft post
     *
     * @param array $article_data {
     *     @type string $title              Post title
     *     @type string $content            Post content (HTML)
     *     @type string $excerpt            Post excerpt
     *     @type string $seo_title          SEO title (stored as post meta)
     *     @type string $meta_description   Meta description (stored as post meta)
     *     @type string $focus_keyword      Focus keyword (stored as post meta)
     *     @type array  $categories         Array of category IDs or names
     *     @type array  $tags               Array of tag names
     *     @type string $featured_image_url URL or path to featured image
     * }
     * @return int|\WP_Error Post ID on success, WP_Error on failure
     */
    public function save_as_draft($article_data) {
        return $this->create_post($article_data, 'draft');
    }

    /**
     * Publish article immediately
     *
     * @param array $article_data See save_as_draft() for structure
     * @return int|\WP_Error Post ID on success, WP_Error on failure
     */
    public function publish_now($article_data) {
        return $this->create_post($article_data, 'publish');
    }

    /**
     * Schedule article for future publication
     *
     * @param array  $article_data See save_as_draft() for structure
     * @param string $datetime     Date/time string in 'Y-m-d H:i:s' format (local time)
     * @return int|\WP_Error Post ID on success, WP_Error on failure
     */
    public function schedule_publication($article_data, $datetime) {
        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return new \WP_Error(
                'invalid_datetime',
                __('Invalid date/time format.', 'ai-seo-writer-pro')
            );
        }

        $post_date = gmdate('Y-m-d H:i:s', $timestamp);
        $post_date_gmt = get_gmt_from_date($post_date);

        $post_data = $this->prepare_post_data($article_data, 'future');
        $post_data['post_date'] = $post_date;
        $post_data['post_date_gmt'] = $post_date_gmt;

        return $this->insert_post_with_meta($post_data, $article_data);
    }

    /**
     * Create a WordPress post with given status
     *
     * @param array  $article_data
     * @param string $status 'draft', 'publish', 'pending', 'future'
     * @return int|\WP_Error
     */
    private function create_post($article_data, $status) {
        $post_data = $this->prepare_post_data($article_data, $status);
        return $this->insert_post_with_meta($post_data, $article_data);
    }

    /**
     * Prepare post data array for wp_insert_post
     *
     * @param array  $article_data
     * @param string $status
     * @return array
     */
    private function prepare_post_data($article_data, $status) {
        $settings = new Settings_Manager();
        $use_gutenberg = $settings->get_setting('gutenberg_integration', true);

        $content = $article_data['content'] ?? '';

        // Format for Gutenberg if enabled
        if ($use_gutenberg && !empty($content)) {
            $content = $this->format_for_gutenberg($content);
        }

        $post_data = [
            'post_title'   => wp_strip_all_tags($article_data['title'] ?? __('Untitled Article', 'ai-seo-writer-pro')),
            'post_content' => $content,
            'post_excerpt' => $article_data['excerpt'] ?? '',
            'post_status'  => $status,
            'post_type'    => 'post',
            'post_author'  => get_current_user_id() ?: 1,
        ];

        return $post_data;
    }

    /**
     * Insert post and attach meta data, categories, tags, featured image
     *
     * @param array $post_data
     * @param array $article_data
     * @return int|\WP_Error
     */
    private function insert_post_with_meta($post_data, $article_data) {
        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Save SEO meta
        if (!empty($article_data['seo_title'])) {
            update_post_meta($post_id, '_ai_seo_title', sanitize_text_field($article_data['seo_title']));
        }
        if (!empty($article_data['meta_description'])) {
            update_post_meta($post_id, '_ai_meta_description', sanitize_textarea_field($article_data['meta_description']));
        }
        if (!empty($article_data['focus_keyword'])) {
            update_post_meta($post_id, '_ai_focus_keyword', sanitize_text_field($article_data['focus_keyword']));
        }

        // Set Yoast/Rank Math compatible meta if available
        if (!empty($article_data['seo_title'])) {
            update_post_meta($post_id, '_yoast_wpseo_title', sanitize_text_field($article_data['seo_title']));
            update_post_meta($post_id, 'rank_math_title', sanitize_text_field($article_data['seo_title']));
        }
        if (!empty($article_data['meta_description'])) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_textarea_field($article_data['meta_description']));
            update_post_meta($post_id, 'rank_math_description', sanitize_textarea_field($article_data['meta_description']));
        }
        if (!empty($article_data['focus_keyword'])) {
            update_post_meta($post_id, '_yoast_wpseo_focuskw', sanitize_text_field($article_data['focus_keyword']));
            update_post_meta($post_id, 'rank_math_focus_keyword', sanitize_text_field($article_data['focus_keyword']));
        }

        // Assign categories
        if (!empty($article_data['categories']) && is_array($article_data['categories'])) {
            $category_ids = $this->normalize_category_ids($article_data['categories']);
            if (!empty($category_ids)) {
                wp_set_post_categories($post_id, $category_ids);
            }
        }

        // Assign tags
        if (!empty($article_data['tags']) && is_array($article_data['tags'])) {
            $tags = array_map('sanitize_text_field', $article_data['tags']);
            $tags = array_filter($tags);
            if (!empty($tags)) {
                wp_set_post_tags($post_id, $tags);
            }
        }

        // Set featured image
        if (!empty($article_data['featured_image_url'])) {
            $featured_result = $this->set_featured_image($post_id, $article_data['featured_image_url']);
            if (is_wp_error($featured_result)) {
                $this->security_manager->log_error(
                    sprintf('Failed to set featured image for post %d: %s', $post_id, $featured_result->get_error_message()),
                    ['post_id' => $post_id]
                );
            }
        }

        return $post_id;
    }

    /**
     * Normalize category input - accept both IDs and names
     *
     * @param array $categories Array of category IDs (int) or names (string)
     * @return array Array of valid category IDs
     */
    private function normalize_category_ids($categories) {
        $ids = [];

        foreach ($categories as $cat) {
            if (is_numeric($cat)) {
                $cat_id = absint($cat);
                if (term_exists($cat_id, 'category')) {
                    $ids[] = $cat_id;
                }
            } else {
                $cat_name = sanitize_text_field($cat);
                $term = term_exists($cat_name, 'category');
                if ($term) {
                    $ids[] = (int) $term['term_id'];
                } else {
                    // Create category if it doesn't exist
                    $new_cat = wp_insert_term($cat_name, 'category');
                    if (!is_wp_error($new_cat)) {
                        $ids[] = (int) $new_cat['term_id'];
                    }
                }
            }
        }

        return array_unique($ids);
    }

    /**
     * Download image and set as featured image for a post
     *
     * @param int    $post_id           Post ID
     * @param string $image_url_or_path URL or local file path to the image
     * @return int|\WP_Error Attachment ID on success, WP_Error on failure
     */
    public function set_featured_image($post_id, $image_url_or_path) {
        if (!get_post($post_id)) {
            return new \WP_Error(
                'invalid_post',
                __('Invalid post ID.', 'ai-seo-writer-pro')
            );
        }

        $attachment_id = $this->upload_to_media_library($image_url_or_path);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        $result = set_post_thumbnail($post_id, $attachment_id);

        if (!$result) {
            return new \WP_Error(
                'set_thumbnail_failed',
                __('Failed to set post thumbnail.', 'ai-seo-writer-pro')
            );
        }

        return $attachment_id;
    }

    /**
     * Upload an image from URL or path to WordPress Media Library
     *
     * @param string $image_url_or_path URL or local file path
     * @param string $alt_text          Optional alt text
     * @return int|\WP_Error Attachment ID on success, WP_Error on failure
     */
    public function upload_to_media_library($image_url_or_path, $alt_text = '') {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $file_array = [];

        if (filter_var($image_url_or_path, FILTER_VALIDATE_URL)) {
            // It's a URL - download it first
            $tmp = download_url($image_url_or_path);

            if (is_wp_error($tmp)) {
                return $tmp;
            }

            $file_array = [
                'name'     => basename($image_url_or_path),
                'tmp_name' => $tmp,
            ];

            // Fix file extension if needed
            $file_type = wp_check_filetype($file_array['name']);
            if (empty($file_type['ext'])) {
                $file_array['name'] .= '.jpg';
            }
        } elseif (file_exists($image_url_or_path)) {
            // It's a local file path
            $file_array = [
                'name'     => basename($image_url_or_path),
                'tmp_name' => $image_url_or_path,
            ];
        } else {
            return new \WP_Error(
                'invalid_image_source',
                __('Invalid image URL or file path.', 'ai-seo-writer-pro')
            );
        }

        // Validate file type
        $file_type = wp_check_filetype($file_array['name']);
        if (empty($file_type['type']) || strpos($file_type['type'], 'image/') !== 0) {
            if (filter_var($image_url_or_path, FILTER_VALIDATE_URL)) {
                @unlink($file_array['tmp_name']);
            }
            return new \WP_Error(
                'invalid_file_type',
                __('The file is not a valid image.', 'ai-seo-writer-pro')
            );
        }

        // Upload to media library
        $attachment_id = media_handle_sideload($file_array, 0);

        if (is_wp_error($attachment_id)) {
            if (filter_var($image_url_or_path, FILTER_VALIDATE_URL) && file_exists($file_array['tmp_name'])) {
                @unlink($file_array['tmp_name']);
            }
            return $attachment_id;
        }

        // Set alt text if provided
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
     * Update content of an existing post
     *
     * @param int    $post_id
     * @param string $content New HTML content
     * @return bool|\WP_Error
     */
    public function update_post_content($post_id, $content) {
        $post = get_post($post_id);

        if (!$post) {
            return new \WP_Error(
                'invalid_post',
                __('Invalid post ID.', 'ai-seo-writer-pro')
            );
        }

        $settings = new Settings_Manager();
        $use_gutenberg = $settings->get_setting('gutenberg_integration', true);

        if ($use_gutenberg) {
            $content = $this->format_for_gutenberg($content);
        }

        $result = wp_update_post([
            'ID'           => $post_id,
            'post_content' => $content,
        ], true);

        if (is_wp_error($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Get available post categories
     *
     * @return array Array of category objects with id, name, slug
     */
    public function get_post_categories() {
        $categories = get_categories([
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
            'number'     => 100,
        ]);

        $result = [];
        foreach ($categories as $cat) {
            $result[] = [
                'id'   => (int) $cat->term_id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ];
        }

        return $result;
    }

    /**
     * Get available post tags
     *
     * @return array Array of tag objects with id, name, slug
     */
    public function get_post_tags() {
        $tags = get_tags([
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
            'number'     => 100,
        ]);

        $result = [];
        foreach ($tags as $tag) {
            $result[] = [
                'id'   => (int) $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ];
        }

        return $result;
    }

    /**
     * Convert HTML content to Gutenberg block format
     *
     * Wraps content in appropriate Gutenberg block delimiters so that
     * WordPress displays it properly in the block editor.
     *
     * @param string $html_content Raw HTML content
     * @return string Gutenberg-formatted content
     */
    public function format_for_gutenberg($html_content) {
        if (empty($html_content)) {
            return '';
        }

        // If content already has Gutenberg block markers, return as-is
        if (strpos($html_content, '<!-- wp:') !== false) {
            return $html_content;
        }

        $blocks = [];

        // Split content by common block-level elements
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);

        // Ensure UTF-8 handling
        $html_content = mb_convert_encoding($html_content, 'HTML-ENTITIES', 'UTF-8');
        $dom->loadHTML('<!DOCTYPE html><html><body>' . $html_content . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return $this->wrap_in_html_block($html_content);
        }

        foreach ($body->childNodes as $node) {
            if ($node->nodeType === XML_TEXT_NODE && trim($node->nodeValue) === '') {
                continue;
            }

            $node_html = $dom->saveHTML($node);

            switch ($node->nodeName) {
                case 'h1':
                case 'h2':
                case 'h3':
                case 'h4':
                case 'h5':
                case 'h6':
                    $level = substr($node->nodeName, 1);
                    $text_content = wp_strip_all_tags($node_html);
                    $blocks[] = "<!-- wp:heading {{\"level\":{$level}}} -->\n";
                    $blocks[] = "<h{$level}>{$text_content}</h{$level}>\n";
                    $blocks[] = "<!-- /wp:heading -->\n";
                    break;

                case 'ul':
                    $blocks[] = "<!-- wp:list -->\n";
                    $blocks[] = $node_html . "\n";
                    $blocks[] = "<!-- /wp:list -->\n";
                    break;

                case 'ol':
                    $blocks[] = "<!-- wp:list {{\"ordered\":true}} -->\n";
                    $blocks[] = $node_html . "\n";
                    $blocks[] = "<!-- /wp:list -->\n";
                    break;

                case 'blockquote':
                    $blocks[] = "<!-- wp:quote -->\n";
                    $blocks[] = "<blockquote class=\"wp-block-quote\">\n";
                    $blocks[] = $node->nodeValue . "\n";
                    $blocks[] = "</blockquote>\n";
                    $blocks[] = "<!-- /wp:quote -->\n";
                    break;

                case 'img':
                    $src = $node->getAttribute('src');
                    $alt = $node->getAttribute('alt');
                    $blocks[] = "<!-- wp:image -->\n";
                    $blocks[] = "<figure class=\"wp-block-image\"><img src=\"" . esc_url($src) . "\" alt=\"" . esc_attr($alt) . "\"/></figure>\n";
                    $blocks[] = "<!-- /wp:image -->\n";
                    break;

                case 'pre':
                case 'code':
                    $code_content = wp_strip_all_tags($node_html);
                    $blocks[] = "<!-- wp:code -->\n";
                    $blocks[] = "<pre class=\"wp-block-code\"><code>{$code_content}</code></pre>\n";
                    $blocks[] = "<!-- /wp:code -->\n";
                    break;

                case 'hr':
                    $blocks[] = "<!-- wp:separator -->\n";
                    $blocks[] = "<hr class=\"wp-block-separator has-alpha-channel-opacity\"/>\n";
                    $blocks[] = "<!-- /wp:separator -->\n";
                    break;

                case 'table':
                    $blocks[] = "<!-- wp:table -->\n";
                    $blocks[] = "<figure class=\"wp-block-table\">{$node_html}</figure>\n";
                    $blocks[] = "<!-- /wp:table -->\n";
                    break;

                case 'p':
                default:
                    // Paragraph or generic content
                    $p_content = trim($node_html);
                    if (!empty($p_content)) {
                        $blocks[] = "<!-- wp:paragraph -->\n";
                        $blocks[] = "<p>{$p_content}</p>\n";
                        $blocks[] = "<!-- /wp:paragraph -->\n";
                    }
                    break;
            }
        }

        $result = implode('', $blocks);

        // Fallback: if no blocks were created, wrap in a single HTML block
        if (empty($result)) {
            $result = $this->wrap_in_html_block($html_content);
        }

        return $result;
    }

    /**
     * Wrap content in a Gutenberg HTML block (fallback)
     *
     * @param string $content
     * @return string
     */
    private function wrap_in_html_block($content) {
        return "<!-- wp:html -->\n" . $content . "\n<!-- /wp:html -->\n";
    }

    // ==================== AJAX Handlers ====================

    /**
     * AJAX: Save as draft
     */
    public function ajax_save_draft() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $article_data = $this->parse_article_data_from_request();
        $post_id = $this->save_as_draft($article_data);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => $post_id->get_error_message()]);
            return;
        }

        wp_send_json_success([
            'message' => __('Draft saved successfully.', 'ai-seo-writer-pro'),
            'post_id' => $post_id,
            'edit_url' => get_edit_post_link($post_id, ''),
        ]);
    }

    /**
     * AJAX: Publish now
     */
    public function ajax_publish_now() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $article_data = $this->parse_article_data_from_request();
        $post_id = $this->publish_now($article_data);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => $post_id->get_error_message()]);
            return;
        }

        wp_send_json_success([
            'message' => __('Article published successfully.', 'ai-seo-writer-pro'),
            'post_id' => $post_id,
            'edit_url' => get_edit_post_link($post_id, ''),
            'view_url' => get_permalink($post_id),
        ]);
    }

    /**
     * AJAX: Schedule publication
     */
    public function ajax_schedule_publication() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $datetime = isset($_POST['datetime']) ? $this->security_manager->sanitize_text($_POST['datetime']) : '';

        if (empty($datetime)) {
            wp_send_json_error(['message' => __('Date/time is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $article_data = $this->parse_article_data_from_request();
        $post_id = $this->schedule_publication($article_data, $datetime);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => $post_id->get_error_message()]);
            return;
        }

        wp_send_json_success([
            'message' => __('Article scheduled successfully.', 'ai-seo-writer-pro'),
            'post_id' => $post_id,
            'edit_url' => get_edit_post_link($post_id, ''),
        ]);
    }

    /**
     * AJAX: Set featured image
     */
    public function ajax_set_featured_image() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';

        if (empty($post_id) || empty($image_url)) {
            wp_send_json_error(['message' => __('Post ID and image URL are required.', 'ai-seo-writer-pro')]);
            return;
        }

        $result = $this->set_featured_image($post_id, $image_url);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
            return;
        }

        wp_send_json_success([
            'message' => __('Featured image set successfully.', 'ai-seo-writer-pro'),
            'attachment_id' => $result,
        ]);
    }

    /**
     * AJAX: Update post content
     */
    public function ajax_update_post_content() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $content = isset($_POST['content']) ? $this->security_manager->sanitize_html($_POST['content']) : '';

        if (empty($post_id)) {
            wp_send_json_error(['message' => __('Post ID is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $result = $this->update_post_content($post_id, $content);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
            return;
        }

        wp_send_json_success([
            'message' => __('Post content updated successfully.', 'ai-seo-writer-pro'),
        ]);
    }

    /**
     * AJAX: Get categories
     */
    public function ajax_get_categories() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $categories = $this->get_post_categories();
        wp_send_json_success(['categories' => $categories]);
    }

    /**
     * AJAX: Get tags
     */
    public function ajax_get_tags() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $tags = $this->get_post_tags();
        wp_send_json_success(['tags' => $tags]);
    }

    /**
     * Parse article data from AJAX request
     *
     * @return array
     */
    private function parse_article_data_from_request() {
        return [
            'title'              => isset($_POST['title']) ? $this->security_manager->sanitize_text($_POST['title']) : '',
            'content'            => isset($_POST['content']) ? $this->security_manager->sanitize_html($_POST['content']) : '',
            'excerpt'            => isset($_POST['excerpt']) ? $this->security_manager->sanitize_textarea($_POST['excerpt']) : '',
            'seo_title'          => isset($_POST['seo_title']) ? $this->security_manager->sanitize_text($_POST['seo_title']) : '',
            'meta_description'   => isset($_POST['meta_description']) ? $this->security_manager->sanitize_textarea($_POST['meta_description']) : '',
            'focus_keyword'      => isset($_POST['focus_keyword']) ? $this->security_manager->sanitize_text($_POST['focus_keyword']) : '',
            'categories'         => isset($_POST['categories']) ? array_map('absint', (array) $_POST['categories']) : [],
            'tags'               => isset($_POST['tags']) ? $this->security_manager->sanitize_array((array) $_POST['tags']) : [],
            'featured_image_url' => isset($_POST['featured_image_url']) ? esc_url_raw($_POST['featured_image_url']) : '',
        ];
    }
}

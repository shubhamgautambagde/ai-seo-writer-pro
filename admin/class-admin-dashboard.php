<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Dashboard - Main admin interface class
 */
class Admin_Dashboard {

    /**
     * Settings Manager instance
     * @var Settings_Manager
     */
    private $settings;

    /**
     * AI Providers instance
     * @var AI_Providers
     */
    private $ai_providers;

    /**
     * Article Generator instance
     * @var Article_Generator
     */
    private $article_generator;

    /**
     * SEO Analyzer instance
     * @var SEO_Analyzer
     */
    private $seo_analyzer;

    /**
     * Content Templates instance
     * @var Content_Templates
     */
    private $content_templates;

    /**
     * Image Generator instance
     * @var Image_Generator
     */
    private $image_generator;

    /**
     * Bulk Generator instance
     * @var Bulk_Generator
     */
    private $bulk_generator;

    /**
     * Publishing Manager instance
     * @var Publishing_Manager
     */
    private $publishing_manager;

    /**
     * License Manager instance
     * @var License_Manager
     */
    private $license;

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
     * Plugin menu hook suffixes
     * @var array
     */
    private $page_hooks = [];

    /**
     * Constructor
     *
     * @param Settings_Manager    $settings
     * @param AI_Providers        $ai_providers
     * @param Article_Generator   $article_generator
     * @param SEO_Analyzer        $seo_analyzer
     * @param Content_Templates   $content_templates
     * @param Image_Generator     $image_generator
     * @param Bulk_Generator      $bulk_generator
     * @param Publishing_Manager  $publishing_manager
     * @param License_Manager     $license
     */
    public function __construct(
        Settings_Manager $settings,
        AI_Providers $ai_providers,
        Article_Generator $article_generator,
        SEO_Analyzer $seo_analyzer,
        Content_Templates $content_templates,
        Image_Generator $image_generator,
        Bulk_Generator $bulk_generator,
        Publishing_Manager $publishing_manager,
        License_Manager $license
    ) {
        $this->settings           = $settings;
        $this->ai_providers       = $ai_providers;
        $this->article_generator  = $article_generator;
        $this->seo_analyzer       = $seo_analyzer;
        $this->content_templates  = $content_templates;
        $this->image_generator    = $image_generator;
        $this->bulk_generator     = $bulk_generator;
        $this->publishing_manager = $publishing_manager;
        $this->license            = $license;
        $this->security           = new Security_Manager();
        $this->database           = new Database_Manager();
    }

    /**
     * Initialize admin hooks
     */
    public function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        $this->register_ajax_handlers();
    }

    /**
     * Add admin menu and submenus
     */
    public function add_admin_menu() {
        $capability = $this->security->get_required_capability();

        // Main menu
        $this->page_hooks['overview'] = add_menu_page(
            __('AI SEO Writer Pro', 'ai-seo-writer-pro'),
            __('AI SEO Writer', 'ai-seo-writer-pro'),
            $capability,
            'ai-seo-writer-pro',
            [$this, 'render_overview'],
            'dashicons-edit-page',
            25
        );

        // Submenus
        $submenus = [
            'overview' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('Overview', 'ai-seo-writer-pro'),
                'menu_title'=> __('Overview', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_overview'],
            ],
            'article-generator' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('AI Article Generator', 'ai-seo-writer-pro'),
                'menu_title'=> __('Article Generator', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_article_generator'],
            ],
            'bulk-generator' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('Bulk Generator', 'ai-seo-writer-pro'),
                'menu_title'=> __('Bulk Generator', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_bulk_generator'],
            ],
            'seo-analyzer' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('SEO Analyzer', 'ai-seo-writer-pro'),
                'menu_title'=> __('SEO Analyzer', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_seo_analyzer'],
            ],
            'content-templates' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('Content Templates', 'ai-seo-writer-pro'),
                'menu_title'=> __('Content Templates', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_content_templates'],
            ],
            'image-generator' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('AI Image Generator', 'ai-seo-writer-pro'),
                'menu_title'=> __('Image Generator', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_image_generator'],
            ],
            'article-history' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('Article History', 'ai-seo-writer-pro'),
                'menu_title'=> __('Article History', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_article_history'],
            ],
            'scheduled-articles' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('Scheduled Articles', 'ai-seo-writer-pro'),
                'menu_title'=> __('Scheduled Articles', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_scheduled_articles'],
            ],
            'api-settings' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('API Settings', 'ai-seo-writer-pro'),
                'menu_title'=> __('API Settings', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_api_settings'],
            ],
            'usage-credits' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('Usage & Credits', 'ai-seo-writer-pro'),
                'menu_title'=> __('Usage & Credits', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_usage_credits'],
            ],
            'general-settings' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('General Settings', 'ai-seo-writer-pro'),
                'menu_title'=> __('General Settings', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_general_settings'],
            ],
            'documentation' => [
                'parent'    => 'ai-seo-writer-pro',
                'page_title'=> __('Documentation', 'ai-seo-writer-pro'),
                'menu_title'=> __('Documentation', 'ai-seo-writer-pro'),
                'callback'  => [$this, 'render_documentation'],
            ],
        ];

        foreach ($submenus as $slug => $menu) {
            $this->page_hooks[$slug] = add_submenu_page(
                $menu['parent'],
                $menu['page_title'],
                $menu['menu_title'],
                $capability,
                'ai-seo-writer-pro' . ($slug === 'overview' ? '' : '-' . $slug),
                $menu['callback']
            );
        }
    }

    /**
     * Enqueue admin assets only on plugin pages
     *
     * @param string $hook
     */
    public function enqueue_assets($hook) {
        if (!in_array($hook, $this->page_hooks, true)) {
            return;
        }

        $css_url = AI_SEO_WRITER_PRO_PLUGIN_URL . 'admin/css/admin-style.css';
        $js_url  = AI_SEO_WRITER_PRO_PLUGIN_URL . 'admin/js/admin-script.js';
        $css_ver = file_exists(AI_SEO_WRITER_PRO_PLUGIN_DIR . 'admin/css/admin-style.css')
            ? filemtime(AI_SEO_WRITER_PRO_PLUGIN_DIR . 'admin/css/admin-style.css')
            : AI_SEO_WRITER_PRO_VERSION;
        $js_ver = file_exists(AI_SEO_WRITER_PRO_PLUGIN_DIR . 'admin/js/admin-script.js')
            ? filemtime(AI_SEO_WRITER_PRO_PLUGIN_DIR . 'admin/js/admin-script.js')
            : AI_SEO_WRITER_PRO_VERSION;

        wp_enqueue_style(
            'ai-seo-writer-pro-admin',
            $css_url,
            [],
            $css_ver
        );

        wp_enqueue_script(
            'ai-seo-writer-pro-admin',
            $js_url,
            ['jquery'],
            $js_ver,
            true
        );

        wp_localize_script('ai-seo-writer-pro-admin', 'AI_SEO_Writer_Pro', [
            'ajax_url'      => admin_url('admin-ajax.php'),
            'rest_url'      => rest_url('ai-seo-writer-pro/v1'),
            'nonce'         => $this->security->get_nonce(),
            'nonce_name'    => Security_Manager::NONCE_NAME,
            'is_pro'        => $this->license->is_pro(),
            'strings'       => [
                'confirm'           => __('Are you sure?', 'ai-seo-writer-pro'),
                'generating'        => __('Generating...', 'ai-seo-writer-pro'),
                'success'           => __('Success!', 'ai-seo-writer-pro'),
                'error'             => __('An error occurred.', 'ai-seo-writer-pro'),
                'loading'           => __('Loading...', 'ai-seo-writer-pro'),
                'saving'            => __('Saving...', 'ai-seo-writer-pro'),
                'deleting'          => __('Deleting...', 'ai-seo-writer-pro'),
                'copied'            => __('Copied!', 'ai-seo-writer-pro'),
                'no_results'        => __('No results found.', 'ai-seo-writer-pro'),
                'paused'            => __('Paused', 'ai-seo-writer-pro'),
                'resumed'           => __('Resumed', 'ai-seo-writer-pro'),
                'connection_ok'     => __('Connection successful!', 'ai-seo-writer-pro'),
                'connection_failed' => __('Connection failed.', 'ai-seo-writer-pro'),
            ],
        ]);
    }

    /**
     * Register AJAX handlers
     */
    public function register_ajax_handlers() {
        // Article generation
        add_action('wp_ajax_ai_seo_writer_pro_generate_article', [$this, 'ajax_generate_article']);
        add_action('wp_ajax_ai_seo_writer_pro_save_article', [$this, 'ajax_save_article']);
        add_action('wp_ajax_ai_seo_writer_pro_publish_article', [$this, 'ajax_publish_article']);

        // SEO Analyzer
        add_action('wp_ajax_ai_seo_writer_pro_analyze_seo', [$this, 'ajax_analyze_seo']);

        // Bulk generator
        add_action('wp_ajax_ai_seo_writer_pro_upload_csv', [$this, 'ajax_upload_csv']);
        add_action('wp_ajax_ai_seo_writer_pro_get_bulk_progress', [$this, 'ajax_get_bulk_progress']);
        add_action('wp_ajax_ai_seo_writer_pro_pause_bulk', [$this, 'ajax_pause_bulk']);
        add_action('wp_ajax_ai_seo_writer_pro_resume_bulk', [$this, 'ajax_resume_bulk']);
        add_action('wp_ajax_ai_seo_writer_pro_cancel_bulk', [$this, 'ajax_cancel_bulk']);

        // Image generator
        add_action('wp_ajax_ai_seo_writer_pro_generate_image', [$this, 'ajax_generate_image']);
        add_action('wp_ajax_ai_seo_writer_pro_upload_to_media', [$this, 'ajax_upload_to_media']);

        // Templates
        add_action('wp_ajax_ai_seo_writer_pro_save_template', [$this, 'ajax_save_template']);
        add_action('wp_ajax_ai_seo_writer_pro_delete_template', [$this, 'ajax_delete_template']);
        add_action('wp_ajax_ai_seo_writer_pro_get_template', [$this, 'ajax_get_template']);

        // History
        add_action('wp_ajax_ai_seo_writer_pro_get_history', [$this, 'ajax_get_history']);
        add_action('wp_ajax_ai_seo_writer_pro_delete_history_item', [$this, 'ajax_delete_history_item']);

        // Scheduled
        add_action('wp_ajax_ai_seo_writer_pro_get_scheduled', [$this, 'ajax_get_scheduled']);
        add_action('wp_ajax_ai_seo_writer_pro_cancel_scheduled', [$this, 'ajax_cancel_scheduled']);

        // Settings
        add_action('wp_ajax_ai_seo_writer_pro_save_api_key', [$this, 'ajax_save_api_key']);
        add_action('wp_ajax_ai_seo_writer_pro_test_api_connection', [$this, 'ajax_test_api_connection']);
        add_action('wp_ajax_ai_seo_writer_pro_save_general_settings', [$this, 'ajax_save_general_settings']);
        add_action('wp_ajax_ai_seo_writer_pro_activate_license', [$this, 'ajax_activate_license']);

        // Dashboard
        add_action('wp_ajax_ai_seo_writer_pro_get_dashboard_stats', [$this, 'ajax_get_dashboard_stats']);
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('ai-seo-writer-pro/v1', '/generate', [
            'methods'             => 'POST',
            'callback'            => [$this, 'rest_generate_article'],
            'permission_callback' => function ($request) {
                return $this->security->verify_rest_permission($request);
            },
        ]);

        register_rest_route('ai-seo-writer-pro/v1', '/analyze', [
            'methods'             => 'POST',
            'callback'            => [$this, 'rest_analyze_seo'],
            'permission_callback' => function ($request) {
                return $this->security->verify_rest_permission($request);
            },
        ]);

        register_rest_route('ai-seo-writer-pro/v1', '/history', [
            'methods'             => 'GET',
            'callback'            => [$this, 'rest_get_history'],
            'permission_callback' => function ($request) {
                return $this->security->verify_rest_permission($request);
            },
        ]);

        register_rest_route('ai-seo-writer-pro/v1', '/templates', [
            'methods'             => 'GET',
            'callback'            => [$this, 'rest_get_templates'],
            'permission_callback' => function ($request) {
                return $this->security->verify_rest_permission($request);
            },
        ]);

        register_rest_route('ai-seo-writer-pro/v1', '/stats', [
            'methods'             => 'GET',
            'callback'            => [$this, 'rest_get_stats'],
            'permission_callback' => function ($request) {
                return $this->security->verify_rest_permission($request);
            },
        ]);
    }

    // ==================== AJAX HANDLERS ====================

    /**
     * AJAX: Generate article
     */
    public function ajax_generate_article() {
        $this->security->verify_ajax_request();

        $params = [
            'topic'              => $this->security->sanitize_textarea($_POST['topic'] ?? ''),
            'primary_keyword'    => $this->security->sanitize_text($_POST['primary_keyword'] ?? ''),
            'secondary_keywords' => $this->security->sanitize_array($_POST['secondary_keywords'] ?? []),
            'title'              => $this->security->sanitize_text($_POST['title'] ?? ''),
            'language'           => $this->security->sanitize_text($_POST['language'] ?? 'en'),
            'tone'               => $this->security->sanitize_text($_POST['tone'] ?? 'professional'),
            'content_type'       => $this->security->sanitize_text($_POST['content_type'] ?? 'blog'),
            'article_length'     => $this->security->sanitize_int($_POST['article_length'] ?? 1500),
            'search_intent'      => $this->security->sanitize_text($_POST['search_intent'] ?? 'informational'),
            'target_audience'    => $this->security->sanitize_text($_POST['target_audience'] ?? ''),
            'ai_provider'        => $this->security->sanitize_text($_POST['ai_provider'] ?? ''),
            'ai_model'           => $this->security->sanitize_text($_POST['ai_model'] ?? ''),
            'template_id'        => $this->security->sanitize_int($_POST['template_id'] ?? 0),
        ];

        if (empty($params['topic']) && empty($params['primary_keyword'])) {
            wp_send_json_error(['message' => __('Topic or primary keyword is required.', 'ai-seo-writer-pro')]);
        }

        if ($this->license->has_reached_limit()) {
            wp_send_json_error(['message' => __('Daily generation limit reached. Upgrade to Pro for unlimited generations.', 'ai-seo-writer-pro')]);
        }

        $result = $this->article_generator->generate($params);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Save article as draft
     */
    public function ajax_save_article() {
        $this->security->verify_ajax_request();

        $data = [
            'title'            => $this->security->sanitize_text($_POST['title'] ?? ''),
            'content'          => $this->security->sanitize_html($_POST['content'] ?? ''),
            'seo_title'        => $this->security->sanitize_text($_POST['seo_title'] ?? ''),
            'meta_description' => $this->security->sanitize_textarea($_POST['meta_description'] ?? ''),
            'status'           => 'draft',
        ];

        $result = $this->publishing_manager->save_post($data);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['post_id' => $result, 'edit_url' => get_edit_post_link($result, '')]);
    }

    /**
     * AJAX: Publish article
     */
    public function ajax_publish_article() {
        $this->security->verify_ajax_request();

        $data = [
            'title'            => $this->security->sanitize_text($_POST['title'] ?? ''),
            'content'          => $this->security->sanitize_html($_POST['content'] ?? ''),
            'seo_title'        => $this->security->sanitize_text($_POST['seo_title'] ?? ''),
            'meta_description' => $this->security->sanitize_textarea($_POST['meta_description'] ?? ''),
            'status'           => 'publish',
            'category_ids'     => $this->security->sanitize_array($_POST['category_ids'] ?? []),
            'tags_input'       => $this->security->sanitize_array($_POST['tags'] ?? []),
        ];

        $result = $this->publishing_manager->save_post($data);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['post_id' => $result, 'edit_url' => get_edit_post_link($result, ''), 'view_url' => get_permalink($result)]);
    }

    /**
     * AJAX: Analyze SEO
     */
    public function ajax_analyze_seo() {
        $this->security->verify_ajax_request();

        $content = $this->security->sanitize_html($_POST['content'] ?? '');
        $keyword = $this->security->sanitize_text($_POST['keyword'] ?? '');
        $title   = $this->security->sanitize_text($_POST['title'] ?? '');

        if (empty($content)) {
            wp_send_json_error(['message' => __('Content is required for analysis.', 'ai-seo-writer-pro')]);
        }

        $result = $this->seo_analyzer->analyze($content, $keyword, $title);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Upload CSV for bulk generation
     */
    public function ajax_upload_csv() {
        $this->security->verify_ajax_request();

        if (!$this->license->is_feature_available('bulk_generation')) {
            wp_send_json_error(['message' => $this->license->get_upgrade_message('bulk_generation')]);
        }

        if (empty($_FILES['csv_file'])) {
            wp_send_json_error(['message' => __('Please upload a CSV file.', 'ai-seo-writer-pro')]);
        }

        $result = $this->bulk_generator->process_csv_upload($_FILES['csv_file']);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Get bulk generation progress
     */
    public function ajax_get_bulk_progress() {
        $this->security->verify_ajax_request();

        $batch_id = $this->security->sanitize_text($_POST['batch_id'] ?? '');
        $progress = $this->bulk_generator->get_batch_progress($batch_id);

        wp_send_json_success($progress);
    }

    /**
     * AJAX: Pause bulk generation
     */
    public function ajax_pause_bulk() {
        $this->security->verify_ajax_request();

        $batch_id = $this->security->sanitize_text($_POST['batch_id'] ?? '');
        $result = $this->bulk_generator->pause_batch($batch_id);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Batch paused.', 'ai-seo-writer-pro')]);
    }

    /**
     * AJAX: Resume bulk generation
     */
    public function ajax_resume_bulk() {
        $this->security->verify_ajax_request();

        $batch_id = $this->security->sanitize_text($_POST['batch_id'] ?? '');
        $result = $this->bulk_generator->resume_batch($batch_id);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Batch resumed.', 'ai-seo-writer-pro')]);
    }

    /**
     * AJAX: Cancel bulk generation
     */
    public function ajax_cancel_bulk() {
        $this->security->verify_ajax_request();

        $batch_id = $this->security->sanitize_text($_POST['batch_id'] ?? '');
        $result = $this->bulk_generator->cancel_batch($batch_id);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Batch cancelled.', 'ai-seo-writer-pro')]);
    }

    /**
     * AJAX: Generate image
     */
    public function ajax_generate_image() {
        $this->security->verify_ajax_request();

        if (!$this->license->is_feature_available('ai_image_generation')) {
            wp_send_json_error(['message' => $this->license->get_upgrade_message('ai_image_generation')]);
        }

        $params = [
            'prompt' => $this->security->sanitize_textarea($_POST['prompt'] ?? ''),
            'size'   => $this->security->sanitize_text($_POST['size'] ?? '1024x1024'),
            'style'  => $this->security->sanitize_text($_POST['style'] ?? 'vivid'),
            'n'      => $this->security->sanitize_int($_POST['n'] ?? 1),
        ];

        if (empty($params['prompt'])) {
            wp_send_json_error(['message' => __('Image prompt is required.', 'ai-seo-writer-pro')]);
        }

        $result = $this->image_generator->generate($params);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Upload image to media library
     */
    public function ajax_upload_to_media() {
        $this->security->verify_ajax_request();

        $image_url = $this->security->sanitize_url($_POST['image_url'] ?? '');
        $title     = $this->security->sanitize_text($_POST['title'] ?? 'AI Generated Image');

        if (empty($image_url)) {
            wp_send_json_error(['message' => __('Image URL is required.', 'ai-seo-writer-pro')]);
        }

        $result = $this->image_generator->upload_to_media_library($image_url, $title);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Save template
     */
    public function ajax_save_template() {
        $this->security->verify_ajax_request();

        $data = [
            'id'               => $this->security->sanitize_int($_POST['id'] ?? 0),
            'name'             => $this->security->sanitize_text($_POST['name'] ?? ''),
            'description'      => $this->security->sanitize_textarea($_POST['description'] ?? ''),
            'category'         => $this->security->sanitize_text($_POST['category'] ?? 'custom'),
            'prompt_template'  => $this->security->sanitize_textarea($_POST['prompt_template'] ?? ''),
            'system_prompt'    => $this->security->sanitize_textarea($_POST['system_prompt'] ?? ''),
            'user_id'          => get_current_user_id(),
        ];

        if (empty($data['name']) || empty($data['prompt_template'])) {
            wp_send_json_error(['message' => __('Name and prompt template are required.', 'ai-seo-writer-pro')]);
        }

        if ($data['id'] > 0) {
            $result = $this->database->update_template($data['id'], $data);
        } else {
            $data['slug']   = sanitize_title($data['name']);
            $data['status'] = 'active';
            $result = $this->database->insert_template($data);
        }

        if (!$result) {
            wp_send_json_error(['message' => __('Failed to save template.', 'ai-seo-writer-pro')]);
        }

        wp_send_json_success(['message' => __('Template saved successfully.', 'ai-seo-writer-pro'), 'id' => $data['id'] ?: $result]);
    }

    /**
     * AJAX: Delete template
     */
    public function ajax_delete_template() {
        $this->security->verify_ajax_request();

        $id = $this->security->sanitize_int($_POST['id'] ?? 0);

        if (!$this->database->delete_template($id)) {
            wp_send_json_error(['message' => __('Failed to delete template.', 'ai-seo-writer-pro')]);
        }

        wp_send_json_success(['message' => __('Template deleted.', 'ai-seo-writer-pro')]);
    }

    /**
     * AJAX: Get template
     */
    public function ajax_get_template() {
        $this->security->verify_ajax_request();

        $id = $this->security->sanitize_int($_POST['id'] ?? 0);
        $template = $this->database->get_template($id);

        if (!$template) {
            wp_send_json_error(['message' => __('Template not found.', 'ai-seo-writer-pro')]);
        }

        wp_send_json_success($template);
    }

    /**
     * AJAX: Get history
     */
    public function ajax_get_history() {
        $this->security->verify_ajax_request();

        $args = [
            'user_id' => get_current_user_id(),
            'status'  => $this->security->sanitize_text($_POST['status'] ?? ''),
            'limit'   => $this->security->sanitize_int($_POST['limit'] ?? 20),
            'offset'  => $this->security->sanitize_int($_POST['offset'] ?? 0),
            'orderby' => $this->security->sanitize_text($_POST['orderby'] ?? 'created_at'),
            'order'   => $this->security->sanitize_text($_POST['order'] ?? 'DESC'),
        ];

        $history = $this->database->get_history($args);

        wp_send_json_success(['items' => $history]);
    }

    /**
     * AJAX: Delete history item
     */
    public function ajax_delete_history_item() {
        $this->security->verify_ajax_request();

        $id = $this->security->sanitize_int($_POST['id'] ?? 0);

        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_history';
        $result = $wpdb->delete($table, ['id' => $id], ['%d']);

        if (!$result) {
            wp_send_json_error(['message' => __('Failed to delete item.', 'ai-seo-writer-pro')]);
        }

        wp_send_json_success(['message' => __('Item deleted.', 'ai-seo-writer-pro')]);
    }

    /**
     * AJAX: Get scheduled articles
     */
    public function ajax_get_scheduled() {
        $this->security->verify_ajax_request();

        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_queue';
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE status IN (%s, %s) AND scheduled_at IS NOT NULL ORDER BY scheduled_at ASC LIMIT %d",
                'pending',
                'paused',
                50
            ),
            ARRAY_A
        );

        wp_send_json_success(['items' => $items]);
    }

    /**
     * AJAX: Cancel scheduled article
     */
    public function ajax_cancel_scheduled() {
        $this->security->verify_ajax_request();

        $id = $this->security->sanitize_int($_POST['id'] ?? 0);
        $result = $this->database->update_queue_item($id, ['status' => 'cancelled']);

        if (!$result) {
            wp_send_json_error(['message' => __('Failed to cancel scheduled item.', 'ai-seo-writer-pro')]);
        }

        wp_send_json_success(['message' => __('Scheduled item cancelled.', 'ai-seo-writer-pro')]);
    }

    /**
     * AJAX: Save API key
     */
    public function ajax_save_api_key() {
        $this->security->verify_ajax_request();

        $provider = $this->security->sanitize_text($_POST['provider'] ?? '');
        $key      = $this->security->sanitize_text($_POST['api_key'] ?? '');
        $model    = $this->security->sanitize_text($_POST['model'] ?? '');

        if (empty($provider)) {
            wp_send_json_error(['message' => __('Provider is required.', 'ai-seo-writer-pro')]);
        }

        $this->settings->save_api_key($provider, $key);

        if (!empty($model)) {
            $settings = $this->settings->get_settings();
            $settings['default_model'] = $model;
            $this->settings->update_settings($settings);
        }

        wp_send_json_success([
            'message' => __('API key saved successfully.', 'ai-seo-writer-pro'),
            'masked_key' => $this->settings->get_masked_api_key($provider),
        ]);
    }

    /**
     * AJAX: Test API connection
     */
    public function ajax_test_api_connection() {
        $this->security->verify_ajax_request();

        $provider = $this->security->sanitize_text($_POST['provider'] ?? '');

        if (empty($provider)) {
            wp_send_json_error(['message' => __('Provider is required.', 'ai-seo-writer-pro')]);
        }

        $result = $this->ai_providers->test_connection($provider);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Connection successful!', 'ai-seo-writer-pro')]);
    }

    /**
     * AJAX: Save general settings
     */
    public function ajax_save_general_settings() {
        $this->security->verify_ajax_request();

        $input = $_POST['settings'] ?? [];
        $sanitized = $this->settings->sanitize_settings($input);
        $result = $this->settings->update_settings($sanitized);

        if (!$result) {
            wp_send_json_error(['message' => __('Failed to save settings.', 'ai-seo-writer-pro')]);
        }

        wp_send_json_success(['message' => __('Settings saved successfully.', 'ai-seo-writer-pro')]);
    }

    /**
     * AJAX: Activate license
     */
    public function ajax_activate_license() {
        $this->security->verify_ajax_request();

        $license_key = $this->security->sanitize_text($_POST['license_key'] ?? '');
        $api_url     = $this->security->sanitize_url($_POST['api_url'] ?? '');

        if (empty($license_key)) {
            wp_send_json_error(['message' => __('License key is required.', 'ai-seo-writer-pro')]);
        }

        $result = $this->license->activate_license($license_key, $api_url);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success($result);
    }

    /**
     * AJAX: Get dashboard stats
     */
    public function ajax_get_dashboard_stats() {
        $this->security->verify_ajax_request();

        $user_id = get_current_user_id();
        $counts  = $this->database->get_dashboard_counts($user_id);

        // Monthly stats (last 30 days)
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date   = current_time('Y-m-d');
        $monthly    = $this->database->get_stats_range($user_id, $start_date, $end_date);

        // Recent articles
        $recent = $this->database->get_history([
            'user_id' => $user_id,
            'limit'   => 5,
        ]);

        wp_send_json_success([
            'counts'  => $counts,
            'monthly' => $monthly,
            'recent'  => $recent,
        ]);
    }

    // ==================== REST HANDLERS ====================

    /**
     * REST: Generate article
     */
    public function rest_generate_article(\WP_REST_Request $request) {
        $params = $request->get_params();
        $result = $this->article_generator->generate($params);

        if (is_wp_error($result)) {
            return new \WP_Error('generation_failed', $result->get_error_message(), ['status' => 500]);
        }

        return new \WP_REST_Response($result);
    }

    /**
     * REST: Analyze SEO
     */
    public function rest_analyze_seo(\WP_REST_Request $request) {
        $content = $request->get_param('content');
        $keyword = $request->get_param('keyword');
        $title   = $request->get_param('title');

        $result = $this->seo_analyzer->analyze($content, $keyword, $title);

        if (is_wp_error($result)) {
            return new \WP_Error('analysis_failed', $result->get_error_message(), ['status' => 500]);
        }

        return new \WP_REST_Response($result);
    }

    /**
     * REST: Get history
     */
    public function rest_get_history(\WP_REST_Request $request) {
        $args = [
            'user_id' => get_current_user_id(),
            'limit'   => $request->get_param('limit') ?? 20,
            'offset'  => $request->get_param('offset') ?? 0,
        ];

        return new \WP_REST_Response(['items' => $this->database->get_history($args)]);
    }

    /**
     * REST: Get templates
     */
    public function rest_get_templates(\WP_REST_Request $request) {
        $templates = $this->database->get_templates(['user_id' => get_current_user_id()]);
        return new \WP_REST_Response(['items' => $templates]);
    }

    /**
     * REST: Get stats
     */
    public function rest_get_stats(\WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $counts  = $this->database->get_dashboard_counts($user_id);
        return new \WP_REST_Response($counts);
    }

    // ==================== RENDER METHODS ====================

    /**
     * Get the current page slug
     *
     * @return string
     */
    private function get_current_page() {
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'ai-seo-writer-pro';
        return str_replace('ai-seo-writer-pro-', '', str_replace('ai-seo-writer-pro', 'overview', $page));
    }

    /**
     * Render page wrapper header
     *
     * @param string $title
     */
    private function render_page_header($title) {
        $current_page = $this->get_current_page();
        ?>
        <div class="ai-seo-writer-pro-wrap">
            <div class="ai-seo-writer-pro-header">
                <div class="ai-seo-writer-pro-header-inner">
                    <div class="ai-seo-writer-pro-logo">
                        <span class="ai-seo-writer-pro-logo-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="32" height="32" rx="8" fill="#1e293b"/>
                                <path d="M10 22L16 10L22 22H10Z" fill="#38bdf8"/>
                                <circle cx="16" cy="18" r="2" fill="#1e293b"/>
                            </svg>
                        </span>
                        <div class="ai-seo-writer-pro-logo-text">
                            <h1><?php echo esc_html($title); ?></h1>
                            <span class="ai-seo-writer-pro-version">v<?php echo esc_html(AI_SEO_WRITER_PRO_VERSION); ?></span>
                        </div>
                    </div>
                    <div class="ai-seo-writer-pro-header-actions">
                        <?php if (!$this->license->is_pro()) : ?>
                            <a href="#" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-sm">
                                <?php esc_html_e('Upgrade to Pro', 'ai-seo-writer-pro'); ?>
                            </a>
                        <?php else : ?>
                            <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success">
                                <?php esc_html_e('Pro Active', 'ai-seo-writer-pro'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="ai-seo-writer-pro-body">
                <nav class="ai-seo-writer-pro-sidebar">
                    <?php $this->render_sidebar_nav($current_page); ?>
                </nav>
                <div class="ai-seo-writer-pro-content">
        <?php
    }

    /**
     * Render page wrapper footer
     */
    private function render_page_footer() {
        ?>
                </div><!-- .ai-seo-writer-pro-content -->
            </div><!-- .ai-seo-writer-pro-body -->
        </div><!-- .ai-seo-writer-pro-wrap -->
        <?php
    }

    /**
     * Render sidebar navigation
     *
     * @param string $current_page
     */
    private function render_sidebar_nav($current_page) {
        $nav_items = [
            'overview' => [
                'label' => __('Overview', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro'),
            ],
            'article-generator' => [
                'label' => __('AI Article Generator', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-article-generator'),
            ],
            'bulk-generator' => [
                'label' => __('Bulk Generator', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-bulk-generator'),
                'pro'   => true,
            ],
            'seo-analyzer' => [
                'label' => __('SEO Analyzer', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-seo-analyzer'),
            ],
            'content-templates' => [
                'label' => __('Content Templates', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-content-templates'),
            ],
            'image-generator' => [
                'label' => __('AI Image Generator', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-image-generator'),
                'pro'   => true,
            ],
            'article-history' => [
                'label' => __('Article History', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-article-history'),
            ],
            'scheduled-articles' => [
                'label' => __('Scheduled Articles', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-scheduled-articles'),
            ],
            'api-settings' => [
                'label' => __('API Settings', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a2 2 0 01-2 2H4a2 2 0 01-2-2V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-api-settings'),
            ],
            'usage-credits' => [
                'label' => __('Usage & Credits', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-usage-credits'),
            ],
            'general-settings' => [
                'label' => __('General Settings', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-general-settings'),
            ],
            'documentation' => [
                'label' => __('Documentation', 'ai-seo-writer-pro'),
                'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
                'url'   => admin_url('admin.php?page=ai-seo-writer-pro-documentation'),
            ],
        ];

        echo '<ul class="ai-seo-writer-pro-nav">';
        foreach ($nav_items as $slug => $item) {
            $active = ($slug === $current_page) ? ' active' : '';
            $pro_badge = !empty($item['pro']) && !$this->license->is_pro()
                ? ' <span class="ai-seo-writer-pro-nav-pro">PRO</span>'
                : '';
            printf(
                '<li class="ai-seo-writer-pro-nav-item%s"><a href="%s" class="ai-seo-writer-pro-nav-link">%s<span>%s%s</span></a></li>',
                esc_attr($active),
                esc_url($item['url']),
                $item['icon'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                esc_html($item['label']),
                $pro_badge // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            );
        }
        echo '</ul>';
    }

    /**
     * Load a view file
     *
     * @param string $view
     * @param array  $data
     */
    private function load_view($view, $data = []) {
        $view_file = AI_SEO_WRITER_PRO_PLUGIN_DIR . 'admin/views/' . $view . '.php';

        if (!file_exists($view_file)) {
            printf(
                '<div class="ai-seo-writer-pro-alert ai-seo-writer-pro-alert-error">%s</div>',
                esc_html(sprintf(__('View file not found: %s', 'ai-seo-writer-pro'), $view))
            );
            return;
        }

        extract($data, EXTR_SKIP);
        include $view_file;
    }

    /**
     * Render Overview page
     */
    public function render_overview() {
        $this->render_page_header(__('Overview', 'ai-seo-writer-pro'));
        $user_id = get_current_user_id();
        $counts  = $this->database->get_dashboard_counts($user_id);
        $recent  = $this->database->get_history(['user_id' => $user_id, 'limit' => 5]);
        $this->load_view('overview', [
            'counts' => $counts,
            'recent' => $recent,
            'security' => $this->security,
            'license' => $this->license,
        ]);
        $this->render_page_footer();
    }

    /**
     * Render Article Generator page
     */
    public function render_article_generator() {
        $this->render_page_header(__('AI Article Generator', 'ai-seo-writer-pro'));
        $settings = $this->settings->get_settings();
        $this->load_view('article-generator', [
            'settings'   => $settings,
            'settings_mgr' => $this->settings,
            'security'   => $this->security,
            'ai_providers' => $this->ai_providers,
            'license'    => $this->license,
        ]);
        $this->render_page_footer();
    }

    /**
     * Render Bulk Generator page
     */
    public function render_bulk_generator() {
        $this->render_page_header(__('Bulk Generator', 'ai-seo-writer-pro'));
        $this->load_view('bulk-generator', [
            'security' => $this->security,
            'license'  => $this->license,
        ]);
        $this->render_page_footer();
    }

    /**
     * Render SEO Analyzer page
     */
    public function render_seo_analyzer() {
        $this->render_page_header(__('SEO Analyzer', 'ai-seo-writer-pro'));
        $this->load_view('seo-analyzer', [
            'security' => $this->security,
        ]);
        $this->render_page_footer();
    }

    /**
     * Render Content Templates page
     */
    public function render_content_templates() {
        $this->render_page_header(__('Content Templates', 'ai-seo-writer-pro'));
        $templates = $this->database->get_templates(['user_id' => get_current_user_id()]);
        $this->load_view('content-templates', [
            'templates' => $templates,
            'security'  => $this->security,
            'built_in'  => $this->content_templates->get_built_in_templates(),
        ]);
        $this->render_page_footer();
    }

    /**
     * Render Image Generator page
     */
    public function render_image_generator() {
        $this->render_page_header(__('AI Image Generator', 'ai-seo-writer-pro'));
        $this->load_view('image-generator', [
            'security' => $this->security,
            'license'  => $this->license,
        ]);
        $this->render_page_footer();
    }

    /**
     * Render Article History page
     */
    public function render_article_history() {
        $this->render_page_header(__('Article History', 'ai-seo-writer-pro'));
        $this->load_view('article-history', [
            'security' => $this->security,
        ]);
        $this->render_page_footer();
    }

    /**
     * Render Scheduled Articles page
     */
    public function render_scheduled_articles() {
        $this->render_page_header(__('Scheduled Articles', 'ai-seo-writer-pro'));
        $this->load_view('scheduled-articles', [
            'security' => $this->security,
        ]);
        $this->render_page_footer();
    }

    /**
     * Render API Settings page
     */
    public function render_api_settings() {
        $this->render_page_header(__('API Settings', 'ai-seo-writer-pro'));
        $providers = $this->ai_providers->get_available_providers();
        $this->load_view('api-settings', [
            'providers' => $providers,
            'settings'  => $this->settings,
            'security'  => $this->security,
        ]);
        $this->render_page_footer();
    }

    /**
     * Render Usage & Credits page
     */
    public function render_usage_credits() {
        $this->render_page_header(__('Usage & Credits', 'ai-seo-writer-pro'));
        $user_id = get_current_user_id();
        $counts  = $this->database->get_dashboard_counts($user_id);
        $this->load_view('usage-credits', [
            'counts'  => $counts,
            'license' => $this->license,
            'security' => $this->security,
        ]);
        $this->render_page_footer();
    }

    /**
     * Render General Settings page
     */
    public function render_general_settings() {
        $this->render_page_header(__('General Settings', 'ai-seo-writer-pro'));
        $settings = $this->settings->get_settings();
        $this->load_view('general-settings', [
            'settings'   => $settings,
            'settings_mgr' => $this->settings,
            'security'   => $this->security,
            'license'    => $this->license,
        ]);
        $this->render_page_footer();
    }

    /**
     * Render Documentation page
     */
    public function render_documentation() {
        $this->render_page_header(__('Documentation', 'ai-seo-writer-pro'));
        $this->load_view('documentation', [
            'security' => $this->security,
        ]);
        $this->render_page_footer();
    }
}

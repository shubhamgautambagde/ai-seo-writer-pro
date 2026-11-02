<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Bulk Generator - Handles bulk article generation via CSV import and WP-Cron queue processing
 */
class Bulk_Generator {

    /**
     * Article Generator instance
     * @var Article_Generator
     */
    private $article_generator;

    /**
     * Publishing Manager instance
     * @var Publishing_Manager
     */
    private $publishing_manager;

    /**
     * Database Manager instance
     * @var Database_Manager
     */
    private $database_manager;

    /**
     * Security Manager instance
     * @var Security_Manager
     */
    private $security_manager;

    /**
     * Cron hook name
     * @var string
     */
    const CRON_HOOK = 'ai_seo_writer_pro_bulk_queue_cron';

    /**
     * Constructor
     * @param Article_Generator $article_generator
     * @param Publishing_Manager $publishing_manager
     */
    public function __construct($article_generator, $publishing_manager) {
        $this->article_generator = $article_generator;
        $this->publishing_manager = $publishing_manager;
        $this->database_manager = new Database_Manager();
        $this->security_manager = new Security_Manager();
    }

    /**
     * Initialize hooks and AJAX handlers
     */
    public function init() {
        // WP-Cron action
        add_action(self::CRON_HOOK, [$this, 'cron_handler']);

        // Ensure cron event is scheduled
        add_action('wp', [$this, 'maybe_schedule_cron']);

        // AJAX handlers
        add_action('wp_ajax_ai_seo_writer_pro_upload_csv', [$this, 'ajax_upload_csv']);
        add_action('wp_ajax_ai_seo_writer_pro_pause_queue', [$this, 'ajax_pause_queue']);
        add_action('wp_ajax_ai_seo_writer_pro_resume_queue', [$this, 'ajax_resume_queue']);
        add_action('wp_ajax_ai_seo_writer_pro_cancel_queue', [$this, 'ajax_cancel_queue']);
        add_action('wp_ajax_ai_seo_writer_pro_get_queue_status', [$this, 'ajax_get_queue_status']);
        add_action('wp_ajax_ai_seo_writer_pro_get_batches', [$this, 'ajax_get_batches']);
        add_action('wp_ajax_ai_seo_writer_pro_process_queue_item', [$this, 'ajax_process_queue_item']);
    }

    /**
     * Ensure cron event is scheduled
     */
    public function maybe_schedule_cron() {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'ai_seo_writer_pro_one_minute', self::CRON_HOOK);
        }
    }

    /**
     * Import CSV file and parse into queue items
     *
     * Expected CSV columns:
     * topic, primary_keyword, secondary_keywords, target_audience, search_intent,
     * language, article_length, tone, content_type, competitor_url, categories, tags
     *
     * @param string $file_path Absolute path to CSV file
     * @return array|\WP_Error Array of parsed items or WP_Error
     */
    public function import_csv($file_path) {
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return new \WP_Error(
                'file_not_found',
                __('CSV file not found or not readable.', 'ai-seo-writer-pro')
            );
        }

        $handle = fopen($file_path, 'r');
        if ($handle === false) {
            return new \WP_Error(
                'file_open_failed',
                __('Failed to open CSV file.', 'ai-seo-writer-pro')
            );
        }

        $items = [];
        $headers = fgetcsv($handle);

        if ($headers === false || empty($headers)) {
            fclose($handle);
            return new \WP_Error(
                'invalid_csv',
                __('CSV file is empty or has no header row.', 'ai-seo-writer-pro')
            );
        }

        // Normalize headers
        $headers = array_map(function ($h) {
            return strtolower(trim($h));
        }, $headers);

        $required_columns = ['topic', 'primary_keyword'];
        foreach ($required_columns as $col) {
            if (!in_array($col, $headers, true)) {
                fclose($handle);
                return new \WP_Error(
                    'missing_column',
                    sprintf(__('Missing required CSV column: %s', 'ai-seo-writer-pro'), $col)
                );
            }
        }

        $row = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $row++;

            // Skip empty rows
            if (count(array_filter($data)) === 0) {
                continue;
            }

            $item = [];
            foreach ($headers as $index => $header) {
                $value = isset($data[$index]) ? $data[$index] : '';
                $item[$header] = $this->sanitize_csv_field($header, $value);
            }

            // Validate required fields
            if (empty($item['topic'])) {
                $this->security_manager->log_error(
                    sprintf('CSV row %d skipped: empty topic', $row),
                    ['row' => $row]
                );
                continue;
            }

            // Set defaults for missing fields
            $item = wp_parse_args($item, [
                'topic' => '',
                'primary_keyword' => '',
                'secondary_keywords' => '',
                'target_audience' => '',
                'search_intent' => 'informational',
                'language' => 'en',
                'article_length' => 1500,
                'tone' => 'professional',
                'content_type' => 'blog',
                'competitor_url' => '',
                'categories' => '',
                'tags' => '',
            ]);

            $items[] = $item;
        }

        fclose($handle);

        if (empty($items)) {
            return new \WP_Error(
                'no_valid_items',
                __('No valid items found in CSV file.', 'ai-seo-writer-pro')
            );
        }

        return $items;
    }

    /**
     * Sanitize individual CSV field based on column name
     * @param string $header
     * @param string $value
     * @return mixed
     */
    private function sanitize_csv_field($header, $value) {
        $value = trim($value);

        switch ($header) {
            case 'article_length':
                return absint($value) ?: 1500;

            case 'secondary_keywords':
            case 'categories':
            case 'tags':
                if (empty($value)) {
                    return '';
                }
                // Handle both comma-separated and pipe-separated values
                $keywords = preg_split('/[,|]/', $value);
                $keywords = array_map('trim', $keywords);
                $keywords = array_filter($keywords);
                return implode(',', $keywords);

            case 'competitor_url':
                return esc_url_raw($value);

            case 'language':
                return sanitize_text_field(substr($value, 0, 10));

            case 'search_intent':
                $allowed = ['informational', 'navigational', 'commercial', 'transactional'];
                return in_array($value, $allowed, true) ? $value : 'informational';

            case 'tone':
            case 'content_type':
                return sanitize_text_field($value);

            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Add items to the processing queue
     *
     * @param array $items Array of items from import_csv
     * @param string $batch_id Batch identifier
     * @return array|\WP_Error Result with counts or WP_Error
     */
    public function add_to_queue($items, $batch_id) {
        if (empty($items) || !is_array($items)) {
            return new \WP_Error(
                'empty_items',
                __('No items provided to add to queue.', 'ai-seo-writer-pro')
            );
        }

        if (empty($batch_id)) {
            $batch_id = 'batch_' . time() . '_' . wp_generate_password(8, false);
        }

        $user_id = get_current_user_id();
        $added = 0;
        $failed = 0;
        $settings = new Settings_Manager();
        $settings_data = $settings->get_settings();

        foreach ($items as $item) {
            $queue_data = [
                'batch_id' => $batch_id,
                'user_id' => $user_id,
                'title' => $item['topic'],
                'topic' => $item['topic'],
                'primary_keyword' => $item['primary_keyword'],
                'secondary_keywords' => $item['secondary_keywords'],
                'target_audience' => $item['target_audience'],
                'search_intent' => $item['search_intent'],
                'language' => $item['language'],
                'article_length' => $item['article_length'],
                'tone' => $item['tone'],
                'content_type' => $item['content_type'],
                'competitor_url' => $item['competitor_url'],
                'ai_provider' => $settings_data['default_ai_provider'],
                'ai_model' => $settings_data['default_model'],
                'category_ids' => $item['categories'],
                'tags' => $item['tags'],
                'status' => 'pending',
                'priority' => 10,
                'attempts' => 0,
                'max_attempts' => 3,
                'created_at' => current_time('mysql'),
            ];

            $result = $this->database_manager->insert_queue_item($queue_data);

            if ($result) {
                $added++;
            } else {
                $failed++;
                $this->security_manager->log_error(
                    'Failed to insert queue item',
                    ['item' => $item, 'batch_id' => $batch_id]
                );
            }
        }

        return [
            'batch_id' => $batch_id,
            'added' => $added,
            'failed' => $failed,
            'total' => count($items),
        ];
    }

    /**
     * Process a single queue item
     *
     * IMPORTANT: This method processes ONLY ONE item per call.
     * It is designed to be called by WP-Cron which runs at regular intervals.
     * NEVER loop through all items here.
     *
     * @return bool True if an item was processed, false if no pending items
     */
    public function process_queue_item() {
        // Get next pending item
        $item = $this->database_manager->get_next_queue_item();

        if (!$item) {
            return false;
        }

        // Mark as processing
        $this->database_manager->update_queue_item($item['id'], [
            'status' => 'processing',
            'started_at' => current_time('mysql'),
            'attempts' => (int) $item['attempts'] + 1,
        ]);

        try {
            // Prepare generation parameters
            $secondary_keywords = !empty($item['secondary_keywords'])
                ? explode(',', $item['secondary_keywords'])
                : [];

            $generation_params = [
                'topic' => $item['topic'],
                'primary_keyword' => $item['primary_keyword'],
                'secondary_keywords' => $secondary_keywords,
                'target_audience' => $item['target_audience'],
                'search_intent' => $item['search_intent'],
                'language' => $item['language'],
                'article_length' => (int) $item['article_length'],
                'tone' => $item['tone'],
                'content_type' => $item['content_type'],
                'competitor_url' => $item['competitor_url'],
                'ai_provider' => $item['ai_provider'],
                'ai_model' => $item['ai_model'],
            ];

            // Generate article
            $result = $this->article_generator->generate_article($generation_params);

            if (is_wp_error($result)) {
                throw new \Exception($result->get_error_message());
            }

            if (empty($result) || empty($result['content'])) {
                throw new \Exception(__('Empty article generated.', 'ai-seo-writer-pro'));
            }

            // Prepare article data for publishing
            $article_data = [
                'title' => $result['title'] ?? $item['topic'],
                'content' => $result['content'],
                'excerpt' => $result['excerpt'] ?? '',
                'seo_title' => $result['seo_title'] ?? '',
                'meta_description' => $result['meta_description'] ?? '',
                'focus_keyword' => $item['primary_keyword'],
                'categories' => $this->parse_category_ids($item['category_ids']),
                'tags' => !empty($item['tags']) ? explode(',', $item['tags']) : [],
                'featured_image_url' => $result['featured_image_url'] ?? '',
            ];

            // Save as draft
            $post_id = $this->publishing_manager->save_as_draft($article_data);

            if (is_wp_error($post_id)) {
                throw new \Exception($post_id->get_error_message());
            }

            // Mark as completed
            $this->database_manager->update_queue_item($item['id'], [
                'status' => 'completed',
                'post_id' => $post_id,
                'completed_at' => current_time('mysql'),
                'error_message' => null,
            ]);

            // Log to history
            $this->database_manager->insert_history([
                'user_id' => $item['user_id'],
                'post_id' => $post_id,
                'title' => $article_data['title'],
                'topic' => $item['topic'],
                'primary_keyword' => $item['primary_keyword'],
                'secondary_keywords' => $item['secondary_keywords'],
                'content' => maybe_serialize($result),
                'seo_title' => $article_data['seo_title'],
                'meta_description' => $article_data['meta_description'],
                'ai_provider' => $item['ai_provider'],
                'ai_model' => $item['ai_model'],
                'tokens_used' => $result['tokens_used'] ?? 0,
                'content_type' => $item['content_type'],
                'language' => $item['language'],
                'tone' => $item['tone'],
                'word_count' => str_word_count(wp_strip_all_tags($result['content'])),
                'seo_score' => $result['seo_score'] ?? null,
                'readability_score' => $result['readability_score'] ?? null,
                'quality_score' => $result['quality_score'] ?? null,
                'status' => 'draft',
                'created_at' => current_time('mysql'),
            ]);

            return true;

        } catch (\Exception $e) {
            $attempts = (int) $item['attempts'] + 1;
            $max_attempts = (int) $item['max_attempts'];

            $error_message = $e->getMessage();
            $this->security_manager->log_error(
                sprintf('Queue item %d processing failed (attempt %d/%d): %s', $item['id'], $attempts, $max_attempts, $error_message),
                ['item_id' => $item['id'], 'attempts' => $attempts]
            );

            if ($attempts >= $max_attempts) {
                // Mark as failed
                $this->database_manager->update_queue_item($item['id'], [
                    'status' => 'failed',
                    'error_message' => $error_message,
                    'completed_at' => current_time('mysql'),
                ]);
            } else {
                // Mark back to pending for retry
                $this->database_manager->update_queue_item($item['id'], [
                    'status' => 'pending',
                    'error_message' => $error_message,
                ]);
            }

            return false;
        }
    }

    /**
     * Parse category IDs from stored string
     * @param string $category_ids_str
     * @return array
     */
    private function parse_category_ids($category_ids_str) {
        if (empty($category_ids_str)) {
            return [];
        }
        $ids = explode(',', $category_ids_str);
        return array_filter(array_map('absint', $ids));
    }

    /**
     * WP-Cron callback handler
     * Processes exactly ONE queue item per cron run
     */
    public function cron_handler() {
        // Prevent multiple concurrent executions
        $lock_key = 'ai_seo_writer_pro_queue_processing_lock';
        if (get_transient($lock_key)) {
            return;
        }
        set_transient($lock_key, true, 300); // Lock for 5 minutes max

        try {
            $this->process_queue_item();
        } finally {
            delete_transient($lock_key);
        }
    }

    /**
     * Pause all items in a batch
     * @param string $batch_id
     * @return bool
     */
    public function pause_queue($batch_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_queue';

        $result = $wpdb->query($wpdb->prepare(
            "UPDATE $table SET status = %s WHERE batch_id = %s AND status IN (%s, %s)",
            'paused',
            $batch_id,
            'pending',
            'processing'
        ));

        return $result !== false;
    }

    /**
     * Resume paused items in a batch
     * @param string $batch_id
     * @return bool
     */
    public function resume_queue($batch_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_queue';

        $result = $wpdb->query($wpdb->prepare(
            "UPDATE $table SET status = %s WHERE batch_id = %s AND status = %s",
            'pending',
            $batch_id,
            'paused'
        ));

        return $result !== false;
    }

    /**
     * Cancel a batch - mark items as cancelled
     * @param string $batch_id
     * @return bool
     */
    public function cancel_queue($batch_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_queue';

        $result = $wpdb->query($wpdb->prepare(
            "UPDATE $table SET status = %s WHERE batch_id = %s AND status NOT IN (%s, %s)",
            'cancelled',
            $batch_id,
            'completed',
            'failed'
        ));

        return $result !== false;
    }

    /**
     * Get queue status counts for a batch
     * @param string $batch_id
     * @return array
     */
    public function get_queue_status($batch_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_queue';

        $counts = [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'failed' => 0,
            'paused' => 0,
            'cancelled' => 0,
            'total' => 0,
        ];

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT status, COUNT(*) as cnt FROM $table WHERE batch_id = %s GROUP BY status",
            $batch_id
        ), ARRAY_A);

        foreach ($results as $row) {
            $status = $row['status'];
            if (isset($counts[$status])) {
                $counts[$status] = (int) $row['cnt'];
            }
            $counts['total'] += (int) $row['cnt'];
        }

        return $counts;
    }

    /**
     * Get all batches with their statistics
     * @return array
     */
    public function get_batches() {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_queue';

        $batches = $wpdb->get_results(
            "SELECT 
                batch_id,
                MIN(created_at) as created_at,
                MAX(completed_at) as last_completed,
                COUNT(*) as total_items,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'paused' THEN 1 ELSE 0 END) as paused,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM $table 
            GROUP BY batch_id 
            ORDER BY created_at DESC 
            LIMIT 50",
            ARRAY_A
        );

        return $batches;
    }

    // ==================== AJAX Handlers ====================

    /**
     * AJAX: Handle CSV upload
     */
    public function ajax_upload_csv() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        if (empty($_FILES['csv_file'])) {
            wp_send_json_error(['message' => __('No file uploaded.', 'ai-seo-writer-pro')]);
            return;
        }

        $file = $_FILES['csv_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => __('File upload error.', 'ai-seo-writer-pro')]);
            return;
        }

        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'csv') {
            wp_send_json_error(['message' => __('Only CSV files are allowed.', 'ai-seo-writer-pro')]);
            return;
        }

        $file_path = $file['tmp_name'];

        // Import CSV
        $items = $this->import_csv($file_path);

        if (is_wp_error($items)) {
            wp_send_json_error(['message' => $items->get_error_message()]);
            return;
        }

        // Generate batch ID
        $batch_id = isset($_POST['batch_id'])
            ? $this->security_manager->sanitize_text($_POST['batch_id'])
            : '';

        // Add to queue
        $result = $this->add_to_queue($items, $batch_id);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
            return;
        }

        wp_send_json_success([
            'message' => sprintf(
                __('%d items added to queue. %d failed.', 'ai-seo-writer-pro'),
                $result['added'],
                $result['failed']
            ),
            'batch_id' => $result['batch_id'],
            'added' => $result['added'],
            'failed' => $result['failed'],
            'total' => $result['total'],
        ]);
    }

    /**
     * AJAX: Pause queue
     */
    public function ajax_pause_queue() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $batch_id = isset($_POST['batch_id']) ? $this->security_manager->sanitize_text($_POST['batch_id']) : '';

        if (empty($batch_id)) {
            wp_send_json_error(['message' => __('Batch ID is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $result = $this->pause_queue($batch_id);

        if ($result) {
            wp_send_json_success(['message' => __('Queue paused.', 'ai-seo-writer-pro')]);
        } else {
            wp_send_json_error(['message' => __('Failed to pause queue.', 'ai-seo-writer-pro')]);
        }
    }

    /**
     * AJAX: Resume queue
     */
    public function ajax_resume_queue() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $batch_id = isset($_POST['batch_id']) ? $this->security_manager->sanitize_text($_POST['batch_id']) : '';

        if (empty($batch_id)) {
            wp_send_json_error(['message' => __('Batch ID is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $result = $this->resume_queue($batch_id);

        if ($result) {
            wp_send_json_success(['message' => __('Queue resumed.', 'ai-seo-writer-pro')]);
        } else {
            wp_send_json_error(['message' => __('Failed to resume queue.', 'ai-seo-writer-pro')]);
        }
    }

    /**
     * AJAX: Cancel queue
     */
    public function ajax_cancel_queue() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $batch_id = isset($_POST['batch_id']) ? $this->security_manager->sanitize_text($_POST['batch_id']) : '';

        if (empty($batch_id)) {
            wp_send_json_error(['message' => __('Batch ID is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $result = $this->cancel_queue($batch_id);

        if ($result) {
            wp_send_json_success(['message' => __('Queue cancelled.', 'ai-seo-writer-pro')]);
        } else {
            wp_send_json_error(['message' => __('Failed to cancel queue.', 'ai-seo-writer-pro')]);
        }
    }

    /**
     * AJAX: Get queue status
     */
    public function ajax_get_queue_status() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $batch_id = isset($_REQUEST['batch_id']) ? $this->security_manager->sanitize_text($_REQUEST['batch_id']) : '';

        if (empty($batch_id)) {
            wp_send_json_error(['message' => __('Batch ID is required.', 'ai-seo-writer-pro')]);
            return;
        }

        $status = $this->get_queue_status($batch_id);

        wp_send_json_success($status);
    }

    /**
     * AJAX: Get batches list
     */
    public function ajax_get_batches() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $batches = $this->get_batches();

        wp_send_json_success(['batches' => $batches]);
    }

    /**
     * AJAX: Manually trigger processing of one queue item
     */
    public function ajax_process_queue_item() {
        if (!$this->security_manager->verify_ajax_request('manage_options')) {
            return;
        }

        $result = $this->process_queue_item();

        if ($result) {
            wp_send_json_success(['message' => __('Item processed successfully.', 'ai-seo-writer-pro')]);
        } else {
            wp_send_json_success(['message' => __('No pending items to process.', 'ai-seo-writer-pro'), 'no_items' => true]);
        }
    }
}

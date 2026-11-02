<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database Manager - Handles custom database tables
 */
class Database_Manager {

    /**
     * Database version
     * @var string
     */
    private $db_version = '1.0.0';

    /**
     * Initialize
     */
    public function init() {
        add_action('plugins_loaded', [$this, 'maybe_upgrade']);
    }

    /**
     * Create all custom database tables
     */
    public function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Table: Generation History
        $table_history = $wpdb->prefix . 'ai_seo_writer_pro_history';
        $sql_history = "CREATE TABLE $table_history (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
            post_id bigint(20) UNSIGNED DEFAULT NULL,
            title text NOT NULL,
            topic text NOT NULL,
            primary_keyword varchar(255) DEFAULT '',
            secondary_keywords text DEFAULT NULL,
            content longtext DEFAULT NULL,
            seo_title varchar(255) DEFAULT '',
            meta_description text DEFAULT NULL,
            url_slug varchar(255) DEFAULT '',
            ai_provider varchar(50) NOT NULL DEFAULT '',
            ai_model varchar(100) NOT NULL DEFAULT '',
            tokens_used int(11) UNSIGNED NOT NULL DEFAULT 0,
            content_type varchar(50) NOT NULL DEFAULT 'blog',
            language varchar(10) NOT NULL DEFAULT 'en',
            tone varchar(50) NOT NULL DEFAULT 'professional',
            word_count int(11) UNSIGNED NOT NULL DEFAULT 0,
            seo_score decimal(5,2) DEFAULT NULL,
            readability_score decimal(5,2) DEFAULT NULL,
            quality_score decimal(5,2) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'draft',
            error_message text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY post_id (post_id),
            KEY status (status),
            KEY created_at (created_at),
            KEY ai_provider (ai_provider)
        ) $charset_collate;";
        dbDelta($sql_history);

        // Table: API Usage Logs
        $table_logs = $wpdb->prefix . 'ai_seo_writer_pro_api_logs';
        $sql_logs = "CREATE TABLE $table_logs (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
            ai_provider varchar(50) NOT NULL DEFAULT '',
            ai_model varchar(100) NOT NULL DEFAULT '',
            endpoint varchar(255) DEFAULT '',
            request_type varchar(50) NOT NULL DEFAULT '',
            prompt_tokens int(11) UNSIGNED NOT NULL DEFAULT 0,
            completion_tokens int(11) UNSIGNED NOT NULL DEFAULT 0,
            total_tokens int(11) UNSIGNED NOT NULL DEFAULT 0,
            response_time decimal(10,4) DEFAULT NULL,
            http_status int(11) DEFAULT NULL,
            is_error tinyint(1) NOT NULL DEFAULT 0,
            error_message text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY ai_provider (ai_provider),
            KEY created_at (created_at),
            KEY is_error (is_error)
        ) $charset_collate;";
        dbDelta($sql_logs);

        // Table: Article Generation Queue
        $table_queue = $wpdb->prefix . 'ai_seo_writer_pro_queue';
        $sql_queue = "CREATE TABLE $table_queue (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            batch_id varchar(50) NOT NULL DEFAULT '',
            user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
            title text NOT NULL,
            topic text NOT NULL,
            primary_keyword varchar(255) DEFAULT '',
            secondary_keywords text DEFAULT NULL,
            target_audience varchar(255) DEFAULT '',
            search_intent varchar(50) DEFAULT 'informational',
            language varchar(10) NOT NULL DEFAULT 'en',
            article_length int(11) UNSIGNED NOT NULL DEFAULT 1500,
            tone varchar(50) NOT NULL DEFAULT 'professional',
            content_type varchar(50) NOT NULL DEFAULT 'blog',
            competitor_url varchar(255) DEFAULT '',
            ai_provider varchar(50) NOT NULL DEFAULT '',
            ai_model varchar(100) NOT NULL DEFAULT '',
            category_ids text DEFAULT NULL,
            tags text DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            priority tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
            attempts tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
            max_attempts tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
            post_id bigint(20) UNSIGNED DEFAULT NULL,
            error_message text DEFAULT NULL,
            scheduled_at datetime DEFAULT NULL,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY batch_id (batch_id),
            KEY user_id (user_id),
            KEY status (status),
            KEY priority (priority),
            KEY scheduled_at (scheduled_at),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_queue);

        // Table: Custom AI Templates
        $table_templates = $wpdb->prefix . 'ai_seo_writer_pro_templates';
        $sql_templates = "CREATE TABLE $table_templates (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text DEFAULT NULL,
            category varchar(50) NOT NULL DEFAULT 'custom',
            prompt_template longtext NOT NULL,
            system_prompt text DEFAULT NULL,
            default_settings text DEFAULT NULL,
            is_global tinyint(1) NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY slug (slug),
            KEY category (category),
            KEY is_global (is_global),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql_templates);

        // Table: Usage Statistics
        $table_stats = $wpdb->prefix . 'ai_seo_writer_pro_stats';
        $sql_stats = "CREATE TABLE $table_stats (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
            stat_date date NOT NULL,
            articles_generated int(11) UNSIGNED NOT NULL DEFAULT 0,
            articles_published int(11) UNSIGNED NOT NULL DEFAULT 0,
            total_tokens int(11) UNSIGNED NOT NULL DEFAULT 0,
            total_words int(11) UNSIGNED NOT NULL DEFAULT 0,
            avg_seo_score decimal(5,2) DEFAULT NULL,
            api_calls int(11) UNSIGNED NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_date (user_id, stat_date),
            KEY user_id (user_id),
            KEY stat_date (stat_date)
        ) $charset_collate;";
        dbDelta($sql_stats);

        update_option('ai_seo_writer_pro_db_version', $this->db_version);
    }

    /**
     * Check if database needs upgrade
     */
    public function maybe_upgrade() {
        $installed_version = get_option('ai_seo_writer_pro_db_version', '0');
        if (version_compare($installed_version, $this->db_version, '<')) {
            $this->create_tables();
        }
    }

    /**
     * Insert a record into generation history
     * @param array $data
     * @return int|false
     */
    public function insert_history($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_history';
        $format = $this->get_history_format();
        $result = $wpdb->insert($table, $data, $format);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update a history record
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_history($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_history';
        $format = $this->get_history_format();
        return (bool) $wpdb->update($table, $data, ['id' => $id], $format, ['%d']);
    }

    /**
     * Get history records
     * @param array $args
     * @return array
     */
    public function get_history($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_history';

        $defaults = [
            'user_id' => 0,
            'status' => '',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ];
        $args = wp_parse_args($args, $defaults);

        $where = [];
        $params = [];

        if ($args['user_id'] > 0) {
            $where[] = 'user_id = %d';
            $params[] = $args['user_id'];
        }

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $params[] = $args['status'];
        }

        $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = $wpdb->prepare(
            "SELECT * FROM $table $where_sql ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d",
            array_merge($params, [$args['limit'], $args['offset']])
        );

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Insert API log
     * @param array $data
     * @return int|false
     */
    public function insert_api_log($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_api_logs';
        $format = [
            '%d', '%s', '%s', '%s', '%s',
            '%d', '%d', '%d', '%f', '%d',
            '%d', '%s'
        ];
        $result = $wpdb->insert($table, $data, $format);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Insert queue item
     * @param array $data
     * @return int|false
     */
    public function insert_queue_item($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_queue';
        $format = $this->get_queue_format();
        $result = $wpdb->insert($table, $data, $format);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update queue item
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_queue_item($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_queue';
        $format = $this->get_queue_format();
        return (bool) $wpdb->update($table, $data, ['id' => $id], $format, ['%d']);
    }

    /**
     * Get next pending queue item
     * @return array|null
     */
    public function get_next_queue_item() {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_queue';

        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE status = %s AND (scheduled_at IS NULL OR scheduled_at <= %s) ORDER BY priority ASC, created_at ASC LIMIT 1",
            'pending',
            current_time('mysql')
        );

        return $wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * Get queue items by batch
     * @param string $batch_id
     * @return array
     */
    public function get_queue_by_batch($batch_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_queue';
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE batch_id = %s ORDER BY id ASC", $batch_id),
            ARRAY_A
        );
    }

    /**
     * Insert custom template
     * @param array $data
     * @return int|false
     */
    public function insert_template($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_templates';
        $format = ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s'];
        $result = $wpdb->insert($table, $data, $format);
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update template
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_template($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_templates';
        $format = ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s'];
        return (bool) $wpdb->update($table, $data, ['id' => $id], $format, ['%d']);
    }

    /**
     * Get templates
     * @param array $args
     * @return array
     */
    public function get_templates($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_templates';

        $defaults = [
            'user_id' => 0,
            'category' => '',
            'include_global' => true,
            'status' => 'active',
        ];
        $args = wp_parse_args($args, $defaults);

        $where = [];
        $params = [];

        $where[] = 'status = %s';
        $params[] = $args['status'];

        $or_conditions = [];
        if ($args['user_id'] > 0) {
            $or_conditions[] = 'user_id = %d';
            $params[] = $args['user_id'];
        }
        if ($args['include_global']) {
            $or_conditions[] = 'is_global = 1';
        }
        if ($or_conditions) {
            $where[] = '(' . implode(' OR ', $or_conditions) . ')';
        }

        if (!empty($args['category'])) {
            $where[] = 'category = %s';
            $params[] = $args['category'];
        }

        $where_sql = 'WHERE ' . implode(' AND ', $where);
        $sql = $wpdb->prepare("SELECT * FROM $table $where_sql ORDER BY is_global DESC, name ASC", $params);

        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get template by ID
     * @param int $id
     * @return array|null
     */
    public function get_template($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_templates';
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id),
            ARRAY_A
        );
    }

    /**
     * Delete template
     * @param int $id
     * @return bool
     */
    public function delete_template($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_templates';
        return (bool) $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    /**
     * Update or insert daily stats
     * @param int $user_id
     * @param array $data
     * @return bool
     */
    public function update_daily_stats($user_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_stats';
        $stat_date = current_time('Y-m-d');

        $existing = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d AND stat_date = %s", $user_id, $stat_date),
            ARRAY_A
        );

        if ($existing) {
            $update_data = [];
            foreach ($data as $key => $value) {
                if (in_array($key, ['articles_generated', 'articles_published', 'total_tokens', 'total_words', 'api_calls'])) {
                    $update_data[$key] = ($existing[$key] ?? 0) + $value;
                } elseif ($key === 'avg_seo_score') {
                    $current_count = $existing['articles_generated'] ?? 1;
                    $current_avg = $existing['avg_seo_score'] ?? 0;
                    $new_count = $current_count + 1;
                    $update_data[$key] = (($current_avg * $current_count) + $value) / $new_count;
                }
            }
            return (bool) $wpdb->update(
                $table,
                $update_data,
                ['user_id' => $user_id, 'stat_date' => $stat_date],
                ['%d', '%d', '%d', '%d', '%d', '%f'],
                ['%d', '%s']
            );
        } else {
            return (bool) $wpdb->insert($table, array_merge([
                'user_id' => $user_id,
                'stat_date' => $stat_date,
            ], $data), ['%d', '%s', '%d', '%d', '%d', '%d', '%f', '%d']);
        }
    }

    /**
     * Get statistics for a date range
     * @param int $user_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function get_stats_range($user_id, $start_date, $end_date) {
        global $wpdb;
        $table = $wpdb->prefix . 'ai_seo_writer_pro_stats';
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE user_id = %d AND stat_date BETWEEN %s AND %s ORDER BY stat_date ASC",
                $user_id, $start_date, $end_date
            ),
            ARRAY_A
        );
    }

    /**
     * Get total counts for dashboard
     * @param int $user_id
     * @return array
     */
    public function get_dashboard_counts($user_id = 0) {
        global $wpdb;
        $history_table = $wpdb->prefix . 'ai_seo_writer_pro_history';
        $logs_table = $wpdb->prefix . 'ai_seo_writer_pro_api_logs';

        $user_where = $user_id > 0 ? $wpdb->prepare('WHERE user_id = %d', $user_id) : '';

        $counts = [];

        $counts['total_articles'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $history_table $user_where");
        $counts['published_articles'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $history_table $user_where " . ($user_where ? 'AND' : 'WHERE') . " status = 'published'");
        $counts['draft_articles'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $history_table $user_where " . ($user_where ? 'AND' : 'WHERE') . " status = 'draft'");
        $counts['total_tokens'] = (int) $wpdb->get_var("SELECT COALESCE(SUM(tokens_used), 0) FROM $history_table $user_where");
        $counts['avg_seo_score'] = (float) $wpdb->get_var("SELECT COALESCE(AVG(seo_score), 0) FROM $history_table $user_where WHERE seo_score IS NOT NULL");

        return $counts;
    }

    /**
     * Get data format for history table
     * @return array
     */
    private function get_history_format() {
        return [
            '%d', '%d', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s',
            '%d', '%s', '%s', '%s', '%d',
            '%f', '%f', '%f', '%s', '%s'
        ];
    }

    /**
     * Get data format for queue table
     * @return array
     */
    private function get_queue_format() {
        return [
            '%s', '%d', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%d', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s',
            '%d', '%d', '%d', '%d', '%s', '%s',
            '%s', '%s', '%s'
        ];
    }
}

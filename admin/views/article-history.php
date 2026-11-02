<?php
/**
 * View: Article History
 *
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ai-seo-writer-pro-card">
    <div class="ai-seo-writer-pro-card-header">
        <div>
            <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Article History', 'ai-seo-writer-pro'); ?></h3>
            <p class="ai-seo-writer-pro-card-subtitle"><?php esc_html_e('View and manage all your generated articles', 'ai-seo-writer-pro'); ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="ai-seo-writer-pro-filters">
        <div class="ai-seo-writer-pro-filter-item">
            <label for="ai-seo-history-filter-status"><?php esc_html_e('Status', 'ai-seo-writer-pro'); ?></label>
            <select id="ai-seo-history-filter-status" class="ai-seo-writer-pro-form-control">
                <option value=""><?php esc_html_e('All Statuses', 'ai-seo-writer-pro'); ?></option>
                <option value="published"><?php esc_html_e('Published', 'ai-seo-writer-pro'); ?></option>
                <option value="draft"><?php esc_html_e('Draft', 'ai-seo-writer-pro'); ?></option>
                <option value="failed"><?php esc_html_e('Failed', 'ai-seo-writer-pro'); ?></option>
            </select>
        </div>

        <div class="ai-seo-writer-pro-filter-item">
            <label for="ai-seo-history-search"><?php esc_html_e('Search', 'ai-seo-writer-pro'); ?></label>
            <input type="text" id="ai-seo-history-search" class="ai-seo-writer-pro-form-control" placeholder="<?php esc_attr_e('Search articles...', 'ai-seo-writer-pro'); ?>">
        </div>

        <div class="ai-seo-writer-pro-filter-item" style="margin-left: auto;">
            <button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary ai-seo-writer-pro-btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <?php esc_html_e('Export CSV', 'ai-seo-writer-pro'); ?>
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="ai-seo-writer-pro-table-wrap ai-seo-writer-pro-relative">
        <table class="ai-seo-writer-pro-table" id="ai-seo-history-table">
            <thead>
                <tr>
                    <th data-sort="title"><?php esc_html_e('Title / Topic', 'ai-seo-writer-pro'); ?></th>
                    <th data-sort="keyword"><?php esc_html_e('Keyword', 'ai-seo-writer-pro'); ?></th>
                    <th data-sort="provider"><?php esc_html_e('Provider', 'ai-seo-writer-pro'); ?></th>
                    <th data-sort="words"><?php esc_html_e('Words', 'ai-seo-writer-pro'); ?></th>
                    <th data-sort="seo"><?php esc_html_e('SEO Score', 'ai-seo-writer-pro'); ?></th>
                    <th data-sort="status"><?php esc_html_e('Status', 'ai-seo-writer-pro'); ?></th>
                    <th data-sort="date"><?php esc_html_e('Created', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Actions', 'ai-seo-writer-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8" class="ai-seo-writer-pro-table-empty">
                        <div class="ai-seo-writer-pro-loading"></div>
                        <div style="margin-top: 8px;"><?php esc_html_e('Loading...', 'ai-seo-writer-pro'); ?></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="ai-seo-writer-pro-pagination">
        <div class="ai-seo-writer-pro-pagination-info">
            <?php esc_html_e('Showing items', 'ai-seo-writer-pro'); ?> <span id="pagination-start">1</span>–<span id="pagination-end">20</span>
        </div>
        <div class="ai-seo-writer-pro-pagination-buttons">
            <button type="button" class="ai-seo-writer-pro-pagination-btn" disabled><?php esc_html_e('Prev', 'ai-seo-writer-pro'); ?></button>
            <button type="button" class="ai-seo-writer-pro-pagination-btn active" data-page="1">1</button>
            <button type="button" class="ai-seo-writer-pro-pagination-btn" data-page="2">2</button>
            <button type="button" class="ai-seo-writer-pro-pagination-btn" data-page="3">3</button>
            <button type="button" class="ai-seo-writer-pro-pagination-btn"><?php esc_html_e('Next', 'ai-seo-writer-pro'); ?></button>
        </div>
    </div>
</div>

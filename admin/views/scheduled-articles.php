<?php
/**
 * View: Scheduled Articles
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
            <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Scheduled Articles', 'ai-seo-writer-pro'); ?></h3>
            <p class="ai-seo-writer-pro-card-subtitle"><?php esc_html_e('Manage articles queued for generation', 'ai-seo-writer-pro'); ?></p>
        </div>
        <a href="<?php echo esc_url(admin_url('admin.php?page=ai-seo-writer-pro-bulk-generator')); ?>" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <?php esc_html_e('Schedule New', 'ai-seo-writer-pro'); ?>
        </a>
    </div>

    <!-- Status Legend -->
    <div class="ai-seo-writer-pro-flex ai-seo-writer-pro-gap-2 ai-seo-writer-pro-mb-2">
        <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-warning"><?php esc_html_e('Pending', 'ai-seo-writer-pro'); ?></span>
        <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-primary"><?php esc_html_e('Processing', 'ai-seo-writer-pro'); ?></span>
        <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-gray"><?php esc_html_e('Paused', 'ai-seo-writer-pro'); ?></span>
    </div>

    <!-- Table -->
    <div class="ai-seo-writer-pro-table-wrap ai-seo-writer-pro-relative" id="ai-seo-scheduled-table-wrap">
        <table class="ai-seo-writer-pro-table" id="ai-seo-scheduled-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Title / Topic', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Keyword', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Scheduled For', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Provider', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Priority', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Status', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Attempts', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Actions', 'ai-seo-writer-pro'); ?></th>
                </tr>
            </thead>
            <tbody id="ai-seo-scheduled-tbody">
                <tr>
                    <td colspan="8" class="ai-seo-writer-pro-table-empty">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin: 0 auto 12px; display: block;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <?php esc_html_e('No scheduled articles yet. Articles from bulk generation will appear here.', 'ai-seo-writer-pro'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Batch Summary -->
<div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-3">
    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('In Queue', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value" id="scheduled-pending-count">0</div>
    </div>

    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Processing Now', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value" id="scheduled-processing-count">0</div>
    </div>

    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Paused', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value" id="scheduled-paused-count">0</div>
    </div>
</div>

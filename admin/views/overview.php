<?php
/**
 * View: Overview / Dashboard
 *
 * @var array $counts
 * @var array $recent
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 * @var \AI_SEO_Writer_Pro\License_Manager $license
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Stats Grid -->
<div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-auto ai-seo-writer-pro-mb-3">
    <!-- Total Articles -->
    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Total Articles', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value"><?php echo esc_html($counts['total_articles'] ?? 0); ?></div>
    </div>

    <!-- Published -->
    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Published', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value"><?php echo esc_html($counts['published_articles'] ?? 0); ?></div>
    </div>

    <!-- Drafts -->
    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Drafts', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value"><?php echo esc_html($counts['draft_articles'] ?? 0); ?></div>
    </div>

    <!-- Total Tokens -->
    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Total Tokens Used', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value"><?php echo esc_html(number_format_i18n($counts['total_tokens'] ?? 0)); ?></div>
    </div>

    <!-- Avg SEO Score -->
    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Avg SEO Score', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value"><?php echo esc_html(round($counts['avg_seo_score'] ?? 0, 1)); ?></div>
    </div>

    <!-- Remaining Today -->
    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Remaining Today', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value">
            <?php
            $remaining = $license->get_remaining_generations();
            echo esc_html($license->is_pro() ? __('Unlimited', 'ai-seo-writer-pro') : $remaining);
            ?>
        </div>
    </div>
</div>

<div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-2">
    <!-- Monthly Stats Chart -->
    <div class="ai-seo-writer-pro-card">
        <div class="ai-seo-writer-pro-card-header">
            <div>
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Monthly Generation Stats', 'ai-seo-writer-pro'); ?></h3>
                <p class="ai-seo-writer-pro-card-subtitle"><?php esc_html_e('Articles generated over the last 30 days', 'ai-seo-writer-pro'); ?></p>
            </div>
        </div>
        <div class="ai-seo-writer-pro-chart" id="ai-seo-monthly-chart">
            <?php
            // Generate empty bars as placeholders
            for ($i = 0; $i < 30; $i++) {
                echo '<div class="ai-seo-writer-pro-chart-bar" style="height: ' . esc_attr(rand(5, 60)) . '%"></div>';
            }
            ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="ai-seo-writer-pro-card">
        <div class="ai-seo-writer-pro-card-header">
            <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Quick Actions', 'ai-seo-writer-pro'); ?></h3>
        </div>
        <div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-2">
            <a href="<?php echo esc_url(admin_url('admin.php?page=ai-seo-writer-pro-article-generator')); ?>" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-block">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                <?php esc_html_e('Generate Article', 'ai-seo-writer-pro'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ai-seo-writer-pro-seo-analyzer')); ?>" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary ai-seo-writer-pro-btn-block">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <?php esc_html_e('Analyze SEO', 'ai-seo-writer-pro'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ai-seo-writer-pro-content-templates')); ?>" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary ai-seo-writer-pro-btn-block">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <?php esc_html_e('Browse Templates', 'ai-seo-writer-pro'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ai-seo-writer-pro-api-settings')); ?>" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary ai-seo-writer-pro-btn-block">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a2 2 0 01-2 2H4a2 2 0 01-2-2V8z"/></svg>
                <?php esc_html_e('Configure API', 'ai-seo-writer-pro'); ?>
            </a>
        </div>

        <?php if (!$license->is_pro()) : ?>
        <div class="ai-seo-writer-pro-alert ai-seo-writer-pro-alert-info ai-seo-writer-pro-mb-2" style="margin-top: 20px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            <span>
                <strong><?php esc_html_e('Upgrade to Pro', 'ai-seo-writer-pro'); ?></strong><br>
                <?php esc_html_e('Unlock bulk generation, AI images, advanced SEO analysis, and unlimited generations.', 'ai-seo-writer-pro'); ?>
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Articles -->
<div class="ai-seo-writer-pro-card">
    <div class="ai-seo-writer-pro-card-header">
        <div>
            <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Recent Articles', 'ai-seo-writer-pro'); ?></h3>
            <p class="ai-seo-writer-pro-card-subtitle"><?php esc_html_e('Your latest generated content', 'ai-seo-writer-pro'); ?></p>
        </div>
        <a href="<?php echo esc_url(admin_url('admin.php?page=ai-seo-writer-pro-article-history')); ?>" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary ai-seo-writer-pro-btn-sm">
            <?php esc_html_e('View All', 'ai-seo-writer-pro'); ?>
        </a>
    </div>

    <div class="ai-seo-writer-pro-table-wrap">
        <table class="ai-seo-writer-pro-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Title', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Keyword', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Provider', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Words', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('SEO Score', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Status', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Date', 'ai-seo-writer-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent)) : ?>
                    <tr>
                        <td colspan="7" class="ai-seo-writer-pro-table-empty">
                            <?php esc_html_e('No articles generated yet. Start by creating your first article!', 'ai-seo-writer-pro'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($recent as $item) : ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($item['title'] ?? '-'); ?></strong>
                                <div class="ai-seo-writer-pro-text-small ai-seo-writer-pro-text-muted">
                                    <?php echo esc_html(mb_substr($item['topic'] ?? '', 0, 60)); ?>
                                </div>
                            </td>
                            <td><?php echo esc_html($item['primary_keyword'] ?? '-'); ?></td>
                            <td><?php echo esc_html($item['ai_provider'] ?? '-'); ?></td>
                            <td><?php echo esc_html($item['word_count'] ?? 0); ?></td>
                            <td>
                                <?php if (!empty($item['seo_score'])) : ?>
                                    <?php
                                    $score_class = $item['seo_score'] >= 80 ? 'success' : ($item['seo_score'] >= 60 ? 'warning' : 'danger');
                                    ?>
                                    <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-<?php echo esc_attr($score_class); ?>">
                                        <?php echo esc_html(round($item['seo_score'])); ?>
                                    </span>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_map = [
                                    'published' => ['success', __('Published', 'ai-seo-writer-pro')],
                                    'draft'     => ['gray', __('Draft', 'ai-seo-writer-pro')],
                                    'failed'    => ['danger', __('Failed', 'ai-seo-writer-pro')],
                                ];
                                $status = $status_map[$item['status']] ?? ['info', $item['status']];
                                ?>
                                <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-<?php echo esc_attr($status[0]); ?>">
                                    <?php echo esc_html($status[1]); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($item['created_at'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

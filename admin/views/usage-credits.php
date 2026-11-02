<?php
/**
 * View: Usage & Credits
 *
 * @var array $counts
 * @var \AI_SEO_Writer_Pro\License_Manager $license
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_pro      = $license->is_pro();
$remaining   = $license->get_remaining_generations();
$daily_limit = $is_pro ? 999999 : $license->filter_generation_limit(0);
$used_today  = $is_pro ? 0 : max(0, $daily_limit - $remaining);
$usage_pct   = $daily_limit > 0 ? min(100, ($used_today / $daily_limit) * 100) : 0;
?>

<!-- License Status -->
<div class="ai-seo-writer-pro-card">
    <div class="ai-seo-writer-pro-card-header">
        <div>
            <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('License Status', 'ai-seo-writer-pro'); ?></h3>
            <p class="ai-seo-writer-pro-card-subtitle">
                <?php if ($is_pro) : ?>
                    <?php esc_html_e('You have an active Pro license', 'ai-seo-writer-pro'); ?>
                <?php else : ?>
                    <?php esc_html_e('Free version - upgrade for unlimited generations', 'ai-seo-writer-pro'); ?>
                <?php endif; ?>
            </p>
        </div>
        <?php if ($is_pro) : ?>
            <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success" style="font-size: 13px; padding: 6px 14px;">
                <?php esc_html_e('PRO ACTIVE', 'ai-seo-writer-pro'); ?>
            </span>
        <?php else : ?>
            <button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-sm">
                <?php esc_html_e('Upgrade to Pro', 'ai-seo-writer-pro'); ?>
            </button>
        <?php endif; ?>
    </div>

    <div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-3">
        <div>
            <div class="ai-seo-writer-pro-progress-label">
                <span><?php esc_html_e('Daily Generations Used', 'ai-seo-writer-pro'); ?></span>
                <span>
                    <?php echo esc_html($used_today); ?> / <?php echo $is_pro ? esc_html__('Unlimited', 'ai-seo-writer-pro') : esc_html($daily_limit); ?>
                </span>
            </div>
            <div class="ai-seo-writer-pro-progress">
                <div class="ai-seo-writer-pro-progress-bar <?php echo $usage_pct > 80 ? 'warning' : ''; ?>" style="width: <?php echo esc_attr($is_pro ? 15 : $usage_pct); ?>%"></div>
            </div>
        </div>

        <div>
            <div class="ai-seo-writer-pro-progress-label">
                <span><?php esc_html_e('Plan', 'ai-seo-writer-pro'); ?></span>
            </div>
            <div class="ai-seo-writer-pro-stat-card" style="padding: 12px 16px;">
                <div class="ai-seo-writer-pro-stat-value" style="font-size: 20px;">
                    <?php echo $is_pro ? esc_html__('Professional', 'ai-seo-writer-pro') : esc_html__('Free', 'ai-seo-writer-pro'); ?>
                </div>
            </div>
        </div>

        <div>
            <div class="ai-seo-writer-pro-progress-label">
                <span><?php esc_html_e('Remaining Today', 'ai-seo-writer-pro'); ?></span>
            </div>
            <div class="ai-seo-writer-pro-stat-card" style="padding: 12px 16px;">
                <div class="ai-seo-writer-pro-stat-value" style="font-size: 20px; color: var(--ai-seo-success);">
                    <?php echo $is_pro ? esc_html__('∞', 'ai-seo-writer-pro') : esc_html($remaining); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Usage Statistics -->
<div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-4 ai-seo-writer-pro-mb-3">
    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Total Articles', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value"><?php echo esc_html($counts['total_articles'] ?? 0); ?></div>
    </div>

    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Published', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value"><?php echo esc_html($counts['published_articles'] ?? 0); ?></div>
    </div>

    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Total Tokens', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value"><?php echo esc_html(number_format_i18n($counts['total_tokens'] ?? 0)); ?></div>
    </div>

    <div class="ai-seo-writer-pro-stat-card">
        <div class="ai-seo-writer-pro-stat-header">
            <span class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Avg SEO Score', 'ai-seo-writer-pro'); ?></span>
            <div class="ai-seo-writer-pro-stat-icon warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="ai-seo-writer-pro-stat-value"><?php echo esc_html(round($counts['avg_seo_score'] ?? 0, 1)); ?></div>
    </div>
</div>

<!-- Feature Limits -->
<div class="ai-seo-writer-pro-card">
    <div class="ai-seo-writer-pro-card-header">
        <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Feature Limits', 'ai-seo-writer-pro'); ?></h3>
    </div>

    <div class="ai-seo-writer-pro-table-wrap">
        <table class="ai-seo-writer-pro-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Feature', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Free Plan', 'ai-seo-writer-pro'); ?></th>
                    <th><?php esc_html_e('Pro Plan', 'ai-seo-writer-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><?php esc_html_e('Article Generations', 'ai-seo-writer-pro'); ?></strong></td>
                    <td><?php echo esc_html($daily_limit); ?> <?php esc_html_e('per day', 'ai-seo-writer-pro'); ?></td>
                    <td><span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success"><?php esc_html_e('Unlimited', 'ai-seo-writer-pro'); ?></span></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e('Bulk Generation', 'ai-seo-writer-pro'); ?></strong></td>
                    <td><span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-gray"><?php esc_html_e('Not available', 'ai-seo-writer-pro'); ?></span></td>
                    <td><span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success"><?php esc_html_e('Available', 'ai-seo-writer-pro'); ?></span></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e('AI Image Generation', 'ai-seo-writer-pro'); ?></strong></td>
                    <td><span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-gray"><?php esc_html_e('Not available', 'ai-seo-writer-pro'); ?></span></td>
                    <td><span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success"><?php esc_html_e('Available', 'ai-seo-writer-pro'); ?></span></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e('Advanced SEO Analyzer', 'ai-seo-writer-pro'); ?></strong></td>
                    <td><span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-warning"><?php esc_html_e('Basic', 'ai-seo-writer-pro'); ?></span></td>
                    <td><span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success"><?php esc_html_e('Advanced', 'ai-seo-writer-pro'); ?></span></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e('Custom Templates', 'ai-seo-writer-pro'); ?></strong></td>
                    <td><?php esc_html_e('Built-in only', 'ai-seo-writer-pro'); ?></td>
                    <td><span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success"><?php esc_html_e('Unlimited custom', 'ai-seo-writer-pro'); ?></span></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e('AI Providers', 'ai-seo-writer-pro'); ?></strong></td>
                    <td><?php esc_html_e('1 provider', 'ai-seo-writer-pro'); ?></td>
                    <td><span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success"><?php esc_html_e('All providers', 'ai-seo-writer-pro'); ?></span></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e('Priority Support', 'ai-seo-writer-pro'); ?></strong></td>
                    <td><?php esc_html_e('Community', 'ai-seo-writer-pro'); ?></td>
                    <td><span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success"><?php esc_html_e('Priority email', 'ai-seo-writer-pro'); ?></span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if (!$is_pro) : ?>
        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--ai-seo-border); text-align: center;">
            <h4 style="margin: 0 0 8px; font-size: 16px;"><?php esc_html_e('Ready to unlock all features?', 'ai-seo-writer-pro'); ?></h4>
            <p style="color: var(--ai-seo-gray-500); margin: 0 0 16px;"><?php esc_html_e('Upgrade to Pro and get unlimited generations, bulk generation, AI images, and more.', 'ai-seo-writer-pro'); ?></p>
            <button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-lg">
                <?php esc_html_e('Upgrade to Pro Now', 'ai-seo-writer-pro'); ?>
            </button>
        </div>
    <?php endif; ?>
</div>

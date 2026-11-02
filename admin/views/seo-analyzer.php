<?php
/**
 * View: SEO Analyzer
 *
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-2">
    <!-- Input -->
    <div class="ai-seo-writer-pro-card ai-seo-writer-pro-relative">
        <div class="ai-seo-writer-pro-card-header">
            <div>
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Content Analysis', 'ai-seo-writer-pro'); ?></h3>
                <p class="ai-seo-writer-pro-card-subtitle"><?php esc_html_e('Paste your content to analyze SEO performance', 'ai-seo-writer-pro'); ?></p>
            </div>
        </div>

        <form id="ai-seo-analyze-form">
            <?php echo $security->get_nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label" for="ai-seo-analyze-title">
                    <?php esc_html_e('Article Title', 'ai-seo-writer-pro'); ?>
                </label>
                <input
                    type="text"
                    id="ai-seo-analyze-title"
                    class="ai-seo-writer-pro-form-control"
                    placeholder="<?php esc_attr_e('Enter your article title', 'ai-seo-writer-pro'); ?>"
                >
            </div>

            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label" for="ai-seo-analyze-keyword">
                    <?php esc_html_e('Target Keyword', 'ai-seo-writer-pro'); ?>
                    <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="ai-seo-analyze-keyword"
                    class="ai-seo-writer-pro-form-control"
                    data-required="true"
                    placeholder="<?php esc_attr_e('Primary keyword to analyze', 'ai-seo-writer-pro'); ?>"
                >
            </div>

            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label" for="ai-seo-analyze-content">
                    <?php esc_html_e('Content', 'ai-seo-writer-pro'); ?>
                    <span class="required">*</span>
                </label>
                <textarea
                    id="ai-seo-analyze-content"
                    class="ai-seo-writer-pro-form-control"
                    data-required="true"
                    rows="14"
                    placeholder="<?php esc_attr_e('Paste your article content here...', 'ai-seo-writer-pro'); ?>"
                ></textarea>
                <span class="ai-seo-writer-pro-form-hint">
                    <?php esc_html_e('For best results, include the full article content.', 'ai-seo-writer-pro'); ?>
                </span>
            </div>

            <button type="button" id="ai-seo-analyze-btn" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-lg ai-seo-writer-pro-btn-block">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <?php esc_html_e('Analyze SEO', 'ai-seo-writer-pro'); ?>
            </button>
        </form>
    </div>

    <!-- Results -->
    <div id="ai-seo-analysis-results" class="ai-seo-writer-pro-relative ai-seo-writer-pro-hidden">
        <!-- Score Overview -->
        <div class="ai-seo-writer-pro-card ai-seo-writer-pro-text-center">
            <div class="ai-seo-writer-pro-card-header">
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('SEO Score', 'ai-seo-writer-pro'); ?></h3>
            </div>
            <div class="ai-seo-writer-pro-score-circle" id="ai-seo-score-circle">
                <svg width="120" height="120" viewBox="0 0 120 120">
                    <circle class="score-bg" cx="60" cy="60" r="52"/>
                    <circle class="score-fill" cx="60" cy="60" r="52" stroke-dasharray="326.7" stroke-dashoffset="326.7" stroke="#0ea5e9"/>
                </svg>
                <div class="score-value">0</div>
                <div class="score-label"><?php esc_html_e('Out of 100', 'ai-seo-writer-pro'); ?></div>
            </div>

            <div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-4" style="margin-top: 24px;">
                <div>
                    <div class="ai-seo-writer-pro-text-small ai-seo-writer-pro-text-muted"><?php esc_html_e('Readability', 'ai-seo-writer-pro'); ?></div>
                    <div class="ai-seo-writer-pro-stat-value" style="font-size: 20px;" id="ai-seo-readability-score">-</div>
                </div>
                <div>
                    <div class="ai-seo-writer-pro-text-small ai-seo-writer-pro-text-muted"><?php esc_html_e('Keyword', 'ai-seo-writer-pro'); ?></div>
                    <div class="ai-seo-writer-pro-stat-value" style="font-size: 20px;" id="ai-seo-keyword-score">-</div>
                </div>
                <div>
                    <div class="ai-seo-writer-pro-text-small ai-seo-writer-pro-text-muted"><?php esc_html_e('Structure', 'ai-seo-writer-pro'); ?></div>
                    <div class="ai-seo-writer-pro-stat-value" style="font-size: 20px;" id="ai-seo-structure-score">-</div>
                </div>
                <div>
                    <div class="ai-seo-writer-pro-text-small ai-seo-writer-pro-text-muted"><?php esc_html_e('Meta', 'ai-seo-writer-pro'); ?></div>
                    <div class="ai-seo-writer-pro-stat-value" style="font-size: 20px;" id="ai-seo-meta-score">-</div>
                </div>
            </div>
        </div>

        <!-- Suggestions -->
        <div class="ai-seo-writer-pro-card">
            <div class="ai-seo-writer-pro-card-header">
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Suggestions & Feedback', 'ai-seo-writer-pro'); ?></h3>
            </div>
            <ul class="ai-seo-writer-pro-suggestions" id="ai-seo-suggestions-list">
                <li class="ai-seo-writer-pro-suggestion-item">
                    <div class="ai-seo-writer-pro-suggestion-icon warning">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <div class="ai-seo-writer-pro-suggestion-content">
                        <h4><?php esc_html_e('Run analysis to see suggestions', 'ai-seo-writer-pro'); ?></h4>
                        <p><?php esc_html_e('Fill in the form and click "Analyze SEO" to get detailed feedback.', 'ai-seo-writer-pro'); ?></p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

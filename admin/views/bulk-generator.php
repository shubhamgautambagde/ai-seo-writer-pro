<?php
/**
 * View: Bulk Generator
 *
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 * @var \AI_SEO_Writer_Pro\License_Manager $license
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<?php if (!$license->is_feature_available('bulk_generation')) : ?>
    <div class="ai-seo-writer-pro-card ai-seo-writer-pro-text-center" style="padding: 60px 24px;">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin-bottom: 16px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <h3 style="margin: 0 0 8px; font-size: 20px;"><?php esc_html_e('Bulk Generation is a Pro Feature', 'ai-seo-writer-pro'); ?></h3>
        <p style="color: var(--ai-seo-gray-500); margin: 0 0 20px; max-width: 480px; margin-left: auto; margin-right: auto;">
            <?php esc_html_e('Upload a CSV with hundreds of topics and generate articles in bulk automatically. Upgrade to Pro to unlock this powerful feature.', 'ai-seo-writer-pro'); ?>
        </p>
        <button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-lg">
            <?php esc_html_e('Upgrade to Pro', 'ai-seo-writer-pro'); ?>
        </button>
    </div>
<?php return; endif; ?>

<div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-2">
    <!-- CSV Upload -->
    <div class="ai-seo-writer-pro-card ai-seo-writer-pro-relative">
        <div class="ai-seo-writer-pro-card-header">
            <div>
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Upload CSV File', 'ai-seo-writer-pro'); ?></h3>
                <p class="ai-seo-writer-pro-card-subtitle"><?php esc_html_e('Upload a CSV file with your article topics', 'ai-seo-writer-pro'); ?></p>
            </div>
        </div>

        <?php echo $security->get_nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <div id="ai-seo-csv-dropzone" class="ai-seo-writer-pro-dropzone">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <p class="ai-seo-writer-pro-dropzone-title"><?php esc_html_e('Drag & drop your CSV file here', 'ai-seo-writer-pro'); ?></p>
            <p class="ai-seo-writer-pro-dropzone-hint"><?php esc_html_e('or click to browse', 'ai-seo-writer-pro'); ?></p>
            <input type="file" id="ai-seo-csv-file" accept=".csv" style="display: none;">
        </div>

        <div class="ai-seo-writer-pro-alert ai-seo-writer-pro-alert-info" style="margin-top: 20px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>
                <strong><?php esc_html_e('CSV Format:', 'ai-seo-writer-pro'); ?></strong><br>
                <?php esc_html_e('Columns: topic, primary_keyword, secondary_keywords, title, language, tone, content_type, article_length', 'ai-seo-writer-pro'); ?>
            </span>
        </div>

        <div class="ai-seo-writer-pro-doc-code" style="margin-top: 16px; font-size: 12px;">
topic,primary_keyword,secondary_keywords,title,language,tone,content_type,article_length
"How to improve WordPress SEO","wordpress seo","seo tips, optimization, speed",,en,professional,blog,1500
"Best web hosting services 2026","best web hosting","hosting comparison, reviews",,en,informative,review,2000
        </div>
    </div>

    <!-- Generation Settings -->
    <div class="ai-seo-writer-pro-card">
        <div class="ai-seo-writer-pro-card-header">
            <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Default Settings', 'ai-seo-writer-pro'); ?></h3>
            <p class="ai-seo-writer-pro-card-subtitle ai-seo-writer-pro-mt-0"><?php esc_html_e('Applied when CSV column is empty', 'ai-seo-writer-pro'); ?></p>
        </div>

        <div class="ai-seo-writer-pro-form-group">
            <label class="ai-seo-writer-pro-form-label"><?php esc_html_e('Default AI Provider', 'ai-seo-writer-pro'); ?></label>
            <select class="ai-seo-writer-pro-form-control">
                <option value="openai">OpenAI</option>
                <option value="gemini">Google Gemini</option>
                <option value="claude">Anthropic Claude</option>
            </select>
        </div>

        <div class="ai-seo-writer-pro-form-group">
            <label class="ai-seo-writer-pro-form-label"><?php esc_html_e('Default Model', 'ai-seo-writer-pro'); ?></label>
            <select class="ai-seo-writer-pro-form-control">
                <option value="gpt-4o">GPT-4o</option>
                <option value="gpt-4o-mini">GPT-4o Mini</option>
                <option value="gpt-4-turbo">GPT-4 Turbo</option>
            </select>
        </div>

        <div class="ai-seo-writer-pro-form-row">
            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label"><?php esc_html_e('Default Language', 'ai-seo-writer-pro'); ?></label>
                <select class="ai-seo-writer-pro-form-control">
                    <option value="en"><?php esc_html_e('English', 'ai-seo-writer-pro'); ?></option>
                    <option value="es"><?php esc_html_e('Spanish', 'ai-seo-writer-pro'); ?></option>
                    <option value="de"><?php esc_html_e('German', 'ai-seo-writer-pro'); ?></option>
                    <option value="fr"><?php esc_html_e('French', 'ai-seo-writer-pro'); ?></option>
                </select>
            </div>

            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label"><?php esc_html_e('Concurrency', 'ai-seo-writer-pro'); ?></label>
                <select class="ai-seo-writer-pro-form-control">
                    <option value="1"><?php esc_html_e('1 at a time', 'ai-seo-writer-pro'); ?></option>
                    <option value="2" selected><?php esc_html_e('2 at a time', 'ai-seo-writer-pro'); ?></option>
                    <option value="3"><?php esc_html_e('3 at a time', 'ai-seo-writer-pro'); ?></option>
                    <option value="5"><?php esc_html_e('5 at a time', 'ai-seo-writer-pro'); ?></option>
                </select>
            </div>
        </div>

        <div class="ai-seo-writer-pro-form-group">
            <label class="ai-seo-writer-pro-form-label"><?php esc_html_e('Post Status', 'ai-seo-writer-pro'); ?></label>
            <select class="ai-seo-writer-pro-form-control">
                <option value="draft"><?php esc_html_e('Save as Draft', 'ai-seo-writer-pro'); ?></option>
                <option value="pending"><?php esc_html_e('Pending Review', 'ai-seo-writer-pro'); ?></option>
                <option value="publish"><?php esc_html_e('Publish Immediately', 'ai-seo-writer-pro'); ?></option>
            </select>
        </div>

        <div class="ai-seo-writer-pro-checkbox-group">
            <input type="checkbox" id="bulk-auto-seo" checked>
            <label for="bulk-auto-seo"><?php esc_html_e('Run SEO analysis after generation', 'ai-seo-writer-pro'); ?></label>
        </div>

        <div class="ai-seo-writer-pro-checkbox-group">
            <input type="checkbox" id="bulk-auto-images">
            <label for="bulk-auto-images"><?php esc_html_e('Generate featured images (if enabled)', 'ai-seo-writer-pro'); ?></label>
        </div>
    </div>
</div>

<!-- Progress Tracking -->
<div id="ai-seo-batch-info" class="ai-seo-writer-pro-card ai-seo-writer-pro-hidden">
    <div class="ai-seo-writer-pro-card-header">
        <div>
            <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Generation Progress', 'ai-seo-writer-pro'); ?></h3>
            <p class="ai-seo-writer-pro-card-subtitle">
                <?php esc_html_e('Batch ID:', 'ai-seo-writer-pro'); ?> <code id="ai-seo-batch-id">-</code>
            </p>
        </div>
        <div class="ai-seo-writer-pro-flex ai-seo-writer-pro-gap-1">
            <button type="button" id="ai-seo-pause-bulk" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-warning ai-seo-writer-pro-btn-sm">
                <?php esc_html_e('Pause', 'ai-seo-writer-pro'); ?>
            </button>
            <button type="button" id="ai-seo-resume-bulk" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-success ai-seo-writer-pro-btn-sm ai-seo-writer-pro-hidden">
                <?php esc_html_e('Resume', 'ai-seo-writer-pro'); ?>
            </button>
            <button type="button" id="ai-seo-cancel-bulk" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-danger ai-seo-writer-pro-btn-sm">
                <?php esc_html_e('Cancel', 'ai-seo-writer-pro'); ?>
            </button>
        </div>
    </div>

    <div class="ai-seo-writer-pro-progress-label">
        <span><?php esc_html_e('Overall Progress', 'ai-seo-writer-pro'); ?></span>
        <span id="ai-seo-progress-percent">0%</span>
    </div>
    <div class="ai-seo-writer-pro-progress ai-seo-writer-pro-mb-3">
        <div id="ai-seo-progress-bar" class="ai-seo-writer-pro-progress-bar" style="width: 0%"></div>
    </div>

    <div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-4">
        <div class="ai-seo-writer-pro-stat-card" style="padding: 16px;">
            <div class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Total', 'ai-seo-writer-pro'); ?></div>
            <div class="ai-seo-writer-pro-stat-value" style="font-size: 22px;" id="ai-seo-total-items">0</div>
        </div>
        <div class="ai-seo-writer-pro-stat-card" style="padding: 16px;">
            <div class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Completed', 'ai-seo-writer-pro'); ?></div>
            <div class="ai-seo-writer-pro-stat-value" style="font-size: 22px; color: var(--ai-seo-success);" id="ai-seo-completed-count">0</div>
        </div>
        <div class="ai-seo-writer-pro-stat-card" style="padding: 16px;">
            <div class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Failed', 'ai-seo-writer-pro'); ?></div>
            <div class="ai-seo-writer-pro-stat-value" style="font-size: 22px; color: var(--ai-seo-danger);" id="ai-seo-failed-count">0</div>
        </div>
        <div class="ai-seo-writer-pro-stat-card" style="padding: 16px;">
            <div class="ai-seo-writer-pro-stat-label"><?php esc_html_e('Pending', 'ai-seo-writer-pro'); ?></div>
            <div class="ai-seo-writer-pro-stat-value" style="font-size: 22px; color: var(--ai-seo-warning);" id="ai-seo-pending-count">0</div>
        </div>
    </div>
</div>

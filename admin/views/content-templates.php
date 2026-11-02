<?php
/**
 * View: Content Templates
 *
 * @var array $templates
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 * @var array $built_in
 */

if (!defined('ABSPATH')) {
    exit;
}

$built_in_templates = [
    [
        'name' => 'Standard Blog Post',
        'description' => 'Classic blog article format with introduction, body sections, and conclusion.',
        'category' => 'blog',
    ],
    [
        'name' => 'How-To Guide',
        'description' => 'Step-by-step instructional content that teaches readers how to accomplish a task.',
        'category' => 'howto',
    ],
    [
        'name' => 'Product Review',
        'description' => 'Comprehensive product evaluation with pros, cons, features, and final verdict.',
        'category' => 'review',
    ],
    [
        'name' => 'Comparison Article',
        'description' => 'Side-by-side comparison of two or more products, services, or approaches.',
        'category' => 'comparison',
    ],
    [
        'name' => 'Listicle',
        'description' => 'Engaging numbered list format with tips, examples, or recommendations.',
        'category' => 'listicle',
    ],
    [
        'name' => 'Pillar Page Content',
        'description' => 'Comprehensive, authoritative guide covering all aspects of a broad topic.',
        'category' => 'pillar',
    ],
    [
        'name' => 'News Article',
        'description' => 'Timely journalistic format with inverted pyramid structure and sources.',
        'category' => 'news',
    ],
    [
        'name' => 'Local SEO Article',
        'description' => 'Location-focused content optimized for local search visibility.',
        'category' => 'local_seo',
    ],
    [
        'name' => 'Landing Page Copy',
        'description' => 'Conversion-focused content designed to drive specific user actions.',
        'category' => 'landing_page',
    ],
    [
        'name' => 'Case Study',
        'description' => 'Detailed analysis of a specific project, challenge, solution, and results.',
        'category' => 'case_study',
    ],
    [
        'name' => 'Interview Style',
        'description' => 'Q&A format content featuring expert insights and perspectives.',
        'category' => 'interview',
    ],
    [
        'name' => 'Ultimate Guide',
        'description' => 'Extremely thorough resource covering every angle of a complex topic.',
        'category' => 'ultimate_guide',
    ],
    [
        'name' => 'Problem-Solution',
        'description' => 'Identify common pain points and present clear, actionable solutions.',
        'category' => 'problem_solution',
    ],
    [
        'name' => 'Myth-Busting',
        'description' => 'Debunk common misconceptions with facts, evidence, and expert opinion.',
        'category' => 'myth_busting',
    ],
    [
        'name' => 'Trend Analysis',
        'description' => 'Forward-looking content analyzing industry trends and future predictions.',
        'category' => 'trend_analysis',
    ],
    [
        'name' => 'Beginner Tutorial',
        'description' => 'Gentle introduction to a topic for readers with no prior knowledge.',
        'category' => 'beginner',
    ],
];
?>

<div class="ai-seo-writer-pro-card">
    <div class="ai-seo-writer-pro-card-header">
        <div>
            <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Content Templates', 'ai-seo-writer-pro'); ?></h3>
            <p class="ai-seo-writer-pro-card-subtitle"><?php esc_html_e('Use pre-built templates or create your own to speed up content generation', 'ai-seo-writer-pro'); ?></p>
        </div>
        <button type="button" id="ai-seo-new-template-btn" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <?php esc_html_e('New Template', 'ai-seo-writer-pro'); ?>
        </button>
    </div>

    <!-- Tabs -->
    <div class="ai-seo-writer-pro-tabs">
        <button type="button" class="ai-seo-writer-pro-tab active" data-target="#built-in-templates">
            <?php esc_html_e('Built-in Templates', 'ai-seo-writer-pro'); ?>
            <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-gray" style="margin-left: 6px;"><?php echo esc_html(count($built_in_templates)); ?></span>
        </button>
        <button type="button" class="ai-seo-writer-pro-tab" data-target="#custom-templates">
            <?php esc_html_e('My Templates', 'ai-seo-writer-pro'); ?>
            <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-gray" style="margin-left: 6px;"><?php echo esc_html(count($templates)); ?></span>
        </button>
    </div>

    <!-- Built-in Templates Panel -->
    <div id="built-in-templates" class="ai-seo-writer-pro-tab-panel active">
        <div class="ai-seo-writer-pro-template-grid">
            <?php foreach ($built_in_templates as $tpl) : ?>
                <div class="ai-seo-writer-pro-template-card built-in">
                    <h4 class="ai-seo-writer-pro-template-name"><?php echo esc_html($tpl['name']); ?></h4>
                    <p class="ai-seo-writer-pro-template-desc"><?php echo esc_html($tpl['description']); ?></p>
                    <span class="ai-seo-writer-pro-template-category"><?php echo esc_html($tpl['category']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Custom Templates Panel -->
    <div id="custom-templates" class="ai-seo-writer-pro-tab-panel">
        <?php if (empty($templates)) : ?>
            <div class="ai-seo-writer-pro-table-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin-bottom: 12px;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <p><?php esc_html_e('No custom templates yet. Create your first one!', 'ai-seo-writer-pro'); ?></p>
            </div>
        <?php else : ?>
            <div class="ai-seo-writer-pro-template-grid">
                <?php foreach ($templates as $tpl) : ?>
                    <div class="ai-seo-writer-pro-template-card">
                        <div class="ai-seo-writer-pro-flex-between" style="margin-bottom: 8px;">
                            <h4 class="ai-seo-writer-pro-template-name" style="margin: 0; padding-right: 0;"><?php echo esc_html($tpl['name']); ?></h4>
                        </div>
                        <p class="ai-seo-writer-pro-template-desc"><?php echo esc_html($tpl['description'] ?? ''); ?></p>
                        <div class="ai-seo-writer-pro-flex-between" style="margin-top: 12px;">
                            <span class="ai-seo-writer-pro-template-category"><?php echo esc_html($tpl['category'] ?? 'custom'); ?></span>
                            <div class="ai-seo-writer-pro-flex ai-seo-writer-pro-gap-1">
                                <button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-ghost ai-seo-writer-pro-btn-sm ai-seo-edit-template" data-id="<?php echo esc_attr($tpl['id']); ?>">
                                    <?php esc_html_e('Edit', 'ai-seo-writer-pro'); ?>
                                </button>
                                <button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-ghost ai-seo-writer-pro-btn-sm ai-seo-delete-template" data-id="<?php echo esc_attr($tpl['id']); ?>" style="color: var(--ai-seo-danger);">
                                    <?php esc_html_e('Delete', 'ai-seo-writer-pro'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Template Form Template (for Modal) -->
<script type="text/template" id="ai-seo-template-form-template">
    <form id="ai-seo-template-form">
        <?php echo $security->get_nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <input type="hidden" id="template-id" value="">

        <div class="ai-seo-writer-pro-form-group">
            <label class="ai-seo-writer-pro-form-label" for="template-name"><?php esc_html_e('Template Name', 'ai-seo-writer-pro'); ?> <span class="required">*</span></label>
            <input type="text" id="template-name" class="ai-seo-writer-pro-form-control" placeholder="<?php esc_attr_e('e.g. Product Review Template', 'ai-seo-writer-pro'); ?>">
        </div>

        <div class="ai-seo-writer-pro-form-group">
            <label class="ai-seo-writer-pro-form-label" for="template-description"><?php esc_html_e('Description', 'ai-seo-writer-pro'); ?></label>
            <textarea id="template-description" class="ai-seo-writer-pro-form-control" rows="2" placeholder="<?php esc_attr_e('Brief description of this template', 'ai-seo-writer-pro'); ?>"></textarea>
        </div>

        <div class="ai-seo-writer-pro-form-group">
            <label class="ai-seo-writer-pro-form-label" for="template-category"><?php esc_html_e('Category', 'ai-seo-writer-pro'); ?></label>
            <select id="template-category" class="ai-seo-writer-pro-form-control">
                <option value="custom"><?php esc_html_e('Custom', 'ai-seo-writer-pro'); ?></option>
                <option value="blog"><?php esc_html_e('Blog', 'ai-seo-writer-pro'); ?></option>
                <option value="howto"><?php esc_html_e('How-To', 'ai-seo-writer-pro'); ?></option>
                <option value="review"><?php esc_html_e('Review', 'ai-seo-writer-pro'); ?></option>
                <option value="seo"><?php esc_html_e('SEO', 'ai-seo-writer-pro'); ?></option>
                <option value="marketing"><?php esc_html_e('Marketing', 'ai-seo-writer-pro'); ?></option>
                <option value="social"><?php esc_html_e('Social Media', 'ai-seo-writer-pro'); ?></option>
            </select>
        </div>

        <div class="ai-seo-writer-pro-form-group">
            <label class="ai-seo-writer-pro-form-label" for="template-system-prompt"><?php esc_html_e('System Prompt', 'ai-seo-writer-pro'); ?></label>
            <textarea id="template-system-prompt" class="ai-seo-writer-pro-form-control" rows="3" placeholder="<?php esc_attr_e('System instructions for the AI (optional)', 'ai-seo-writer-pro'); ?>"></textarea>
        </div>

        <div class="ai-seo-writer-pro-form-group">
            <label class="ai-seo-writer-pro-form-label" for="template-prompt"><?php esc_html_e('Prompt Template', 'ai-seo-writer-pro'); ?> <span class="required">*</span></label>
            <textarea id="template-prompt" class="ai-seo-writer-pro-form-control" rows="8" placeholder="<?php esc_attr_e('Use variables: {topic}, {keyword}, {secondary_keywords}, {tone}, {language}, {length}', 'ai-seo-writer-pro'); ?>"></textarea>
            <span class="ai-seo-writer-pro-form-hint">
                <?php esc_html_e('Available variables:', 'ai-seo-writer-pro'); ?>
                <code>{topic}</code>, <code>{keyword}</code>, <code>{secondary_keywords}</code>, <code>{tone}</code>, <code>{language}</code>, <code>{length}</code>
            </span>
        </div>
    </form>
</script>

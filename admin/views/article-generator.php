<?php
/**
 * View: AI Article Generator
 *
 * @var array $settings
 * @var \AI_SEO_Writer_Pro\Settings_Manager $settings_mgr
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 * @var \AI_SEO_Writer_Pro\AI_Providers $ai_providers
 * @var \AI_SEO_Writer_Pro\License_Manager $license
 */

if (!defined('ABSPATH')) {
    exit;
}

$languages = $settings_mgr->get_languages();
$tones     = $settings_mgr->get_tones();
$types     = $settings_mgr->get_content_types();
$lengths   = $settings_mgr->get_article_lengths();
$intents   = $settings_mgr->get_search_intents();
$providers = $ai_providers->get_available_providers();
?>

<div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-2">
    <!-- Input Form -->
    <div class="ai-seo-writer-pro-card ai-seo-writer-pro-relative">
        <div class="ai-seo-writer-pro-card-header">
            <div>
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Article Settings', 'ai-seo-writer-pro'); ?></h3>
                <p class="ai-seo-writer-pro-card-subtitle"><?php esc_html_e('Configure your article parameters', 'ai-seo-writer-pro'); ?></p>
            </div>
        </div>

        <form id="ai-seo-article-form">
            <?php echo $security->get_nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label" for="ai-seo-template-select">
                    <?php esc_html_e('Content Template', 'ai-seo-writer-pro'); ?>
                </label>
                <select id="ai-seo-template-select" class="ai-seo-writer-pro-form-control">
                    <option value=""><?php esc_html_e('— Select a template (optional) —', 'ai-seo-writer-pro'); ?></option>
                    <optgroup label="<?php esc_attr_e('Built-in Templates', 'ai-seo-writer-pro'); ?>">
                        <option value="blog_post"><?php esc_html_e('Standard Blog Post', 'ai-seo-writer-pro'); ?></option>
                        <option value="how_to"><?php esc_html_e('How-To Guide', 'ai-seo-writer-pro'); ?></option>
                        <option value="product_review"><?php esc_html_e('Product Review', 'ai-seo-writer-pro'); ?></option>
                        <option value="comparison"><?php esc_html_e('Comparison Article', 'ai-seo-writer-pro'); ?></option>
                        <option value="listicle"><?php esc_html_e('Listicle', 'ai-seo-writer-pro'); ?></option>
                        <option value="pillar_page"><?php esc_html_e('Pillar Page Content', 'ai-seo-writer-pro'); ?></option>
                    </optgroup>
                </select>
            </div>

            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label" for="ai-seo-topic">
                    <?php esc_html_e('Topic / Brief Description', 'ai-seo-writer-pro'); ?>
                    <span class="required">*</span>
                </label>
                <textarea
                    id="ai-seo-topic"
                    class="ai-seo-writer-pro-form-control"
                    data-required="true"
                    rows="3"
                    placeholder="<?php esc_attr_e('Describe the topic you want to write about...', 'ai-seo-writer-pro'); ?>"
                ></textarea>
                <span class="ai-seo-writer-pro-form-hint">
                    <?php esc_html_e('Provide a detailed description for better results.', 'ai-seo-writer-pro'); ?>
                </span>
            </div>

            <div class="ai-seo-writer-pro-form-row">
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-primary-keyword">
                        <?php esc_html_e('Primary Keyword', 'ai-seo-writer-pro'); ?>
                        <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="ai-seo-primary-keyword"
                        class="ai-seo-writer-pro-form-control"
                        data-required="true"
                        placeholder="<?php esc_attr_e('e.g. wordpress seo tips', 'ai-seo-writer-pro'); ?>"
                    >
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-secondary-keywords">
                        <?php esc_html_e('Secondary Keywords', 'ai-seo-writer-pro'); ?>
                    </label>
                    <input
                        type="text"
                        id="ai-seo-secondary-keywords"
                        class="ai-seo-writer-pro-form-control"
                        placeholder="<?php esc_attr_e('keyword1, keyword2, keyword3', 'ai-seo-writer-pro'); ?>"
                    >
                    <span class="ai-seo-writer-pro-form-hint"><?php esc_html_e('Comma-separated', 'ai-seo-writer-pro'); ?></span>
                </div>
            </div>

            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label" for="ai-seo-title-input">
                    <?php esc_html_e('Suggested Title (optional)', 'ai-seo-writer-pro'); ?>
                </label>
                <input
                    type="text"
                    id="ai-seo-title-input"
                    class="ai-seo-writer-pro-form-control"
                    placeholder="<?php esc_attr_e('Leave empty for AI to generate', 'ai-seo-writer-pro'); ?>"
                >
            </div>

            <div class="ai-seo-writer-pro-form-row">
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-language">
                        <?php esc_html_e('Language', 'ai-seo-writer-pro'); ?>
                    </label>
                    <select id="ai-seo-language" class="ai-seo-writer-pro-form-control">
                        <?php foreach ($languages as $code => $name) : ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php selected($settings['default_language'], $code); ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-tone">
                        <?php esc_html_e('Writing Tone', 'ai-seo-writer-pro'); ?>
                    </label>
                    <select id="ai-seo-tone" class="ai-seo-writer-pro-form-control">
                        <?php foreach ($tones as $key => $name) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($settings['default_tone'], $key); ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="ai-seo-writer-pro-form-row">
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-content-type">
                        <?php esc_html_e('Content Type', 'ai-seo-writer-pro'); ?>
                    </label>
                    <select id="ai-seo-content-type" class="ai-seo-writer-pro-form-control">
                        <?php foreach ($types as $key => $name) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($settings['default_content_type'], $key); ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-article-length">
                        <?php esc_html_e('Article Length', 'ai-seo-writer-pro'); ?>
                    </label>
                    <select id="ai-seo-article-length" class="ai-seo-writer-pro-form-control">
                        <?php foreach ($lengths as $words => $label) : ?>
                            <option value="<?php echo esc_attr($words); ?>" <?php selected($settings['default_article_length'], $words); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="ai-seo-writer-pro-form-row">
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-search-intent">
                        <?php esc_html_e('Search Intent', 'ai-seo-writer-pro'); ?>
                    </label>
                    <select id="ai-seo-search-intent" class="ai-seo-writer-pro-form-control">
                        <?php foreach ($intents as $key => $name) : ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-target-audience">
                        <?php esc_html_e('Target Audience', 'ai-seo-writer-pro'); ?>
                    </label>
                    <input
                        type="text"
                        id="ai-seo-target-audience"
                        class="ai-seo-writer-pro-form-control"
                        placeholder="<?php esc_attr_e('e.g. small business owners', 'ai-seo-writer-pro'); ?>"
                    >
                </div>
            </div>

            <div class="ai-seo-writer-pro-form-row">
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-provider">
                        <?php esc_html_e('AI Provider', 'ai-seo-writer-pro'); ?>
                    </label>
                    <select id="ai-seo-provider" class="ai-seo-writer-pro-form-control">
                        <?php foreach ($providers as $key => $provider) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($settings['default_ai_provider'], $key); ?>>
                                <?php echo esc_html($provider['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-model">
                        <?php esc_html_e('Model', 'ai-seo-writer-pro'); ?>
                    </label>
                    <select id="ai-seo-model" class="ai-seo-writer-pro-form-control">
                        <option value="gpt-4o" <?php selected($settings['default_model'], 'gpt-4o'); ?>>GPT-4o</option>
                        <option value="gpt-4o-mini" <?php selected($settings['default_model'], 'gpt-4o-mini'); ?>>GPT-4o Mini</option>
                        <option value="gpt-4-turbo" <?php selected($settings['default_model'], 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                        <option value="claude-3-opus" <?php selected($settings['default_model'], 'claude-3-opus'); ?>>Claude 3 Opus</option>
                        <option value="claude-3-sonnet" <?php selected($settings['default_model'], 'claude-3-sonnet'); ?>>Claude 3 Sonnet</option>
                        <option value="gemini-pro" <?php selected($settings['default_model'], 'gemini-pro'); ?>>Gemini Pro</option>
                    </select>
                </div>
            </div>

            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label" for="ai-seo-meta-desc-input">
                    <?php esc_html_e('Meta Description (optional)', 'ai-seo-writer-pro'); ?>
                </label>
                <textarea
                    id="ai-seo-meta-desc-input"
                    class="ai-seo-writer-pro-form-control"
                    rows="2"
                    placeholder="<?php esc_attr_e('Leave empty for AI to generate', 'ai-seo-writer-pro'); ?>"
                ></textarea>
            </div>

            <button type="button" id="ai-seo-generate-btn" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-lg ai-seo-writer-pro-btn-block">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                <?php esc_html_e('Generate Article', 'ai-seo-writer-pro'); ?>
            </button>
        </form>
    </div>

    <!-- Output Area -->
    <div>
        <!-- SEO Preview -->
        <div class="ai-seo-writer-pro-card">
            <div class="ai-seo-writer-pro-card-header">
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Google Search Preview', 'ai-seo-writer-pro'); ?></h3>
            </div>
            <div class="ai-seo-writer-pro-seo-preview">
                <div class="ai-seo-writer-pro-seo-preview-title" id="seo-preview-title">
                    <?php esc_html_e('Example Article Title', 'ai-seo-writer-pro'); ?>
                </div>
                <div class="ai-seo-writer-pro-seo-preview-url" id="seo-preview-url">
                    <?php echo esc_html(wp_parse_url(home_url(), PHP_URL_HOST)); ?>/example-article
                </div>
                <div class="ai-seo-writer-pro-seo-preview-desc" id="seo-preview-desc">
                    <?php esc_html_e('This is an example meta description that will appear in search engine results...', 'ai-seo-writer-pro'); ?>
                </div>
            </div>
        </div>

        <!-- Generated Output -->
        <div class="ai-seo-writer-pro-card ai-seo-writer-pro-relative">
            <div class="ai-seo-writer-pro-card-header">
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Generated Article', 'ai-seo-writer-pro'); ?></h3>
                <div id="ai-seo-article-results" class="ai-seo-writer-pro-flex ai-seo-writer-pro-gap-1 ai-seo-writer-pro-hidden">
                    <button type="button" id="ai-seo-save-draft-btn" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary ai-seo-writer-pro-btn-sm">
                        <?php esc_html_e('Save Draft', 'ai-seo-writer-pro'); ?>
                    </button>
                    <button type="button" id="ai-seo-publish-btn" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-sm">
                        <?php esc_html_e('Publish', 'ai-seo-writer-pro'); ?>
                    </button>
                </div>
            </div>

            <div id="ai-seo-output-area" class="ai-seo-writer-pro-output-area ai-seo-writer-pro-relative">
                <div class="ai-seo-writer-pro-output-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <p><?php esc_html_e('Your generated article will appear here.', 'ai-seo-writer-pro'); ?></p>
                    <p class="ai-seo-writer-pro-text-small"><?php esc_html_e('Fill in the form and click Generate Article to begin.', 'ai-seo-writer-pro'); ?></p>
                </div>
                <div class="ai-seo-writer-pro-output-content" style="display: none;"></div>
            </div>

            <input type="hidden" id="ai-seo-generated-content">
            <input type="hidden" id="ai-seo-generated-title">
            <input type="hidden" id="ai-seo-generated-seo-title">
            <input type="hidden" id="ai-seo-generated-meta-desc">
        </div>
    </div>
</div>

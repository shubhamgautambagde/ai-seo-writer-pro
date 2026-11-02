<?php
/**
 * View: General Settings
 *
 * @var array $settings
 * @var \AI_SEO_Writer_Pro\Settings_Manager $settings_mgr
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 * @var \AI_SEO_Writer_Pro\License_Manager $license
 */

if (!defined('ABSPATH')) {
    exit;
}

$languages = $settings_mgr->get_languages();
$tones     = $settings_mgr->get_tones();
$types     = $settings_mgr->get_content_types();
$lengths   = $settings_mgr->get_article_lengths();
?>

<form id="ai-seo-general-settings-form">
    <?php echo $security->get_nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <!-- Tabs -->
    <div class="ai-seo-writer-pro-tabs">
        <button type="button" class="ai-seo-writer-pro-tab active" data-target="#tab-defaults"><?php esc_html_e('Defaults', 'ai-seo-writer-pro'); ?></button>
        <button type="button" class="ai-seo-writer-pro-tab" data-target="#tab-advanced"><?php esc_html_e('Advanced', 'ai-seo-writer-pro'); ?></button>
        <button type="button" class="ai-seo-writer-pro-tab" data-target="#tab-license"><?php esc_html_e('License', 'ai-seo-writer-pro'); ?></button>
    </div>

    <!-- Defaults Tab -->
    <div id="tab-defaults" class="ai-seo-writer-pro-tab-panel active">
        <div class="ai-seo-writer-pro-card">
            <div class="ai-seo-writer-pro-card-header">
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Default Generation Settings', 'ai-seo-writer-pro'); ?></h3>
                <p class="ai-seo-writer-pro-card-subtitle ai-seo-writer-pro-mt-0"><?php esc_html_e('These values will be pre-filled in the article generator', 'ai-seo-writer-pro'); ?></p>
            </div>

            <div class="ai-seo-writer-pro-form-row">
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="setting-default_provider"><?php esc_html_e('Default AI Provider', 'ai-seo-writer-pro'); ?></label>
                    <select id="setting-default_provider" name="settings[default_ai_provider]" class="ai-seo-writer-pro-form-control">
                        <option value="openai" <?php selected($settings['default_ai_provider'], 'openai'); ?>>OpenAI</option>
                        <option value="gemini" <?php selected($settings['default_ai_provider'], 'gemini'); ?>>Google Gemini</option>
                        <option value="claude" <?php selected($settings['default_ai_provider'], 'claude'); ?>>Anthropic Claude</option>
                        <option value="openrouter" <?php selected($settings['default_ai_provider'], 'openrouter'); ?>>OpenRouter</option>
                    </select>
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="setting-default_model"><?php esc_html_e('Default Model', 'ai-seo-writer-pro'); ?></label>
                    <input type="text" id="setting-default_model" name="settings[default_model]" class="ai-seo-writer-pro-form-control" value="<?php echo esc_attr($settings['default_model']); ?>">
                </div>
            </div>

            <div class="ai-seo-writer-pro-form-row">
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="setting-default_language"><?php esc_html_e('Default Language', 'ai-seo-writer-pro'); ?></label>
                    <select id="setting-default_language" name="settings[default_language]" class="ai-seo-writer-pro-form-control">
                        <?php foreach ($languages as $code => $name) : ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php selected($settings['default_language'], $code); ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="setting-default_tone"><?php esc_html_e('Default Writing Tone', 'ai-seo-writer-pro'); ?></label>
                    <select id="setting-default_tone" name="settings[default_tone]" class="ai-seo-writer-pro-form-control">
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
                    <label class="ai-seo-writer-pro-form-label" for="setting-default_content_type"><?php esc_html_e('Default Content Type', 'ai-seo-writer-pro'); ?></label>
                    <select id="setting-default_content_type" name="settings[default_content_type]" class="ai-seo-writer-pro-form-control">
                        <?php foreach ($types as $key => $name) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($settings['default_content_type'], $key); ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="setting-default_article_length"><?php esc_html_e('Default Article Length', 'ai-seo-writer-pro'); ?></label>
                    <select id="setting-default_article_length" name="settings[default_article_length]" class="ai-seo-writer-pro-form-control">
                        <?php foreach ($lengths as $words => $label) : ?>
                            <option value="<?php echo esc_attr($words); ?>" <?php selected($settings['default_article_length'], $words); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="ai-seo-writer-pro-form-row-3 ai-seo-writer-pro-form-row">
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="setting-temperature"><?php esc_html_e('Temperature', 'ai-seo-writer-pro'); ?></label>
                    <input type="number" id="setting-temperature" name="settings[temperature]" class="ai-seo-writer-pro-form-control" step="0.1" min="0" max="2" value="<?php echo esc_attr($settings['temperature']); ?>">
                    <span class="ai-seo-writer-pro-form-hint"><?php esc_html_e('Higher = more creative, Lower = more focused', 'ai-seo-writer-pro'); ?></span>
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="setting-max_tokens"><?php esc_html_e('Max Generation Tokens', 'ai-seo-writer-pro'); ?></label>
                    <input type="number" id="setting-max_tokens" name="settings[max_generation_tokens]" class="ai-seo-writer-pro-form-control" min="256" max="128000" value="<?php echo esc_attr($settings['max_generation_tokens']); ?>">
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="setting-free_limit"><?php esc_html_e('Free Daily Limit', 'ai-seo-writer-pro'); ?></label>
                    <input type="number" id="setting-free_limit" name="settings[free_daily_limit]" class="ai-seo-writer-pro-form-control" min="1" max="100" value="<?php echo esc_attr($settings['free_daily_limit']); ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Tab -->
    <div id="tab-advanced" class="ai-seo-writer-pro-tab-panel">
        <div class="ai-seo-writer-pro-card">
            <div class="ai-seo-writer-pro-card-header">
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Advanced Settings', 'ai-seo-writer-pro'); ?></h3>
            </div>

            <div class="ai-seo-writer-pro-checkbox-group">
                <input type="checkbox" id="setting-seo_enabled" name="settings[seo_analyzer_enabled]" <?php checked($settings['seo_analyzer_enabled']); ?>>
                <label for="setting-seo_enabled"><?php esc_html_e('Enable SEO Analyzer', 'ai-seo-writer-pro'); ?></label>
            </div>

            <div class="ai-seo-writer-pro-checkbox-group">
                <input type="checkbox" id="setting-auto_save" name="settings[auto_save_drafts]" <?php checked($settings['auto_save_drafts']); ?>>
                <label for="setting-auto_save"><?php esc_html_e('Auto-save drafts during generation', 'ai-seo-writer-pro'); ?></label>
            </div>

            <div class="ai-seo-writer-pro-checkbox-group">
                <input type="checkbox" id="setting-image_enabled" name="settings[image_generation_enabled]" <?php checked($settings['image_generation_enabled']); ?>>
                <label for="setting-image_enabled"><?php esc_html_e('Enable AI Image Generation', 'ai-seo-writer-pro'); ?></label>
            </div>

            <div class="ai-seo-writer-pro-checkbox-group">
                <input type="checkbox" id="setting-logging" name="settings[logging_enabled]" <?php checked($settings['logging_enabled']); ?>>
                <label for="setting-logging"><?php esc_html_e('Enable error logging', 'ai-seo-writer-pro'); ?></label>
            </div>

            <div class="ai-seo-writer-pro-checkbox-group">
                <input type="checkbox" id="setting-gutenberg" name="settings[gutenberg_integration]" <?php checked($settings['gutenberg_integration']); ?>>
                <label for="setting-gutenberg"><?php esc_html_e('Enable Gutenberg block integration', 'ai-seo-writer-pro'); ?></label>
            </div>

            <div class="ai-seo-writer-pro-checkbox-group">
                <input type="checkbox" id="setting-delete_data" name="settings[delete_data_on_uninstall]" <?php checked($settings['delete_data_on_uninstall']); ?>>
                <label for="setting-delete_data"><?php esc_html_e('Delete all plugin data on uninstall', 'ai-seo-writer-pro'); ?></label>
            </div>

            <div class="ai-seo-writer-pro-form-group" style="margin-top: 20px;">
                <label class="ai-seo-writer-pro-form-label" for="setting-rate_limit"><?php esc_html_e('Rate Limit (requests per minute)', 'ai-seo-writer-pro'); ?></label>
                <input type="number" id="setting-rate_limit" name="settings[rate_limit_per_minute]" class="ai-seo-writer-pro-form-control" min="1" max="100" value="<?php echo esc_attr($settings['rate_limit_per_minute']); ?>">
            </div>
        </div>
    </div>

    <!-- License Tab -->
    <div id="tab-license" class="ai-seo-writer-pro-tab-panel">
        <div class="ai-seo-writer-pro-card">
            <div class="ai-seo-writer-pro-card-header">
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('License Activation', 'ai-seo-writer-pro'); ?></h3>
                <?php if ($license->is_pro()) : ?>
                    <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success"><?php esc_html_e('Activated', 'ai-seo-writer-pro'); ?></span>
                <?php endif; ?>
            </div>

            <?php if (!$license->is_pro()) : ?>
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="license-key"><?php esc_html_e('License Key', 'ai-seo-writer-pro'); ?></label>
                    <input type="text" id="license-key" class="ai-seo-writer-pro-form-control" placeholder="<?php esc_attr_e('Enter your license key', 'ai-seo-writer-pro'); ?>" autocomplete="off">
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="license-api-url"><?php esc_html_e('License API URL (optional)', 'ai-seo-writer-pro'); ?></label>
                    <input type="url" id="license-api-url" class="ai-seo-writer-pro-form-control" placeholder="https://licensing.example.com/api/v1">
                    <span class="ai-seo-writer-pro-form-hint"><?php esc_html_e('Leave empty for local development mode', 'ai-seo-writer-pro'); ?></span>
                </div>

                <button type="button" id="ai-seo-activate-license-btn" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary">
                    <?php esc_html_e('Activate License', 'ai-seo-writer-pro'); ?>
                </button>
            <?php else : ?>
                <?php
                $license_data = $license->get_license_data();
                ?>
                <div class="ai-seo-writer-pro-alert ai-seo-writer-pro-alert-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>
                        <strong><?php esc_html_e('License is active!', 'ai-seo-writer-pro'); ?></strong><br>
                        <?php if (!empty($license_data['expires_at'])) : ?>
                            <?php printf(esc_html__('Expires: %s', 'ai-seo-writer-pro'), esc_html($license_data['expires_at'])); ?>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Save Button -->
    <div class="ai-seo-writer-pro-flex ai-seo-writer-pro-flex-between">
        <p class="ai-seo-writer-pro-text-small ai-seo-writer-pro-text-muted">
            <?php esc_html_e('Changes take effect immediately.', 'ai-seo-writer-pro'); ?>
        </p>
        <button type="submit" id="ai-seo-save-settings-btn" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-lg">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?php esc_html_e('Save Settings', 'ai-seo-writer-pro'); ?>
        </button>
    </div>
</form>

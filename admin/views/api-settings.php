<?php
/**
 * View: API Settings
 *
 * @var array $providers
 * @var \AI_SEO_Writer_Pro\Settings_Manager $settings
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 */

if (!defined('ABSPATH')) {
    exit;
}

$provider_list = [
    'openai' => [
        'name' => 'OpenAI',
        'short' => 'OA',
        'color' => '#10a37f',
        'description' => 'GPT-4o, GPT-4o Mini, GPT-4 Turbo, DALL-E 3',
        'models' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo'],
        'docs_url' => 'https://platform.openai.com/api-keys',
    ],
    'gemini' => [
        'name' => 'Google Gemini',
        'short' => 'GG',
        'color' => '#4285f4',
        'description' => 'Gemini Pro, Gemini Flash, Gemini Ultra',
        'models' => ['gemini-pro', 'gemini-flash', 'gemini-ultra'],
        'docs_url' => 'https://aistudio.google.com/apikey',
    ],
    'claude' => [
        'name' => 'Anthropic Claude',
        'short' => 'AC',
        'color' => '#d97757',
        'description' => 'Claude 3 Opus, Sonnet, Haiku',
        'models' => ['claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku'],
        'docs_url' => 'https://console.anthropic.com/settings/keys',
    ],
    'openrouter' => [
        'name' => 'OpenRouter',
        'short' => 'OR',
        'color' => '#ff7000',
        'description' => 'Access 100+ models from various providers',
        'models' => ['openai/gpt-4o', 'anthropic/claude-3-opus', 'google/gemini-pro', 'meta-llama/llama-3-70b'],
        'docs_url' => 'https://openrouter.ai/keys',
    ],
    'custom' => [
        'name' => 'Custom API',
        'short' => 'CU',
        'color' => '#6366f1',
        'description' => 'Connect to any OpenAI-compatible API endpoint',
        'models' => ['custom-model'],
        'docs_url' => '#',
    ],
];
?>

<div class="ai-seo-writer-pro-alert ai-seo-writer-pro-alert-info ai-seo-writer-pro-mb-3">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    <span>
        <strong><?php esc_html_e('Security Notice:', 'ai-seo-writer-pro'); ?></strong>
        <?php esc_html_e('API keys are encrypted at rest using AES-256 and never exposed in HTML or JavaScript. Only masked versions are displayed.', 'ai-seo-writer-pro'); ?>
    </span>
</div>

<?php foreach ($provider_list as $provider_key => $provider) : ?>
    <?php
    $masked_key = $settings->get_masked_api_key($provider_key);
    $has_key    = $settings->has_api_key($provider_key);
    $current_settings = $settings->get_settings();
    ?>
    <div class="ai-seo-writer-pro-provider-card">
        <form class="ai-seo-provider-form" data-provider="<?php echo esc_attr($provider_key); ?>">
            <?php echo $security->get_nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <div class="ai-seo-writer-pro-provider-header">
                <div class="ai-seo-writer-pro-provider-info">
                    <div class="ai-seo-writer-pro-provider-logo" style="background: <?php echo esc_attr($provider['color']); ?>15; color: <?php echo esc_attr($provider['color']); ?>;">
                        <?php echo esc_html($provider['short']); ?>
                    </div>
                    <div>
                        <h4 class="ai-seo-writer-pro-provider-name"><?php echo esc_html($provider['name']); ?></h4>
                        <div class="ai-seo-writer-pro-provider-status">
                            <?php if ($has_key) : ?>
                                <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success"><?php esc_html_e('Configured', 'ai-seo-writer-pro'); ?></span>
                            <?php else : ?>
                                <span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-gray"><?php esc_html_e('Not configured', 'ai-seo-writer-pro'); ?></span>
                            <?php endif; ?>
                            <span class="ai-seo-writer-pro-text-small ai-seo-writer-pro-text-muted" style="margin-left: 8px;">
                                <?php echo esc_html($provider['description']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="ai-seo-writer-pro-flex ai-seo-writer-pro-gap-1">
                    <a href="<?php echo esc_url($provider['docs_url']); ?>" target="_blank" rel="noopener noreferrer" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-ghost ai-seo-writer-pro-btn-sm">
                        <?php esc_html_e('Get API Key', 'ai-seo-writer-pro'); ?>
                    </a>
                    <button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary ai-seo-writer-pro-btn-sm ai-seo-test-connection" data-provider="<?php echo esc_attr($provider_key); ?>" <?php echo $has_key ? '' : 'disabled'; ?>>
                        <?php esc_html_e('Test Connection', 'ai-seo-writer-pro'); ?>
                    </button>
                </div>
            </div>

            <div class="ai-seo-writer-pro-form-row">
                <div class="ai-seo-writer-pro-form-group" style="flex: 1;">
                    <label class="ai-seo-writer-pro-form-label" for="api-key-<?php echo esc_attr($provider_key); ?>">
                        <?php esc_html_e('API Key', 'ai-seo-writer-pro'); ?>
                    </label>
                    <div style="position: relative;">
                        <input
                            type="password"
                            id="api-key-<?php echo esc_attr($provider_key); ?>"
                            class="ai-seo-writer-pro-form-control api-key-input"
                            placeholder="<?php echo $has_key ? esc_attr__('Enter new key to update, or leave empty to keep current', 'ai-seo-writer-pro') : esc_attr__('sk-...', 'ai-seo-writer-pro'); ?>"
                            autocomplete="off"
                        >
                        <?php if ($has_key) : ?>
                            <span class="api-key-display" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--ai-seo-gray-500); font-family: monospace; pointer-events: none;">
                                <?php echo esc_html($masked_key); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ai-seo-writer-pro-form-group" style="min-width: 220px;">
                    <label class="ai-seo-writer-pro-form-label" for="model-<?php echo esc_attr($provider_key); ?>">
                        <?php esc_html_e('Default Model', 'ai-seo-writer-pro'); ?>
                    </label>
                    <select id="model-<?php echo esc_attr($provider_key); ?>" class="ai-seo-writer-pro-form-control model-select">
                        <?php foreach ($provider['models'] as $model) : ?>
                            <option value="<?php echo esc_attr($model); ?>" <?php selected($current_settings['default_model'], $model); ?>>
                                <?php echo esc_html($model); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if ($provider_key === 'custom') : ?>
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="custom-endpoint-<?php echo esc_attr($provider_key); ?>">
                        <?php esc_html_e('API Endpoint URL', 'ai-seo-writer-pro'); ?>
                    </label>
                    <input
                        type="url"
                        id="custom-endpoint-<?php echo esc_attr($provider_key); ?>"
                        class="ai-seo-writer-pro-form-control"
                        placeholder="https://your-api.example.com/v1/chat/completions"
                    >
                </div>
            <?php endif; ?>

            <div class="ai-seo-writer-pro-flex ai-seo-writer-pro-gap-1 ai-seo-writer-pro-flex-between">
                <div class="ai-seo-writer-pro-form-row" style="flex: 1; margin-bottom: 0;">
                    <div class="ai-seo-writer-pro-form-group" style="margin-bottom: 0;">
                        <label class="ai-seo-writer-pro-form-label"><?php esc_html_e('Usage Limit (per minute)', 'ai-seo-writer-pro'); ?></label>
                        <input type="number" class="ai-seo-writer-pro-form-control" value="10" min="1" max="100">
                    </div>
                    <div class="ai-seo-writer-pro-form-group" style="margin-bottom: 0;">
                        <label class="ai-seo-writer-pro-form-label"><?php esc_html_e('Max Tokens', 'ai-seo-writer-pro'); ?></label>
                        <input type="number" class="ai-seo-writer-pro-form-control" value="4000" min="256" max="128000">
                    </div>
                </div>
                <button type="submit" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary" style="align-self: flex-end;">
                    <?php esc_html_e('Save Settings', 'ai-seo-writer-pro'); ?>
                </button>
            </div>
        </form>
    </div>
<?php endforeach; ?>

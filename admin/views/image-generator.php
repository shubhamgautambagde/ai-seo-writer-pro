<?php
/**
 * View: AI Image Generator
 *
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 * @var \AI_SEO_Writer_Pro\License_Manager $license
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<?php if (!$license->is_feature_available('ai_image_generation')) : ?>
    <div class="ai-seo-writer-pro-card ai-seo-writer-pro-text-center" style="padding: 60px 24px;">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin-bottom: 16px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        <h3 style="margin: 0 0 8px; font-size: 20px;"><?php esc_html_e('AI Image Generation is a Pro Feature', 'ai-seo-writer-pro'); ?></h3>
        <p style="color: var(--ai-seo-gray-500); margin: 0 0 20px; max-width: 480px; margin-left: auto; margin-right: auto;">
            <?php esc_html_e('Generate stunning featured images and illustrations for your articles with AI. Upgrade to Pro to unlock this feature.', 'ai-seo-writer-pro'); ?>
        </p>
        <button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-lg">
            <?php esc_html_e('Upgrade to Pro', 'ai-seo-writer-pro'); ?>
        </button>
    </div>
<?php return; endif; ?>

<div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-2">
    <!-- Generation Form -->
    <div class="ai-seo-writer-pro-card">
        <div class="ai-seo-writer-pro-card-header">
            <div>
                <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Generate Image', 'ai-seo-writer-pro'); ?></h3>
                <p class="ai-seo-writer-pro-card-subtitle"><?php esc_html_e('Describe the image you want to create', 'ai-seo-writer-pro'); ?></p>
            </div>
        </div>

        <form id="ai-seo-image-form">
            <?php echo $security->get_nonce_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label" for="ai-seo-image-prompt">
                    <?php esc_html_e('Image Prompt', 'ai-seo-writer-pro'); ?>
                    <span class="required">*</span>
                </label>
                <textarea
                    id="ai-seo-image-prompt"
                    class="ai-seo-writer-pro-form-control"
                    data-required="true"
                    rows="4"
                    placeholder="<?php esc_attr_e('e.g. A modern office workspace with laptop, coffee cup, and notebook, warm lighting, professional photography style', 'ai-seo-writer-pro'); ?>"
                ></textarea>
                <span class="ai-seo-writer-pro-form-hint">
                    <?php esc_html_e('Be specific about style, lighting, colors, and composition for best results.', 'ai-seo-writer-pro'); ?>
                </span>
            </div>

            <div class="ai-seo-writer-pro-form-row">
                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-image-size">
                        <?php esc_html_e('Image Size', 'ai-seo-writer-pro'); ?>
                    </label>
                    <select id="ai-seo-image-size" class="ai-seo-writer-pro-form-control">
                        <option value="1024x1024"><?php esc_html_e('Square (1024×1024)', 'ai-seo-writer-pro'); ?></option>
                        <option value="1792x1024"><?php esc_html_e('Landscape (1792×1024)', 'ai-seo-writer-pro'); ?></option>
                        <option value="1024x1792"><?php esc_html_e('Portrait (1024×1792)', 'ai-seo-writer-pro'); ?></option>
                        <option value="512x512"><?php esc_html_e('Small Square (512×512)', 'ai-seo-writer-pro'); ?></option>
                    </select>
                </div>

                <div class="ai-seo-writer-pro-form-group">
                    <label class="ai-seo-writer-pro-form-label" for="ai-seo-image-style">
                        <?php esc_html_e('Style', 'ai-seo-writer-pro'); ?>
                    </label>
                    <select id="ai-seo-image-style" class="ai-seo-writer-pro-form-control">
                        <option value="vivid"><?php esc_html_e('Vivid', 'ai-seo-writer-pro'); ?></option>
                        <option value="natural"><?php esc_html_e('Natural', 'ai-seo-writer-pro'); ?></option>
                        <option value="cinematic"><?php esc_html_e('Cinematic', 'ai-seo-writer-pro'); ?></option>
                        <option value="anime"><?php esc_html_e('Anime', 'ai-seo-writer-pro'); ?></option>
                        <option value="digital-art"><?php esc_html_e('Digital Art', 'ai-seo-writer-pro'); ?></option>
                        <option value="photorealistic"><?php esc_html_e('Photorealistic', 'ai-seo-writer-pro'); ?></option>
                    </select>
                </div>
            </div>

            <div class="ai-seo-writer-pro-form-group">
                <label class="ai-seo-writer-pro-form-label" for="ai-seo-image-count">
                    <?php esc_html_e('Number of Images', 'ai-seo-writer-pro'); ?>
                </label>
                <select id="ai-seo-image-count" class="ai-seo-writer-pro-form-control">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </div>

            <button type="button" id="ai-seo-generate-image-btn" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-lg ai-seo-writer-pro-btn-block">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                <?php esc_html_e('Generate Images', 'ai-seo-writer-pro'); ?>
            </button>
        </form>

        <div class="ai-seo-writer-pro-alert ai-seo-writer-pro-alert-info" style="margin-top: 20px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>
                <strong><?php esc_html_e('Prompt Tips:', 'ai-seo-writer-pro'); ?></strong><br>
                <?php esc_html_e('Include subject, setting, lighting, style, mood, and camera details for higher quality results.', 'ai-seo-writer-pro'); ?>
            </span>
        </div>
    </div>

    <!-- Gallery -->
    <div class="ai-seo-writer-pro-card">
        <div class="ai-seo-writer-pro-card-header">
            <h3 class="ai-seo-writer-pro-card-title"><?php esc_html_e('Generated Images', 'ai-seo-writer-pro'); ?></h3>
        </div>

        <div id="ai-seo-image-gallery" class="ai-seo-writer-pro-image-gallery">
            <div class="ai-seo-writer-pro-image-item" style="display: flex; align-items: center; justify-content: center; border: 2px dashed var(--ai-seo-gray-200); background: transparent;">
                <div class="ai-seo-writer-pro-text-center ai-seo-writer-pro-text-muted">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 8px; opacity: 0.5;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <span class="ai-seo-writer-pro-text-small"><?php esc_html_e('Your generated images will appear here', 'ai-seo-writer-pro'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

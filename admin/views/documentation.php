<?php
/**
 * View: Documentation
 *
 * @var \AI_SEO_Writer_Pro\Security_Manager $security
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-2">
    <!-- Getting Started -->
    <div class="ai-seo-writer-pro-card">
        <div class="ai-seo-writer-pro-doc-section">
            <h3><?php esc_html_e('Getting Started', 'ai-seo-writer-pro'); ?></h3>
            <p><?php esc_html_e('Welcome to AI SEO Writer Pro! Follow these steps to get up and running quickly:', 'ai-seo-writer-pro'); ?></p>
            <ol>
                <li><strong><?php esc_html_e('Configure your API key', 'ai-seo-writer-pro'); ?></strong> — <?php esc_html_e('Go to API Settings and enter your OpenAI, Gemini, or Claude API key.', 'ai-seo-writer-pro'); ?></li>
                <li><strong><?php esc_html_e('Set your defaults', 'ai-seo-writer-pro'); ?></strong> — <?php esc_html_e('Visit General Settings to configure default language, tone, and content type.', 'ai-seo-writer-pro'); ?></li>
                <li><strong><?php esc_html_e('Generate your first article', 'ai-seo-writer-pro'); ?></strong> — <?php esc_html_e('Navigate to Article Generator, enter a topic and keyword, then click Generate.', 'ai-seo-writer-pro'); ?></li>
                <li><strong><?php esc_html_e('Review and publish', 'ai-seo-writer-pro'); ?></strong> — <?php esc_html_e('Check the SEO preview, make any edits, and publish to your WordPress site.', 'ai-seo-writer-pro'); ?></li>
            </ol>
        </div>

        <div class="ai-seo-writer-pro-doc-section">
            <h3><?php esc_html_e('Obtaining an API Key', 'ai-seo-writer-pro'); ?></h3>
            <p><strong><?php esc_html_e('OpenAI:', 'ai-seo-writer-pro'); ?></strong></p>
            <ol>
                <li><?php esc_html_e('Go to platform.openai.com and sign up or log in', 'ai-seo-writer-pro'); ?></li>
                <li><?php esc_html_e('Navigate to API Keys section', 'ai-seo-writer-pro'); ?></li>
                <li><?php esc_html_e('Click "Create new secret key"', 'ai-seo-writer-pro'); ?></li>
                <li><?php esc_html_e('Copy the key and paste it in API Settings', 'ai-seo-writer-pro'); ?></li>
            </ol>
            <p><strong><?php esc_html_e('Google Gemini:', 'ai-seo-writer-pro'); ?></strong></p>
            <ol>
                <li><?php esc_html_e('Visit aistudio.google.com', 'ai-seo-writer-pro'); ?></li>
                <li><?php esc_html_e('Click "Get API key"', 'ai-seo-writer-pro'); ?></li>
                <li><?php esc_html_e('Create a new API key and copy it', 'ai-seo-writer-pro'); ?></li>
            </ol>
        </div>
    </div>

    <!-- Features Guide -->
    <div class="ai-seo-writer-pro-card">
        <div class="ai-seo-writer-pro-doc-section">
            <h3><?php esc_html_e('Features Guide', 'ai-seo-writer-pro'); ?></h3>

            <h4 style="font-size: 15px; margin: 16px 0 8px; color: var(--ai-seo-gray-800);"><?php esc_html_e('AI Article Generator', 'ai-seo-writer-pro'); ?></h4>
            <p><?php esc_html_e('Create high-quality, SEO-optimized articles by providing a topic, target keyword, and optional parameters like tone, language, and length. The AI will generate a complete article with proper structure, headings, and meta information.', 'ai-seo-writer-pro'); ?></p>

            <h4 style="font-size: 15px; margin: 16px 0 8px; color: var(--ai-seo-gray-800);"><?php esc_html_e('SEO Analyzer', 'ai-seo-writer-pro'); ?></h4>
            <p><?php esc_html_e('Paste any content to get a detailed SEO analysis including readability score, keyword density, meta tag recommendations, heading structure analysis, and actionable suggestions for improvement.', 'ai-seo-writer-pro'); ?></p>

            <h4 style="font-size: 15px; margin: 16px 0 8px; color: var(--ai-seo-gray-800);"><?php esc_html_e('Content Templates', 'ai-seo-writer-pro'); ?></h4>
            <p><?php esc_html_e('Choose from 16 built-in templates or create your own custom templates to standardize your content generation process and maintain consistency across articles.', 'ai-seo-writer-pro'); ?></p>

            <h4 style="font-size: 15px; margin: 16px 0 8px; color: var(--ai-seo-gray-800);"><?php esc_html_e('Bulk Generation (Pro)', 'ai-seo-writer-pro'); ?></h4>
            <p><?php esc_html_e('Upload a CSV file with multiple topics and keywords to generate dozens of articles automatically. Monitor progress, pause/resume, and manage your content queue efficiently.', 'ai-seo-writer-pro'); ?></p>

            <h4 style="font-size: 15px; margin: 16px 0 8px; color: var(--ai-seo-gray-800);"><?php esc_html_e('AI Image Generator (Pro)', 'ai-seo-writer-pro'); ?></h4>
            <p><?php esc_html_e('Create stunning featured images and illustrations using AI. Describe what you want, choose a style and size, and upload directly to your WordPress media library.', 'ai-seo-writer-pro'); ?></p>
        </div>
    </div>
</div>

<!-- Best Practices & Shortcodes -->
<div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-2">
    <div class="ai-seo-writer-pro-card">
        <div class="ai-seo-writer-pro-doc-section">
            <h3><?php esc_html_e('Writing Better Prompts', 'ai-seo-writer-pro'); ?></h3>
            <p><?php esc_html_e('Follow these tips to get the best results from the AI:', 'ai-seo-writer-pro'); ?></p>
            <ul>
                <li><strong><?php esc_html_e('Be specific:', 'ai-seo-writer-pro'); ?></strong> <?php esc_html_e('Instead of "SEO tips", try "10 actionable SEO tips for WordPress beginners in 2026"', 'ai-seo-writer-pro'); ?></li>
                <li><strong><?php esc_html_e('Define the audience:', 'ai-seo-writer-pro'); ?></strong> <?php esc_html_e('Specify who the article is for (e.g., small business owners, developers, marketers)', 'ai-seo-writer-pro'); ?></li>
                <li><strong><?php esc_html_e('Include structure hints:', 'ai-seo-writer-pro'); ?></strong> <?php esc_html_e('Mention if you want specific sections, comparisons, or step-by-step instructions', 'ai-seo-writer-pro'); ?></li>
                <li><strong><?php esc_html_e('Set the tone:', 'ai-seo-writer-pro'); ?></strong> <?php esc_html_e('Choose the right tone for your audience and brand voice', 'ai-seo-writer-pro'); ?></li>
                <li><strong><?php esc_html_e('Use secondary keywords:', 'ai-seo-writer-pro'); ?></strong> <?php esc_html_e('Add related terms to help the AI cover the topic comprehensively', 'ai-seo-writer-pro'); ?></li>
            </ul>
        </div>
    </div>

    <div class="ai-seo-writer-pro-card">
        <div class="ai-seo-writer-pro-doc-section">
            <h3><?php esc_html_e('CSV Format for Bulk Generation', 'ai-seo-writer-pro'); ?></h3>
            <p><?php esc_html_e('Your CSV file should include the following columns (all optional except topic):', 'ai-seo-writer-pro'); ?></p>

            <div class="ai-seo-writer-pro-doc-code">topic,primary_keyword,secondary_keywords,title,language,tone,content_type,article_length
"10 WordPress SEO Tips","wordpress seo","seo optimization, speed, plugins",,en,professional,blog,1500
"Best Web Hosting 2026","best web hosting","hosting reviews, comparison",,en,informative,review,2000</div>

            <h4 style="font-size: 15px; margin: 20px 0 8px; color: var(--ai-seo-gray-800);"><?php esc_html_e('Column Reference', 'ai-seo-writer-pro'); ?></h4>
            <ul>
                <li><code>topic</code> — <?php esc_html_e('Main topic or description (required)', 'ai-seo-writer-pro'); ?></li>
                <li><code>primary_keyword</code> — <?php esc_html_e('Target SEO keyword', 'ai-seo-writer-pro'); ?></li>
                <li><code>secondary_keywords</code> — <?php esc_html_e('Pipe-separated list: keyword1|keyword2|keyword3', 'ai-seo-writer-pro'); ?></li>
                <li><code>title</code> — <?php esc_html_e('Optional custom title', 'ai-seo-writer-pro'); ?></li>
                <li><code>language</code> — <?php esc_html_e('Language code: en, es, de, fr, etc.', 'ai-seo-writer-pro'); ?></li>
                <li><code>tone</code> — <?php esc_html_e('professional, conversational, casual, formal, academic', 'ai-seo-writer-pro'); ?></li>
                <li><code>content_type</code> — <?php esc_html_e('blog, howto, review, comparison, listicle, news, landing_page, pillar', 'ai-seo-writer-pro'); ?></li>
                <li><code>article_length</code> — <?php esc_html_e('Target word count: 500, 1000, 1500, 2000, 3000, 5000', 'ai-seo-writer-pro'); ?></li>
            </ul>
        </div>
    </div>
</div>

<!-- Troubleshooting -->
<div class="ai-seo-writer-pro-card">
    <div class="ai-seo-writer-pro-doc-section">
        <h3><?php esc_html_e('Troubleshooting', 'ai-seo-writer-pro'); ?></h3>

        <div class="ai-seo-writer-pro-grid ai-seo-writer-pro-grid-2">
            <div>
                <h4 style="font-size: 15px; margin: 0 0 8px; color: var(--ai-seo-gray-800);"><?php esc_html_e('Common Issues', 'ai-seo-writer-pro'); ?></h4>
                <p><strong><?php esc_html_e('API Connection Error:', 'ai-seo-writer-pro'); ?></strong><br>
                <?php esc_html_e('Verify your API key is correct and has sufficient credits. Check the API Settings page and use the "Test Connection" button.', 'ai-seo-writer-pro'); ?></p>

                <p><strong><?php esc_html_e('Generation Timeout:', 'ai-seo-writer-pro'); ?></strong><br>
                <?php esc_html_e('Longer articles may take more time. Try reducing the article length or check your server PHP timeout settings.', 'ai-seo-writer-pro'); ?></p>

                <p><strong><?php esc_html_e('Rate Limit Reached:', 'ai-seo-writer-pro'); ?></strong><br>
                <?php esc_html_e('Free plans have daily generation limits. Upgrade to Pro for unlimited generations or wait until the next day.', 'ai-seo-writer-pro'); ?></p>
            </div>

            <div>
                <h4 style="font-size: 15px; margin: 0 0 8px; color: var(--ai-seo-gray-800);"><?php esc_html_e('Support', 'ai-seo-writer-pro'); ?></h4>
                <p><?php esc_html_e('If you encounter issues, please check the following before contacting support:', 'ai-seo-writer-pro'); ?></p>
                <ul>
                    <li><?php esc_html_e('WordPress and PHP versions meet minimum requirements (WP 6.0+, PHP 8.1+)', 'ai-seo-writer-pro'); ?></li>
                    <li><?php esc_html_e('Your API key is valid and has credits available', 'ai-seo-writer-pro'); ?></li>
                    <li><?php esc_html_e('No other plugins are conflicting (try disabling other plugins temporarily)', 'ai-seo-writer-pro'); ?></li>
                    <li><?php esc_html_e('Error logging is enabled and check the debug log for details', 'ai-seo-writer-pro'); ?></li>
                </ul>
                <p style="margin-top: 16px;">
                    <a href="#" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary ai-seo-writer-pro-btn-sm">
                        <?php esc_html_e('Contact Support', 'ai-seo-writer-pro'); ?>
                    </a>
                    <a href="#" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary ai-seo-writer-pro-btn-sm">
                        <?php esc_html_e('View FAQ', 'ai-seo-writer-pro'); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

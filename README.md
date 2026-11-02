# AI SEO Writer Pro

**Advanced AI-Powered WordPress SEO Article Generator Plugin**

Generate high-quality, SEO-optimized articles using AI directly from your WordPress Admin Dashboard.

## Features

### 🤖 AI Provider Integration
- **Multiple AI Providers**: OpenAI, Google Gemini, Anthropic Claude, OpenRouter, Custom API
- API key management with secure encryption
- Model selection per provider
- API connection testing
- Token usage tracking and history
- Comprehensive error handling
- API usage limits and rate limiting

### ✍️ AI Article Generator
- **Input Fields**: Article Topic, Primary Keyword, Secondary Keywords, Target Audience, Search Intent, Language, Article Length, Writing Tone, Content Type, Competitor URL
- **10 Content Types**: SEO Blog Articles, How-to Guides, Informational Articles, Product Reviews, Comparison Articles, Listicles, News Articles, Local SEO Articles, Landing Page Content, Pillar Content

### 🎯 Advanced SEO Optimization
- Auto-generated SEO Title, Meta Description, URL Slug
- H1, H2, H3 heading hierarchy
- Keyword placement optimization
- Internal & External linking suggestions
- Image Alt Text generation
- FAQ Section generation
- Table of Contents
- Schema Markup (Article, FAQ, HowTo)
- Open Graph & Twitter Card metadata
- Social Media descriptions

### 🛠️ AI Content Writing Features
- Regenerate specific paragraphs
- Rewrite selected text
- Expand or shorten content
- Change writing tone
- Improve readability
- Fix grammar
- Remove repetitive content

### 📦 Bulk Article Generator
- Import topics via CSV
- Queue-based processing
- **WP-Cron background processing** (never blocks PHP requests)
- Real-time progress tracking
- Pause and resume queue
- Automatic saving as drafts

### 📤 WordPress Publishing Integration
- Save as Draft
- Publish Immediately
- Schedule Publication
- Categories & Tags support
- Featured Image management
- Edit generated content
- Preview before publishing
- **Gutenberg-compatible** output

### 🖼️ AI Image Generator
- Generate featured images and article illustrations
- Generate image prompts from article context
- Set image alt text automatically
- Upload directly to WordPress Media Library
- Configurable external image-generation APIs

### 📊 SEO Article Score Analyzer
- Keyword density analysis
- Keyword placement checks
- Content length evaluation
- Heading structure analysis
- Meta title & description length
- Readability scoring (Flesch-Kincaid)
- Internal & External link detection
- Image alt attribute checks
- FAQ availability detection
- **SEO Score (0–100)**, Readability Score, Content Quality Score
- Optimization suggestions

> ⚠️ **Disclaimer**: SEO scores are guidelines only and do not guarantee Google rankings.

### 📋 Content Templates
- **16 Ready-to-use Templates**:
  1. SEO Blog Writer
  2. Ultimate Guide Writer
  3. Product Review Writer
  4. Affiliate Article Writer
  5. Local SEO Article Writer
  6. How-to Article Writer
  7. Comparison Article Writer
  8. News Article Writer
  9. Website Landing Page Writer
  10. FAQ Generator
  11. Meta Description Generator
  12. Blog Introduction Generator
  13. Blog Conclusion Generator
  14. Social Media Content Generator
  15. E-commerce Product Description Writer
  16. Pillar Content Generator
- Create and save custom templates

### 📈 Dashboard
- Modern SaaS-style interface
- Statistics widgets
- Monthly generation charts
- Recent articles overview
- API usage tracking

## Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.3 or higher
- Valid API key from at least one supported AI provider

## Installation

1. Download the `ai-seo-writer-pro.zip` file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file and click "Install Now"
4. Activate the plugin
5. Navigate to **AI SEO Writer Pro → API Settings**
6. Enter your API key(s) for the AI provider(s) you want to use
7. Click "Test Connection" to verify
8. Start generating articles from **AI SEO Writer Pro → AI Article Generator**

## Configuration

### API Settings
Navigate to **AI SEO Writer Pro → API Settings** to configure your AI providers:

- **OpenAI**: Get your API key from [platform.openai.com](https://platform.openai.com/api-keys)
- **Google Gemini**: Get your API key from [aistudio.google.com](https://aistudio.google.com/app/apikey)
- **Anthropic Claude**: Get your API key from [console.anthropic.com](https://console.anthropic.com/settings/keys)
- **OpenRouter**: Get your API key from [openrouter.ai](https://openrouter.ai/keys)
- **Custom API**: Configure any compatible REST API endpoint

### General Settings
Navigate to **AI SEO Writer Pro → General Settings** to configure:
- Default AI provider and model
- Default language, tone, and content type
- Article generation parameters
- Rate limiting
- Image generation settings

## Usage

### Generating a Single Article
1. Go to **AI SEO Writer Pro → AI Article Generator**
2. Fill in the article topic, primary keyword, and other parameters
3. Click "Generate Article"
4. Review the generated content and SEO elements
5. Edit if needed, then save as draft or publish

### Bulk Generation
1. Go to **AI SEO Writer Pro → Bulk Generator**
2. Prepare a CSV file with your topics (see Documentation for format)
3. Upload the CSV file
4. Monitor progress from the queue management interface
5. Articles are automatically saved as drafts as they complete

### SEO Analysis
1. Go to **AI SEO Writer Pro → SEO Analyzer**
2. Paste your content and enter the focus keyword
3. Click "Analyze SEO"
4. Review the scores and optimization suggestions

## Database Tables

The plugin creates the following custom database tables on activation:
- `wp_ai_seo_writer_pro_history` - Article generation history
- `wp_ai_seo_writer_pro_api_logs` - API usage and error logs
- `wp_ai_seo_writer_pro_queue` - Bulk generation queue
- `wp_ai_seo_writer_pro_templates` - Custom content templates
- `wp_ai_seo_writer_pro_stats` - Daily usage statistics

## Security

- Nonce verification on all forms and AJAX requests
- Capability checks (requires `manage_options`)
- Input sanitization on all user data
- Output escaping on all displayed content
- Prepared SQL queries via WordPress `$wpdb`
- API keys encrypted at rest using AES-256-CBC
- API keys never exposed in frontend JavaScript or HTML
- REST API permission callbacks
- Rate limiting on generation requests
- API error logging

## Free vs Pro Version

### Free Version
- Limited daily article generation (configurable, default: 5)
- Basic SEO analysis
- Basic content templates
- Manual publishing only

### Pro Version
- Unlimited configurable generation quotas
- Multiple AI providers simultaneously
- Bulk article generation
- Advanced SEO analyzer
- Custom templates creation
- AI image generation
- Advanced scheduling
- Detailed analytics

## Support

For documentation and usage guide, navigate to **AI SEO Writer Pro → Documentation** in your WordPress admin.

## Changelog

### 1.0.0
- Initial release
- All core features implemented

## License

GPL v2 or later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for details.

# AI SEO Writer Pro - Installation Guide

## System Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.3 or higher
- **HTTPS**: Recommended for API communication security
- **PHP Extensions**: curl, json, openssl, mbstring

## Installation Methods

### Method 1: WordPress Admin Upload (Recommended)

1. **Download the Plugin**
   - Save the `ai-seo-writer-pro.zip` file to your computer

2. **Access WordPress Admin**
   - Log in to your WordPress website at `/wp-admin`

3. **Navigate to Plugins**
   - Go to **Plugins → Add New** from the left sidebar menu

4. **Upload the Plugin**
   - Click the **Upload Plugin** button at the top of the page
   - Click **Choose File** and select the `ai-seo-writer-pro.zip` file
   - Click **Install Now**

5. **Activate the Plugin**
   - Once installation completes, click **Activate Plugin**

6. **Verify Installation**
   - You should now see **AI SEO Writer Pro** in the left sidebar menu

### Method 2: FTP Upload

1. **Extract the ZIP File**
   - Extract `ai-seo-writer-pro.zip` on your local computer
   - You should have a folder named `ai-seo-writer-pro`

2. **Connect via FTP**
   - Use an FTP client (FileZilla, Cyberduck, etc.) to connect to your server

3. **Upload the Plugin Folder**
   - Navigate to `/wp-content/plugins/` on your server
   - Upload the entire `ai-seo-writer-pro` folder

4. **Activate in WordPress**
   - Go to **Plugins → Installed Plugins** in WordPress admin
   - Find **AI SEO Writer Pro** and click **Activate**

## Post-Installation Setup

### Step 1: Configure API Keys

1. Go to **AI SEO Writer Pro → API Settings**
2. Select your preferred AI provider tab
3. Enter your API key
4. Click **Save Changes**
5. Click **Test Connection** to verify the key works

#### Where to Get API Keys:

| Provider | URL |
|----------|-----|
| OpenAI | https://platform.openai.com/api-keys |
| Google Gemini | https://aistudio.google.com/app/apikey |
| Anthropic Claude | https://console.anthropic.com/settings/keys |
| OpenRouter | https://openrouter.ai/keys |

### Step 2: Configure General Settings

1. Go to **AI SEO Writer Pro → General Settings**
2. Set your default AI provider and model
3. Configure default article parameters
4. Click **Save Changes**

### Step 3: Generate Your First Article

1. Go to **AI SEO Writer Pro → AI Article Generator**
2. Enter:
   - **Article Topic**: What your article is about
   - **Primary Keyword**: Main SEO keyword
   - **Secondary Keywords**: Related keywords (optional)
   - **Target Audience**: Who you're writing for
   - **Search Intent**: informational, commercial, etc.
   - **Language**: Article language
   - **Article Length**: Target word count
   - **Writing Tone**: Professional, conversational, etc.
   - **Content Type**: Blog post, how-to, review, etc.
3. Click **Generate Article**
4. Wait for generation to complete
5. Review the content and SEO suggestions
6. Click **Save as Draft** or **Publish Now**

## CSV Format for Bulk Generation

Create a CSV file with the following columns (first row must be headers):

```
topic,primary_keyword,secondary_keywords,target_audience,search_intent,language,article_length,tone,content_type,competitor_url,categories,tags
```

**Example:**
```csv
topic,primary_keyword,secondary_keywords,target_audience,search_intent,language,article_length,tone,content_type,competitor_url,categories,tags
"Best Coffee Makers 2026","best coffee maker","espresso machine,drip coffee,pour over","home coffee enthusiasts","commercial",en,1500,professional,review,"https://example.com/coffee-makers","Kitchen,Products","coffee,appliances,reviews"
"How to Start a Blog","start a blog","wordpress blogging,blogging tips","beginner bloggers","informational",en,2000,howto,blog,,Tutorials,"blogging,wordpress,tutorial"
```

**Column Details:**
- `topic` (required): Article topic/title
- `primary_keyword` (required): Main SEO keyword
- `secondary_keywords`: Comma-separated list
- `target_audience`: Description of target readers
- `search_intent`: informational, navigational, commercial, transactional
- `language`: en, es, fr, de, it, pt, nl, ru, ja, zh, ko, ar, hi, tr, pl, sv
- `article_length`: 500, 1000, 1500, 2000, 3000, 5000
- `tone`: professional, conversational, formal, casual, academic, persuasive, informative, entertaining, friendly, authoritative, technical, simplified
- `content_type`: blog, howto, informational, review, comparison, listicle, news, local_seo, landing_page, pillar
- `competitor_url`: Optional URL for reference
- `categories`: Comma-separated WordPress category names/IDs
- `tags`: Comma-separated WordPress tag names

## Verifying the Installation

### Check Database Tables

The plugin creates 5 custom tables on activation. You can verify using phpMyAdmin or a database tool:

1. `wp_ai_seo_writer_pro_history`
2. `wp_ai_seo_writer_pro_api_logs`
3. `wp_ai_seo_writer_pro_queue`
4. `wp_ai_seo_writer_pro_templates`
5. `wp_ai_seo_writer_pro_stats`

### Check WP-Cron Events

For bulk generation to work, WP-Cron must be functioning. Verify:

1. Install **WP Crontrol** plugin
2. Go to **Tools → Cron Events**
3. Look for `ai_seo_writer_pro_bulk_queue_cron`
4. It should be scheduled to run "Every Minute"

### Test a Generation

1. Go to **AI SEO Writer Pro → AI Article Generator**
2. Enter a test topic and keyword
3. Click **Generate Article**
4. If you see content appear, the installation is working correctly

## Troubleshooting

### Plugin Won't Activate

**Symptom**: Error message when activating
**Solutions**:
- Check PHP version is 8.1 or higher: `php -v`
- Ensure WordPress is version 6.0 or higher
- Check file permissions: folders should be 755, files 644
- Check PHP error logs for specific error messages

### API Connection Test Fails

**Symptom**: "Connection failed" when testing API
**Solutions**:
- Verify API key is correct (no extra spaces)
- Check your API provider account has credits/quota
- Ensure your server can make outbound HTTPS requests
- Check if a firewall or security plugin is blocking requests
- Verify cURL extension is enabled in PHP

### Bulk Generation Not Processing

**Symptom**: Queue items stay "Pending"
**Solutions**:
- Verify WP-Cron is working: Install WP Crontrol plugin
- Check if `DISABLE_WP_CRON` is set to `true` in `wp-config.php`
- For high-volume sites, consider setting up a real cron job:
  ```
  * * * * * wget -q -O - https://your-site.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
  ```
- Ensure no caching plugin is caching wp-cron.php

### White Screen / Fatal Error

**Symptom**: Blank page when accessing plugin pages
**Solutions**:
- Enable WordPress debugging in `wp-config.php`:
  ```php
  define('WP_DEBUG', true);
  define('WP_DEBUG_LOG', true);
  ```
- Check `wp-content/debug.log` for error details
- Ensure all plugin files were uploaded correctly
- Check for conflicts with other plugins by deactivating them one by one

### Generated Content is Poor Quality

**Solutions**:
- Try a different AI model (e.g., GPT-4o instead of GPT-3.5-turbo)
- Provide more specific topic descriptions
- Add more secondary keywords for context
- Specify a clear target audience
- Adjust the tone parameter
- Increase the target article length

## Uninstallation

1. Go to **Plugins → Installed Plugins**
2. Deactivate **AI SEO Writer Pro**
3. Click **Delete**
4. Confirm deletion

**Note**: By default, plugin data is preserved on uninstall. To delete all data:
1. Before uninstalling, go to **AI SEO Writer Pro → General Settings → Advanced**
2. Enable "Delete all data on uninstall"
3. Save changes
4. Then deactivate and delete the plugin

## Getting Help

For more detailed documentation, go to **AI SEO Writer Pro → Documentation** in your WordPress admin panel.

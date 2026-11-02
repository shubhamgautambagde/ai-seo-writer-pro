<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Content Templates - Manages built-in and custom AI content templates
 */
class Content_Templates {

    /**
     * Cached built-in templates
     * @var array|null
     */
    private $builtin_templates = null;

    /**
     * Initialize the Content Templates module
     */
    public function init() {
        add_action('wp_ajax_ai_seo_writer_pro_get_templates', [$this, 'ajax_get_templates']);
        add_action('wp_ajax_ai_seo_writer_pro_get_template', [$this, 'ajax_get_template']);
        add_action('wp_ajax_ai_seo_writer_pro_create_template', [$this, 'ajax_create_template']);
        add_action('wp_ajax_ai_seo_writer_pro_update_template', [$this, 'ajax_update_template']);
        add_action('wp_ajax_ai_seo_writer_pro_delete_template', [$this, 'ajax_delete_template']);
        add_action('wp_ajax_ai_seo_writer_pro_apply_template', [$this, 'ajax_apply_template']);
    }

    /**
     * Get all 16 built-in templates
     *
     * @return array
     */
    public function get_builtin_templates() {
        if ($this->builtin_templates !== null) {
            return $this->builtin_templates;
        }

        $this->builtin_templates = [
            [
                'name'              => __('SEO Blog Writer', 'ai-seo-writer-pro'),
                'slug'              => 'seo-blog-writer',
                'description'       => __('Generate comprehensive, SEO-optimized blog articles with proper keyword placement, heading structure, and internal linking suggestions.', 'ai-seo-writer-pro'),
                'category'          => 'blogging',
                'system_prompt'     => 'You are an expert SEO content writer and copywriter. Create high-quality, engaging, and well-researched content that ranks well in search engines while providing real value to readers. Follow SEO best practices including natural keyword usage, proper heading hierarchy, and readability.',
                'prompt_template'   => "Write a comprehensive, SEO-optimized blog article about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nSearch Intent: {search_intent}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\nContent Type: {content_type}\n\nRequirements:\n1. Include the primary keyword in the SEO title, first paragraph, and at least 2-3 subheadings\n2. Use a proper H1, H2, H3 heading structure\n3. Include an engaging introduction and conclusion\n4. Aim for keyword density of 0.5-2.5%\n5. Add internal link suggestions where relevant\n6. Make the content actionable and valuable\n7. End with a FAQ section of 3-5 relevant questions",
                'default_settings'  => [
                    'article_length' => 1500,
                    'tone'           => 'professional',
                    'content_type'   => 'blog',
                    'temperature'    => 0.7,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Ultimate Guide Writer', 'ai-seo-writer-pro'),
                'slug'              => 'ultimate-guide-writer',
                'description'       => __('Create in-depth, comprehensive ultimate guides that cover every aspect of a topic. Perfect for pillar content and establishing topical authority.', 'ai-seo-writer-pro'),
                'category'          => 'blogging',
                'system_prompt'     => 'You are an expert technical writer and subject matter expert. Create comprehensive, authoritative ultimate guides that leave no question unanswered. Structure content logically with clear sections, examples, and practical advice.',
                'prompt_template'   => "Create the ultimate comprehensive guide on: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nSearch Intent: {search_intent}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\n\nStructure the guide with:\n1. A compelling introduction explaining why this topic matters\n2. A table of contents overview\n3. Multiple detailed sections covering all aspects of the topic\n4. Practical step-by-step instructions where applicable\n5. Common mistakes to avoid\n6. Pro tips and best practices\n7. Real-world examples or case studies\n8. A comprehensive FAQ section\n9. A conclusion with next steps",
                'default_settings'  => [
                    'article_length' => 3000,
                    'tone'           => 'authoritative',
                    'content_type'   => 'pillar',
                    'temperature'    => 0.6,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Product Review Writer', 'ai-seo-writer-pro'),
                'slug'              => 'product-review-writer',
                'description'       => __('Write honest, detailed product reviews that help readers make informed purchasing decisions while optimizing for commercial intent keywords.', 'ai-seo-writer-pro'),
                'category'          => 'commercial',
                'system_prompt'     => 'You are an expert product reviewer with years of experience testing and evaluating products. Write honest, balanced, and helpful reviews that readers can trust. Include both pros and cons, and base recommendations on real value.',
                'prompt_template'   => "Write a detailed, honest product review for: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\n\nReview Structure:\n1. Attention-grabbing title with product name and primary keyword\n2. Introduction: What the product is, who it's for, and a quick verdict\n3. Product Overview: Key features, specifications, and what's included\n4. Detailed Analysis: Performance, build quality, ease of use, value for money\n5. Pros and Cons: Clear bullet points\n6. Comparison: How it stacks up against alternatives\n7. Who should buy it (and who shouldn't)\n8. Final Verdict and recommendation\n9. FAQ section",
                'default_settings'  => [
                    'article_length' => 1500,
                    'tone'           => 'conversational',
                    'content_type'   => 'review',
                    'temperature'    => 0.7,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Affiliate Article Writer', 'ai-seo-writer-pro'),
                'slug'              => 'affiliate-article-writer',
                'description'       => __('Generate high-converting affiliate content including best-of lists, product comparisons, and buying guides that drive affiliate revenue.', 'ai-seo-writer-pro'),
                'category'          => 'commercial',
                'system_prompt'     => 'You are an expert affiliate marketer and content creator. Write compelling product comparisons and buying guides that genuinely help readers make purchasing decisions. Disclose affiliate relationships naturally and maintain trust.',
                'prompt_template'   => "Create an affiliate marketing article about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nSearch Intent: {search_intent}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\n\nContent Structure:\n1. SEO-optimized title with buying intent\n2. Introduction: Address the reader's problem/need\n3. Quick Summary / Top Picks at a glance\n4. Detailed Buying Guide: Key factors to consider\n5. Individual Product Reviews (3-7 products):\n   - Product name and image mention\n   - Key features\n   - Pros and cons\n   - Who it's best for\n   - Call-to-action\n6. Comparison table or summary\n7. Final recommendation\n8. FAQ\n\nInclude natural affiliate link placement opportunities.",
                'default_settings'  => [
                    'article_length' => 2000,
                    'tone'           => 'persuasive',
                    'content_type'   => 'review',
                    'temperature'    => 0.7,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Local SEO Article Writer', 'ai-seo-writer-pro'),
                'slug'              => 'local-seo-article-writer',
                'description'       => __('Create location-specific content optimized for local search queries. Include local keywords, service areas, and geographic relevance.', 'ai-seo-writer-pro'),
                'category'          => 'local_seo',
                'system_prompt'     => 'You are a local SEO expert and content writer. Create content that ranks well for local search queries by naturally incorporating location names, service areas, and local intent signals.',
                'prompt_template'   => "Write a local SEO-optimized article about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\n\nLocal SEO Requirements:\n1. Naturally include location names and service areas throughout\n2. Mention local landmarks, neighborhoods, or regions\n3. Include service area information\n4. Add local relevance signals\n5. Include calls-to-action relevant to local customers\n6. Mention operating hours or contact information where appropriate\n\nStructure:\n1. Local-focused SEO title\n2. Introduction with geographic context\n3. Services/solutions offered in the area\n4. Why choose local / benefits of local service\n5. Service areas covered\n6. Frequently asked local questions\n7. Local contact and next steps",
                'default_settings'  => [
                    'article_length' => 1000,
                    'tone'           => 'friendly',
                    'content_type'   => 'local_seo',
                    'temperature'    => 0.7,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('How-to Article Writer', 'ai-seo-writer-pro'),
                'slug'              => 'howto-article-writer',
                'description'       => __('Generate step-by-step how-to guides and tutorials that are easy to follow, with clear instructions, tips, and troubleshooting advice.', 'ai-seo-writer-pro'),
                'category'          => 'blogging',
                'system_prompt'     => 'You are an expert technical writer and educator. Create clear, easy-to-follow how-to guides that anyone can understand. Use numbered steps, include tips, and anticipate common problems.',
                'prompt_template'   => "Write a comprehensive step-by-step how-to guide on: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\n\nGuide Structure:\n1. SEO title starting with 'How to'\n2. Introduction: What the reader will learn and why it matters\n3. Prerequisites / What You'll Need section\n4. Step-by-step instructions with numbered steps:\n   - Each step clearly explained\n   - Include pro tips and warnings\n   - Suggest relevant images for each step\n5. Common mistakes to avoid\n6. Troubleshooting section\n7. Pro tips for best results\n8. FAQ\n9. Conclusion and next steps",
                'default_settings'  => [
                    'article_length' => 1500,
                    'tone'           => 'informative',
                    'content_type'   => 'howto',
                    'temperature'    => 0.6,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Comparison Article Writer', 'ai-seo-writer-pro'),
                'slug'              => 'comparison-article-writer',
                'description'       => __('Create detailed product or service comparison articles that help readers choose between options with clear pros, cons, and recommendations.', 'ai-seo-writer-pro'),
                'category'          => 'commercial',
                'system_prompt'     => 'You are an expert reviewer and comparison specialist. Create balanced, detailed comparison articles that help readers make informed decisions. Be fair and objective.',
                'prompt_template'   => "Write a detailed comparison article about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nSearch Intent: {search_intent}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\n\nStructure:\n1. SEO title with 'vs' or comparison phrasing\n2. Introduction: Brief overview of both options and the comparison goal\n3. Quick Verdict: Who should choose which (at a glance)\n4. Side-by-side comparison table\n5. Detailed comparison of each option:\n   - Key features\n   - Performance\n   - Pricing\n   - Pros and cons\n   - Ideal use cases\n6. Head-to-head comparison by category (features, price, support, etc.)\n7. Factors to consider when choosing\n8. Final recommendation: Which one and why\n9. FAQ",
                'default_settings'  => [
                    'article_length' => 1800,
                    'tone'           => 'informative',
                    'content_type'   => 'comparison',
                    'temperature'    => 0.6,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('News Article Writer', 'ai-seo-writer-pro'),
                'slug'              => 'news-article-writer',
                'description'       => __('Generate timely, well-structured news articles following journalistic principles with the inverted pyramid structure and proper attribution.', 'ai-seo-writer-pro'),
                'category'          => 'blogging',
                'system_prompt'     => 'You are a professional journalist and news writer. Write clear, accurate, and engaging news articles following the inverted pyramid structure. Focus on facts, clarity, and proper context.',
                'prompt_template'   => "Write a news article about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\n\nJournalistic Structure (Inverted Pyramid):\n1. SEO-optimized headline\n2. Lead paragraph (Lede): Who, What, When, Where, Why, How - the most important information first\n3. Supporting details and context\n4. Quotes and expert opinions (use [Source Name] as placeholders)\n5. Background information and broader context\n6. Implications and what happens next\n7. Additional relevant details\n\nRequirements:\n- Objective, factual tone\n- Clear attribution of information\n- No sensationalism\n- Proper paragraph breaks for readability",
                'default_settings'  => [
                    'article_length' => 800,
                    'tone'           => 'professional',
                    'content_type'   => 'news',
                    'temperature'    => 0.5,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Website Landing Page Writer', 'ai-seo-writer-pro'),
                'slug'              => 'landing-page-writer',
                'description'       => __('Create high-converting landing page copy with compelling headlines, benefit-driven content, social proof, and clear calls-to-action.', 'ai-seo-writer-pro'),
                'category'          => 'marketing',
                'system_prompt'     => 'You are a conversion-focused copywriter. Create landing page copy that drives action. Use benefit-driven language, address objections, and craft compelling calls-to-action.',
                'prompt_template'   => "Create high-converting landing page copy for: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\n\nLanding Page Sections:\n1. HERO SECTION:\n   - Compelling H1 headline with primary keyword\n   - Supporting subheadline\n   - Primary CTA button text\n\n2. PROBLEM SECTION:\n   - Describe the pain points your audience faces\n\n3. SOLUTION SECTION:\n   - Introduce the product/service as the solution\n   - Key benefits (not just features)\n\n4. FEATURES SECTION:\n   - 3-5 key features with benefit explanations\n\n5. SOCIAL PROOF:\n   - Testimonial placeholders\n   - Trust badges / client logos\n\n6. FAQ SECTION:\n   - Address common objections\n\n7. FINAL CTA SECTION:\n   - Risk reversal / guarantee\n   - Final compelling CTA",
                'default_settings'  => [
                    'article_length' => 1200,
                    'tone'           => 'persuasive',
                    'content_type'   => 'landing_page',
                    'temperature'    => 0.7,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('FAQ Generator', 'ai-seo-writer-pro'),
                'slug'              => 'faq-generator',
                'description'       => __('Generate comprehensive FAQ sections targeting long-tail keywords and featured snippets. Questions and answers based on your topic.', 'ai-seo-writer-pro'),
                'category'          => 'utility',
                'system_prompt'     => 'You are an expert at creating helpful FAQ content. Generate relevant questions that real people ask, and provide clear, concise, and helpful answers. Optimize for featured snippets.',
                'prompt_template'   => "Generate a comprehensive FAQ section about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nLanguage: {language}\nTone: {tone}\n\nGenerate 8-12 frequently asked questions with detailed answers.\n\nRequirements:\n1. Questions should reflect what real users search for\n2. Answers should be clear, concise, and informative\n3. Include the primary keyword naturally in questions and answers\n4. Some answers should be structured for featured snippets (short, direct)\n5. Cover different aspects of the topic\n6. Include questions about:\n   - Basic definitions and concepts\n   - How-to and process questions\n   - Benefits and advantages\n   - Comparison questions\n   - Pricing and cost\n   - Common misconceptions\n\nFormat each Q&A clearly.",
                'default_settings'  => [
                    'article_length' => 800,
                    'tone'           => 'informative',
                    'content_type'   => 'informational',
                    'temperature'    => 0.6,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Meta Description Generator', 'ai-seo-writer-pro'),
                'slug'              => 'meta-description-generator',
                'description'       => __('Create compelling, click-worthy meta descriptions optimized for both SEO and click-through rates. 150-160 characters with primary keyword.', 'ai-seo-writer-pro'),
                'category'          => 'utility',
                'system_prompt'     => 'You are an SEO meta description specialist. Create compelling meta descriptions that improve click-through rates. Include the primary keyword naturally and stay within 150-160 characters.',
                'prompt_template'   => "Generate 5 optimized meta descriptions for a page about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nLanguage: {language}\nTone: {tone}\n\nRequirements for each meta description:\n1. Length: 150-160 characters\n2. Include the primary keyword naturally\n3. Compelling and click-worthy\n4. Include a value proposition or benefit\n5. Create urgency or curiosity where appropriate\n6. End with a subtle call-to-action\n\nProvide 5 variations with different angles:\n- Benefit-focused\n- Question-based\n- Urgency-driven\n- Specific/numbers-based\n- Curiosity-inducing",
                'default_settings'  => [
                    'tone'           => 'professional',
                    'temperature'    => 0.8,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Blog Introduction Generator', 'ai-seo-writer-pro'),
                'slug'              => 'blog-intro-generator',
                'description'       => __('Generate engaging blog post introductions that hook readers, include the primary keyword early, and set expectations for the article.', 'ai-seo-writer-pro'),
                'category'          => 'utility',
                'system_prompt'     => 'You are a master at writing captivating introductions. Create blog intros that hook readers immediately, establish relevance, and smoothly transition into the main content.',
                'prompt_template'   => "Generate 3 engaging blog post introductions for an article about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nLanguage: {language}\nTone: {tone}\n\nEach introduction should:\n1. Hook the reader with a question, statistic, story, or bold statement\n2. Establish the problem or relevance of the topic\n3. Include the primary keyword within the first 1-2 sentences\n4. Set expectations for what the article will cover\n5. Be 100-200 words\n6. End with a natural transition to the first section\n\nProvide 3 different styles:\n1. Storytelling approach\n2. Problem-Solution approach\n3. Data/Statistic driven approach",
                'default_settings'  => [
                    'tone'           => 'conversational',
                    'temperature'    => 0.8,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Blog Conclusion Generator', 'ai-seo-writer-pro'),
                'slug'              => 'blog-conclusion-generator',
                'description'       => __('Create satisfying blog conclusions that summarize key points, reinforce the main message, and end with a clear call-to-action or thought-provoking closing.', 'ai-seo-writer-pro'),
                'category'          => 'utility',
                'system_prompt'     => 'You are an expert at writing satisfying conclusions. Create blog conclusions that wrap up content effectively, reinforce key takeaways, and inspire readers to take action.',
                'prompt_template'   => "Generate 3 satisfying blog post conclusions for an article about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nLanguage: {language}\nTone: {tone}\n\nEach conclusion should:\n1. Start with a transition phrase (not 'In conclusion')\n2. Summarize the key points without repeating verbatim\n3. Reinforce the main message or value proposition\n4. Optionally include the primary keyword\n5. End with a clear call-to-action, thought-provoking question, or inspiring closing\n6. Be 80-150 words\n\nProvide 3 different styles:\n1. Action-oriented (clear CTA)\n2. Reflective/inspirational\n3. Forward-looking (what's next)",
                'default_settings'  => [
                    'tone'           => 'professional',
                    'temperature'    => 0.8,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Social Media Content Generator', 'ai-seo-writer-pro'),
                'slug'              => 'social-media-generator',
                'description'       => __('Generate platform-optimized social media posts to promote your content. Includes posts for Twitter/X, LinkedIn, Facebook, and Instagram.', 'ai-seo-writer-pro'),
                'category'          => 'marketing',
                'system_prompt'     => 'You are a social media marketing expert. Create engaging, platform-optimized social media posts that drive engagement and traffic. Each platform has different best practices.',
                'prompt_template'   => "Generate social media posts to promote content about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nLanguage: {language}\nTone: {tone}\nContent Type: {content_type}\n\nGenerate posts for these platforms:\n\n1. TWITTER/X (280 chars max):\n   - Hook + value + link\n   - Include relevant hashtags\n   - Create 2 variations\n\n2. LINKEDIN:\n   - Professional, thought-leadership angle\n   - 3-5 lines\n   - End with a question to encourage engagement\n   - Relevant hashtags\n\n3. FACEBOOK:\n   - Friendly, conversational\n   - Ask a question to encourage comments\n   - Include a brief description of value\n\n4. INSTAGRAM:\n   - Engaging caption with storytelling\n   - Emoji-friendly\n   - 10-15 relevant hashtags at the end\n\n5. EMAIL NEWSLETTER TEASER:\n   - Subject line suggestion\n   - Short email body (2-3 sentences)",
                'default_settings'  => [
                    'tone'           => 'conversational',
                    'temperature'    => 0.8,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('E-commerce Product Description Writer', 'ai-seo-writer-pro'),
                'slug'              => 'ecommerce-product-description',
                'description'       => __('Write compelling e-commerce product descriptions that sell. Combines features with benefits, addresses objections, and includes SEO keywords naturally.', 'ai-seo-writer-pro'),
                'category'          => 'commercial',
                'system_prompt'     => 'You are an e-commerce copywriting expert. Write product descriptions that convert browsers into buyers. Focus on benefits, not just features, and create desire.',
                'prompt_template'   => "Write a compelling e-commerce product description for: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\n\nProduct Description Structure:\n\n1. SEO TITLE (50-60 chars):\n   - Include product name and primary keyword\n\n2. SHORT DESCRIPTION (150-200 chars):\n   - Benefit-driven summary for category pages\n\n3. MAIN DESCRIPTION:\n   - Hook: Address the customer's desire or problem\n   - Key Benefits: 3-5 bullet points (benefit, not just feature)\n   - Features & Specifications: Detailed list\n   - Social Proof: Placeholder for reviews\n   - Who This Is For: Ideal customer profile\n   - Why Choose This Product: Unique selling proposition\n   - Risk Reversal: Guarantee/return policy\n\n4. SEO ELEMENTS:\n   - Natural keyword usage\n   - Related terms and synonyms\n   - Answer common pre-purchase questions",
                'default_settings'  => [
                    'article_length' => 600,
                    'tone'           => 'persuasive',
                    'content_type'   => 'review',
                    'temperature'    => 0.7,
                ],
                'is_builtin'        => true,
            ],
            [
                'name'              => __('Pillar Content Generator', 'ai-seo-writer-pro'),
                'slug'              => 'pillar-content-generator',
                'description'       => __('Create comprehensive pillar content that serves as the foundation for your topic cluster strategy. Broad, authoritative, and internally linkable.', 'ai-seo-writer-pro'),
                'category'          => 'blogging',
                'system_prompt'     => 'You are an expert content strategist and pillar content creator. Write comprehensive, authoritative pillar pages that cover a broad topic thoroughly. Structure content to support a topic cluster strategy with natural internal linking opportunities.',
                'prompt_template'   => "Create comprehensive pillar content about: {topic}\n\nPrimary Keyword: {primary_keyword}\nSecondary Keywords: {secondary_keywords}\nTarget Audience: {target_audience}\nSearch Intent: {search_intent}\nLanguage: {language}\nTarget Length: {article_length} words\nTone: {tone}\n\nPILLAR CONTENT STRUCTURE:\n\n1. DEFINITIVE TITLE: Broad, authoritative, includes primary keyword\n\n2. INTRODUCTION:\n   - Why this topic is important\n   - What readers will learn\n   - Brief overview of sections\n\n3. CORE FOUNDATION SECTIONS:\n   - Definitions and basic concepts\n   - Why it matters / Importance\n   - Core principles\n\n4. DETAILED SUBTOPIC SECTIONS (8-12 sections):\n   Each section should:\n   - Cover a specific subtopic thoroughly\n   - Include H2/H3 headings with keyword variations\n   - Provide practical examples\n   - Include internal link opportunities to related cluster content\n\n5. ADVANCED / STRATEGIC SECTION:\n   - Best practices and pro tips\n   - Common pitfalls to avoid\n   - Future trends\n\n6. IMPLEMENTATION GUIDE:\n   - Actionable steps\n   - Tools and resources\n\n7. FAQ SECTION:\n   - 8-10 comprehensive Q&A pairs\n\n8. CONCLUSION:\n   - Key takeaways\n   - Next steps\n\nEnsure the content naturally supports internal linking to cluster articles.",
                'default_settings'  => [
                    'article_length' => 4000,
                    'tone'           => 'authoritative',
                    'content_type'   => 'pillar',
                    'temperature'    => 0.6,
                ],
                'is_builtin'        => true,
            ],
        ];

        return $this->builtin_templates;
    }

    /**
     * Get all templates (built-in + custom from DB)
     *
     * @param int $user_id
     * @return array
     */
    public function get_all_templates($user_id = 0) {
        $builtin = $this->get_builtin_templates();
        $custom = [];

        if ($user_id > 0) {
            $db = new Database_Manager();
            $custom_templates = $db->get_templates([
                'user_id'        => $user_id,
                'include_global' => true,
                'status'         => 'active',
            ]);

            foreach ($custom_templates as $tpl) {
                $custom[] = [
                    'id'               => (int) $tpl['id'],
                    'name'             => $tpl['name'],
                    'slug'             => $tpl['slug'],
                    'description'      => $tpl['description'] ?? '',
                    'category'         => $tpl['category'],
                    'system_prompt'    => $tpl['system_prompt'] ?? '',
                    'prompt_template'  => $tpl['prompt_template'],
                    'default_settings' => !empty($tpl['default_settings'])
                        ? json_decode($tpl['default_settings'], true)
                        : [],
                    'is_builtin'       => false,
                    'is_global'        => !empty($tpl['is_global']),
                    'user_id'          => (int) $tpl['user_id'],
                    'status'           => $tpl['status'],
                    'created_at'       => $tpl['created_at'],
                    'updated_at'       => $tpl['updated_at'],
                ];
            }
        }

        return array_merge($builtin, $custom);
    }

    /**
     * Get a template by slug (checks built-in first, then custom)
     *
     * @param string $slug
     * @param int $user_id
     * @return array|null
     */
    public function get_template_by_slug($slug, $user_id = 0) {
        $builtin = $this->get_builtin_templates();
        foreach ($builtin as $tpl) {
            if ($tpl['slug'] === $slug) {
                return $tpl;
            }
        }

        if ($user_id > 0) {
            $db = new Database_Manager();
            $custom = $db->get_templates([
                'user_id'        => $user_id,
                'include_global' => true,
                'status'         => 'active',
            ]);

            foreach ($custom as $tpl) {
                if ($tpl['slug'] === $slug) {
                    return [
                        'id'               => (int) $tpl['id'],
                        'name'             => $tpl['name'],
                        'slug'             => $tpl['slug'],
                        'description'      => $tpl['description'] ?? '',
                        'category'         => $tpl['category'],
                        'system_prompt'    => $tpl['system_prompt'] ?? '',
                        'prompt_template'  => $tpl['prompt_template'],
                        'default_settings' => !empty($tpl['default_settings'])
                            ? json_decode($tpl['default_settings'], true)
                            : [],
                        'is_builtin'       => false,
                        'is_global'        => !empty($tpl['is_global']),
                        'user_id'          => (int) $tpl['user_id'],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Get a template by ID (custom templates only)
     *
     * @param int $id
     * @return array|null
     */
    public function get_template($id) {
        $db = new Database_Manager();
        $tpl = $db->get_template((int) $id);

        if (!$tpl) {
            return null;
        }

        return [
            'id'               => (int) $tpl['id'],
            'name'             => $tpl['name'],
            'slug'             => $tpl['slug'],
            'description'      => $tpl['description'] ?? '',
            'category'         => $tpl['category'],
            'system_prompt'    => $tpl['system_prompt'] ?? '',
            'prompt_template'  => $tpl['prompt_template'],
            'default_settings' => !empty($tpl['default_settings'])
                ? json_decode($tpl['default_settings'], true)
                : [],
            'is_builtin'       => false,
            'is_global'        => !empty($tpl['is_global']),
            'user_id'          => (int) $tpl['user_id'],
            'status'           => $tpl['status'],
            'created_at'       => $tpl['created_at'],
            'updated_at'       => $tpl['updated_at'],
        ];
    }

    /**
     * Create a custom template
     *
     * @param array $data
     * @return int|false Template ID or false on failure
     */
    public function create_custom_template($data) {
        $security = new Security_Manager();
        $db = new Database_Manager();

        $defaults = [
            'user_id'          => get_current_user_id(),
            'name'             => '',
            'slug'             => '',
            'description'      => '',
            'category'         => 'custom',
            'prompt_template'  => '',
            'system_prompt'    => '',
            'default_settings' => [],
            'is_global'        => 0,
            'status'           => 'active',
        ];

        $data = wp_parse_args($data, $defaults);

        // Sanitize
        $name = $security->sanitize_text($data['name']);
        $slug = !empty($data['slug'])
            ? sanitize_title($security->sanitize_text($data['slug']))
            : sanitize_title($name);

        // Ensure unique slug
        $slug = $this->ensure_unique_slug($slug, $data['user_id']);

        $insert_data = [
            'user_id'          => (int) $data['user_id'],
            'name'             => $name,
            'slug'             => $slug,
            'description'      => $security->sanitize_textarea($data['description']),
            'category'         => $security->sanitize_text($data['category']),
            'prompt_template'  => $security->sanitize_textarea($data['prompt_template']),
            'system_prompt'    => $security->sanitize_textarea($data['system_prompt']),
            'default_settings' => is_array($data['default_settings'])
                ? wp_json_encode($data['default_settings'])
                : $security->sanitize_textarea($data['default_settings']),
            'is_global'        => (int) $data['is_global'],
            'status'           => $security->sanitize_text($data['status']),
        ];

        return $db->insert_template($insert_data);
    }

    /**
     * Update a custom template
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_custom_template($id, $data) {
        $security = new Security_Manager();
        $db = new Database_Manager();

        $existing = $db->get_template((int) $id);
        if (!$existing) {
            return false;
        }

        $update_data = [];

        if (isset($data['name'])) {
            $update_data['name'] = $security->sanitize_text($data['name']);
        }

        if (isset($data['slug'])) {
            $slug = sanitize_title($security->sanitize_text($data['slug']));
            if ($slug !== $existing['slug']) {
                $slug = $this->ensure_unique_slug($slug, $existing['user_id'], (int) $id);
            }
            $update_data['slug'] = $slug;
        }

        if (isset($data['description'])) {
            $update_data['description'] = $security->sanitize_textarea($data['description']);
        }

        if (isset($data['category'])) {
            $update_data['category'] = $security->sanitize_text($data['category']);
        }

        if (isset($data['prompt_template'])) {
            $update_data['prompt_template'] = $security->sanitize_textarea($data['prompt_template']);
        }

        if (isset($data['system_prompt'])) {
            $update_data['system_prompt'] = $security->sanitize_textarea($data['system_prompt']);
        }

        if (isset($data['default_settings'])) {
            $update_data['default_settings'] = is_array($data['default_settings'])
                ? wp_json_encode($data['default_settings'])
                : $security->sanitize_textarea($data['default_settings']);
        }

        if (isset($data['is_global'])) {
            $update_data['is_global'] = (int) $data['is_global'];
        }

        if (isset($data['status'])) {
            $update_data['status'] = $security->sanitize_text($data['status']);
        }

        if (empty($update_data)) {
            return false;
        }

        return $db->update_template((int) $id, $update_data);
    }

    /**
     * Delete a custom template
     *
     * @param int $id
     * @return bool
     */
    public function delete_custom_template($id) {
        $db = new Database_Manager();
        return $db->delete_template((int) $id);
    }

    /**
     * Apply template - fill template variables with parameters
     *
     * Supported variables: {topic}, {primary_keyword}, {secondary_keywords},
     * {target_audience}, {search_intent}, {language}, {article_length},
     * {tone}, {content_type}, {competitor_url}
     *
     * @param string $template_slug
     * @param array $params
     * @return array|false Array with system_prompt and filled prompt, or false
     */
    public function apply_template($template_slug, $params = []) {
        $user_id = get_current_user_id();
        $template = $this->get_template_by_slug($template_slug, $user_id);

        if (!$template) {
            return false;
        }

        $defaults = [
            'topic'              => '',
            'primary_keyword'    => '',
            'secondary_keywords' => '',
            'target_audience'    => __('General audience', 'ai-seo-writer-pro'),
            'search_intent'      => 'informational',
            'language'           => 'English',
            'article_length'     => 1500,
            'tone'               => 'professional',
            'content_type'       => 'blog',
            'competitor_url'     => '',
        ];

        $params = wp_parse_args($params, $defaults);

        // Format secondary keywords
        if (is_array($params['secondary_keywords'])) {
            $params['secondary_keywords'] = implode(', ', $params['secondary_keywords']);
        }
        if (empty($params['secondary_keywords'])) {
            $params['secondary_keywords'] = __('None specified', 'ai-seo-writer-pro');
        }

        // Fill variables in prompt template
        $prompt = $template['prompt_template'];
        $system_prompt = !empty($template['system_prompt']) ? $template['system_prompt'] : '';

        $replacements = [
            '{topic}'              => $params['topic'],
            '{primary_keyword}'    => $params['primary_keyword'],
            '{secondary_keywords}' => $params['secondary_keywords'],
            '{target_audience}'    => $params['target_audience'],
            '{search_intent}'      => $params['search_intent'],
            '{language}'           => $params['language'],
            '{article_length}'     => $params['article_length'],
            '{tone}'               => $params['tone'],
            '{content_type}'       => $params['content_type'],
            '{competitor_url}'     => $params['competitor_url'],
        ];

        foreach ($replacements as $variable => $value) {
            $prompt = str_replace($variable, $value, $prompt);
            if (!empty($system_prompt)) {
                $system_prompt = str_replace($variable, $value, $system_prompt);
            }
        }

        return [
            'template'       => $template,
            'system_prompt'  => $system_prompt,
            'prompt'         => $prompt,
            'params'         => $params,
        ];
    }

    /**
     * Ensure a template slug is unique
     *
     * @param string $slug
     * @param int $user_id
     * @param int $exclude_id
     * @return string
     */
    private function ensure_unique_slug($slug, $user_id, $exclude_id = 0) {
        $db = new Database_Manager();
        $original_slug = $slug;
        $suffix = 1;

        // Check built-in templates first
        $builtin = $this->get_builtin_templates();
        $builtin_slugs = wp_list_pluck($builtin, 'slug');

        while (true) {
            $conflict = false;

            if (in_array($slug, $builtin_slugs, true)) {
                $conflict = true;
            } else {
                // Check database
                global $wpdb;
                $table = $wpdb->prefix . 'ai_seo_writer_pro_templates';
                $query = $wpdb->prepare(
                    "SELECT id FROM $table WHERE slug = %s AND user_id = %d AND id != %d LIMIT 1",
                    $slug,
                    $user_id,
                    $exclude_id
                );
                $existing_id = $wpdb->get_var($query);
                if ($existing_id) {
                    $conflict = true;
                }
            }

            if (!$conflict) {
                break;
            }

            $slug = $original_slug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Get available template categories
     *
     * @return array
     */
    public function get_categories() {
        return [
            'blogging'   => __('Blogging', 'ai-seo-writer-pro'),
            'commercial' => __('Commercial', 'ai-seo-writer-pro'),
            'marketing'  => __('Marketing', 'ai-seo-writer-pro'),
            'local_seo'  => __('Local SEO', 'ai-seo-writer-pro'),
            'utility'    => __('Utility', 'ai-seo-writer-pro'),
            'custom'     => __('Custom', 'ai-seo-writer-pro'),
        ];
    }

    /*
     * AJAX Handlers
     */

    /**
     * AJAX: Get all templates
     */
    public function ajax_get_templates() {
        $security = new Security_Manager();

        if (!$security->verify_ajax_request('edit_posts')) {
            return;
        }

        $user_id = get_current_user_id();
        $templates = $this->get_all_templates($user_id);

        // Strip prompt_template for list view to reduce payload
        $list = array_map(function ($tpl) {
            return [
                'id'          => $tpl['id'] ?? null,
                'name'        => $tpl['name'],
                'slug'        => $tpl['slug'],
                'description' => $tpl['description'],
                'category'    => $tpl['category'],
                'is_builtin'  => !empty($tpl['is_builtin']),
                'is_global'   => !empty($tpl['is_global']),
            ];
        }, $templates);

        wp_send_json_success([
            'templates'  => $list,
            'categories' => $this->get_categories(),
        ]);
    }

    /**
     * AJAX: Get a single template (full details)
     */
    public function ajax_get_template() {
        $security = new Security_Manager();

        if (!$security->verify_ajax_request('edit_posts')) {
            return;
        }

        $template_id = isset($_POST['id']) ? $security->sanitize_int($_POST['id']) : 0;
        $template_slug = isset($_POST['slug']) ? $security->sanitize_text($_POST['slug']) : '';

        $template = null;

        if ($template_id > 0) {
            $template = $this->get_template($template_id);
        } elseif (!empty($template_slug)) {
            $user_id = get_current_user_id();
            $template = $this->get_template_by_slug($template_slug, $user_id);
        }

        if (!$template) {
            wp_send_json_error(['message' => __('Template not found.', 'ai-seo-writer-pro')], 404);
            return;
        }

        wp_send_json_success(['template' => $template]);
    }

    /**
     * AJAX: Create a custom template
     */
    public function ajax_create_template() {
        $security = new Security_Manager();

        if (!$security->verify_ajax_request('manage_options')) {
            return;
        }

        $data = [
            'name'             => isset($_POST['name']) ? $security->sanitize_text($_POST['name']) : '',
            'slug'             => isset($_POST['slug']) ? $security->sanitize_text($_POST['slug']) : '',
            'description'      => isset($_POST['description']) ? $security->sanitize_textarea($_POST['description']) : '',
            'category'         => isset($_POST['category']) ? $security->sanitize_text($_POST['category']) : 'custom',
            'prompt_template'  => isset($_POST['prompt_template']) ? $security->sanitize_textarea($_POST['prompt_template']) : '',
            'system_prompt'    => isset($_POST['system_prompt']) ? $security->sanitize_textarea($_POST['system_prompt']) : '',
            'default_settings' => isset($_POST['default_settings']) ? $_POST['default_settings'] : [],
            'is_global'        => isset($_POST['is_global']) ? (int) $_POST['is_global'] : 0,
        ];

        if (empty($data['name']) || empty($data['prompt_template'])) {
            wp_send_json_error(['message' => __('Name and prompt template are required.', 'ai-seo-writer-pro')], 400);
            return;
        }

        $template_id = $this->create_custom_template($data);

        if (!$template_id) {
            wp_send_json_error(['message' => __('Failed to create template.', 'ai-seo-writer-pro')], 500);
            return;
        }

        wp_send_json_success([
            'message' => __('Template created successfully.', 'ai-seo-writer-pro'),
            'template_id' => $template_id,
        ]);
    }

    /**
     * AJAX: Update a custom template
     */
    public function ajax_update_template() {
        $security = new Security_Manager();

        if (!$security->verify_ajax_request('manage_options')) {
            return;
        }

        $template_id = isset($_POST['id']) ? $security->sanitize_int($_POST['id']) : 0;

        if ($template_id <= 0) {
            wp_send_json_error(['message' => __('Invalid template ID.', 'ai-seo-writer-pro')], 400);
            return;
        }

        $data = [];

        if (isset($_POST['name'])) {
            $data['name'] = $security->sanitize_text($_POST['name']);
        }
        if (isset($_POST['slug'])) {
            $data['slug'] = $security->sanitize_text($_POST['slug']);
        }
        if (isset($_POST['description'])) {
            $data['description'] = $security->sanitize_textarea($_POST['description']);
        }
        if (isset($_POST['category'])) {
            $data['category'] = $security->sanitize_text($_POST['category']);
        }
        if (isset($_POST['prompt_template'])) {
            $data['prompt_template'] = $security->sanitize_textarea($_POST['prompt_template']);
        }
        if (isset($_POST['system_prompt'])) {
            $data['system_prompt'] = $security->sanitize_textarea($_POST['system_prompt']);
        }
        if (isset($_POST['default_settings'])) {
            $data['default_settings'] = $_POST['default_settings'];
        }
        if (isset($_POST['is_global'])) {
            $data['is_global'] = (int) $_POST['is_global'];
        }
        if (isset($_POST['status'])) {
            $data['status'] = $security->sanitize_text($_POST['status']);
        }

        $result = $this->update_custom_template($template_id, $data);

        if (!$result) {
            wp_send_json_error(['message' => __('Failed to update template.', 'ai-seo-writer-pro')], 500);
            return;
        }

        wp_send_json_success([
            'message' => __('Template updated successfully.', 'ai-seo-writer-pro'),
        ]);
    }

    /**
     * AJAX: Delete a custom template
     */
    public function ajax_delete_template() {
        $security = new Security_Manager();

        if (!$security->verify_ajax_request('manage_options')) {
            return;
        }

        $template_id = isset($_POST['id']) ? $security->sanitize_int($_POST['id']) : 0;

        if ($template_id <= 0) {
            wp_send_json_error(['message' => __('Invalid template ID.', 'ai-seo-writer-pro')], 400);
            return;
        }

        $result = $this->delete_custom_template($template_id);

        if (!$result) {
            wp_send_json_error(['message' => __('Failed to delete template.', 'ai-seo-writer-pro')], 500);
            return;
        }

        wp_send_json_success([
            'message' => __('Template deleted successfully.', 'ai-seo-writer-pro'),
        ]);
    }

    /**
     * AJAX: Apply a template with parameters
     */
    public function ajax_apply_template() {
        $security = new Security_Manager();

        if (!$security->verify_ajax_request('edit_posts')) {
            return;
        }

        $template_slug = isset($_POST['template_slug']) ? $security->sanitize_text($_POST['template_slug']) : '';

        if (empty($template_slug)) {
            wp_send_json_error(['message' => __('Template slug is required.', 'ai-seo-writer-pro')], 400);
            return;
        }

        $params = [
            'topic'              => isset($_POST['topic']) ? $security->sanitize_textarea($_POST['topic']) : '',
            'primary_keyword'    => isset($_POST['primary_keyword']) ? $security->sanitize_text($_POST['primary_keyword']) : '',
            'secondary_keywords' => isset($_POST['secondary_keywords']) ? $security->sanitize_text($_POST['secondary_keywords']) : '',
            'target_audience'    => isset($_POST['target_audience']) ? $security->sanitize_text($_POST['target_audience']) : '',
            'search_intent'      => isset($_POST['search_intent']) ? $security->sanitize_text($_POST['search_intent']) : '',
            'language'           => isset($_POST['language']) ? $security->sanitize_text($_POST['language']) : '',
            'article_length'     => isset($_POST['article_length']) ? $security->sanitize_int($_POST['article_length']) : 1500,
            'tone'               => isset($_POST['tone']) ? $security->sanitize_text($_POST['tone']) : '',
            'content_type'       => isset($_POST['content_type']) ? $security->sanitize_text($_POST['content_type']) : '',
            'competitor_url'     => isset($_POST['competitor_url']) ? $security->sanitize_url($_POST['competitor_url']) : '',
        ];

        $result = $this->apply_template($template_slug, $params);

        if (!$result) {
            wp_send_json_error(['message' => __('Template not found.', 'ai-seo-writer-pro')], 404);
            return;
        }

        wp_send_json_success($result);
    }
}

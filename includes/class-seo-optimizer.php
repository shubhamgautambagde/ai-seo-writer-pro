<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * SEO Optimizer - Generates SEO-optimized elements for articles
 *
 * Uses AI provider when available, falls back to intelligent rule-based generation.
 */
class SEO_Optimizer {

    /**
     * AI Providers instance (optional)
     * @var AI_Providers|null
     */
    private $ai_providers = null;

    /**
     * Constructor
     *
     * @param AI_Providers|null $ai_providers
     */
    public function __construct($ai_providers = null) {
        $this->ai_providers = $ai_providers;
    }

    /**
     * Set AI Providers instance
     *
     * @param AI_Providers $ai_providers
     */
    public function set_ai_providers($ai_providers) {
        $this->ai_providers = $ai_providers;
    }

    /**
     * Initialize hooks
     */
    public function init() {
        // SEO optimizer hooks can be added here
        do_action('ai_seo_writer_pro_seo_optimizer_init', $this);
    }

    /**
     * Generate SEO title (50-60 characters)
     *
     * @param string $topic
     * @param string $keyword
     * @return string
     */
    public function generate_seo_title($topic, $keyword) {
        $topic = trim($topic);
        $keyword = trim($keyword);

        // Try AI generation first
        $ai_result = $this->try_ai_generation('seo_title', [
            'topic' => $topic,
            'keyword' => $keyword,
        ]);

        if ($ai_result) {
            $ai_result = $this->truncate_to_chars($ai_result, 60);
            if ($this->validate_seo_title($ai_result, $keyword)) {
                return $ai_result;
            }
        }

        // Rule-based fallback
        return $this->rule_based_seo_title($topic, $keyword);
    }

    /**
     * Generate meta description (150-160 characters)
     *
     * @param string $topic
     * @param string $keyword
     * @return string
     */
    public function generate_meta_description($topic, $keyword) {
        $topic = trim($topic);
        $keyword = trim($keyword);

        // Try AI generation first
        $ai_result = $this->try_ai_generation('meta_description', [
            'topic' => $topic,
            'keyword' => $keyword,
        ]);

        if ($ai_result) {
            $ai_result = $this->truncate_to_chars($ai_result, 160);
            if (stripos($ai_result, $keyword) !== false || strlen($ai_result) >= 140) {
                return $ai_result;
            }
        }

        // Rule-based fallback
        return $this->rule_based_meta_description($topic, $keyword);
    }

    /**
     * Generate URL slug from title
     *
     * @param string $title
     * @return string
     */
    public function generate_url_slug($title) {
        if (function_exists('sanitize_title')) {
            return sanitize_title($title);
        }

        // Fallback sanitization
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    /**
     * Generate headings structure (H1, H2, H3 hierarchy)
     *
     * @param string $topic
     * @param string $keyword
     * @param int $sections Number of sections
     * @return array
     */
    public function generate_headings_structure($topic, $keyword, $sections = 5) {
        $topic = trim($topic);
        $keyword = trim($keyword);
        $sections = max(3, min(10, absint($sections)));

        // Try AI generation first
        $ai_result = $this->try_ai_generation('headings', [
            'topic' => $topic,
            'keyword' => $keyword,
            'sections' => $sections,
        ]);

        if ($ai_result && is_array($ai_result)) {
            return $this->normalize_headings($ai_result, $topic, $keyword);
        }

        // Rule-based fallback
        return $this->rule_based_headings($topic, $keyword, $sections);
    }

    /**
     * Suggest internal links based on content and existing posts
     *
     * @param string $content
     * @param array $existing_posts Array of posts with 'id', 'title', 'url', 'keywords'
     * @return array
     */
    public function suggest_internal_links($content, $existing_posts = []) {
        $content = strtolower($content);
        $suggestions = [];

        if (empty($existing_posts)) {
            // Try to get recent posts
            $existing_posts = $this->get_recent_posts_for_linking();
        }

        foreach ($existing_posts as $post) {
            $post_title = strtolower($post['title'] ?? '');
            $post_keywords = isset($post['keywords']) ? array_map('strtolower', (array) $post['keywords']) : [];
            $match_score = 0;

            // Check title match
            if (!empty($post_title) && strpos($content, $post_title) !== false) {
                $match_score += 10;
            }

            // Check keyword matches
            foreach ($post_keywords as $kw) {
                if (!empty($kw) && strpos($content, $kw) !== false) {
                    $match_score += 5;
                }
            }

            // Check word overlap
            $content_words = array_unique(preg_split('/\W+/', $content, -1, PREG_SPLIT_NO_EMPTY));
            $title_words = array_unique(preg_split('/\W+/', $post_title, -1, PREG_SPLIT_NO_EMPTY));
            $overlap = array_intersect($content_words, $title_words);
            $match_score += count($overlap);

            if ($match_score > 0) {
                $suggestions[] = [
                    'id' => $post['id'] ?? 0,
                    'title' => $post['title'] ?? '',
                    'url' => $post['url'] ?? '',
                    'anchor_text' => $post['title'] ?? '',
                    'relevance_score' => $match_score,
                ];
            }
        }

        // Sort by relevance
        usort($suggestions, function ($a, $b) {
            return $b['relevance_score'] - $a['relevance_score'];
        });

        return array_slice($suggestions, 0, 5);
    }

    /**
     * Suggest external authority links for a topic
     *
     * @param string $topic
     * @return array
     */
    public function suggest_external_links($topic) {
        $topic = strtolower(trim($topic));
        $suggestions = [];

        // Authority sites by category/topic keywords
        $authority_sites = [
            'health' => [
                ['name' => 'Mayo Clinic', 'url' => 'https://www.mayoclinic.org', 'anchor' => 'Mayo Clinic'],
                ['name' => 'WebMD', 'url' => 'https://www.webmd.com', 'anchor' => 'WebMD'],
                ['name' => 'NHS', 'url' => 'https://www.nhs.uk', 'anchor' => 'NHS'],
                ['name' => 'WHO', 'url' => 'https://www.who.int', 'anchor' => 'World Health Organization'],
            ],
            'tech' => [
                ['name' => 'TechCrunch', 'url' => 'https://techcrunch.com', 'anchor' => 'TechCrunch'],
                ['name' => 'Wired', 'url' => 'https://www.wired.com', 'anchor' => 'Wired'],
                ['name' => 'MIT Technology Review', 'url' => 'https://www.technologyreview.com', 'anchor' => 'MIT Technology Review'],
            ],
            'business' => [
                ['name' => 'Forbes', 'url' => 'https://www.forbes.com', 'anchor' => 'Forbes'],
                ['name' => 'Harvard Business Review', 'url' => 'https://hbr.org', 'anchor' => 'Harvard Business Review'],
                ['name' => 'Bloomberg', 'url' => 'https://www.bloomberg.com', 'anchor' => 'Bloomberg'],
            ],
            'education' => [
                ['name' => 'Wikipedia', 'url' => 'https://www.wikipedia.org', 'anchor' => 'Wikipedia'],
                ['name' => 'Stanford Online', 'url' => 'https://online.stanford.edu', 'anchor' => 'Stanford Online'],
                ['name' => 'Coursera', 'url' => 'https://www.coursera.org', 'anchor' => 'Coursera'],
            ],
            'marketing' => [
                ['name' => 'HubSpot', 'url' => 'https://www.hubspot.com', 'anchor' => 'HubSpot'],
                ['name' => 'Moz', 'url' => 'https://moz.com', 'anchor' => 'Moz'],
                ['name' => 'Search Engine Journal', 'url' => 'https://www.searchenginejournal.com', 'anchor' => 'Search Engine Journal'],
                ['name' => 'Ahrefs Blog', 'url' => 'https://ahrefs.com/blog', 'anchor' => 'Ahrefs Blog'],
            ],
            'finance' => [
                ['name' => 'Investopedia', 'url' => 'https://www.investopedia.com', 'anchor' => 'Investopedia'],
                ['name' => 'The Wall Street Journal', 'url' => 'https://www.wsj.com', 'anchor' => 'The Wall Street Journal'],
                ['name' => 'Financial Times', 'url' => 'https://www.ft.com', 'anchor' => 'Financial Times'],
            ],
            'science' => [
                ['name' => 'National Geographic', 'url' => 'https://www.nationalgeographic.com', 'anchor' => 'National Geographic'],
                ['name' => 'Scientific American', 'url' => 'https://www.scientificamerican.com', 'anchor' => 'Scientific American'],
                ['name' => 'Nature', 'url' => 'https://www.nature.com', 'anchor' => 'Nature'],
            ],
            'general' => [
                ['name' => 'Wikipedia', 'url' => 'https://www.wikipedia.org', 'anchor' => 'Wikipedia'],
                ['name' => 'Encyclopedia Britannica', 'url' => 'https://www.britannica.com', 'anchor' => 'Encyclopedia Britannica'],
                ['name' => 'Google Scholar', 'url' => 'https://scholar.google.com', 'anchor' => 'Google Scholar'],
            ],
        ];

        // Topic keyword to category mapping
        $category_keywords = [
            'health' => ['health', 'medical', 'medicine', 'doctor', 'disease', 'treatment', 'symptom', 'nutrition', 'fitness', 'diet', 'mental', 'wellness'],
            'tech' => ['tech', 'technology', 'software', 'ai', 'artificial intelligence', 'machine learning', 'programming', 'coding', 'computer', 'digital', 'app', 'cloud'],
            'business' => ['business', 'entrepreneur', 'startup', 'management', 'leadership', 'strategy', 'corporate', 'company'],
            'education' => ['education', 'learning', 'student', 'school', 'university', 'college', 'teaching', 'course', 'study'],
            'marketing' => ['marketing', 'seo', 'content', 'social media', 'advertising', 'brand', 'campaign', 'traffic', 'conversion'],
            'finance' => ['finance', 'money', 'invest', 'stock', 'market', 'crypto', 'bitcoin', 'economy', 'banking', 'loan', 'insurance'],
            'science' => ['science', 'research', 'study', 'experiment', 'biology', 'physics', 'chemistry', 'environment', 'climate'],
        ];

        $matched_categories = ['general'];
        foreach ($category_keywords as $category => $keywords) {
            foreach ($keywords as $kw) {
                if (strpos($topic, $kw) !== false) {
                    $matched_categories[] = $category;
                    break;
                }
            }
        }

        $matched_categories = array_unique($matched_categories);

        foreach ($matched_categories as $category) {
            if (isset($authority_sites[$category])) {
                $suggestions = array_merge($suggestions, $authority_sites[$category]);
            }
        }

        // Deduplicate
        $seen = [];
        $unique = [];
        foreach ($suggestions as $suggestion) {
            if (!in_array($suggestion['url'], $seen)) {
                $seen[] = $suggestion['url'];
                $unique[] = $suggestion;
            }
        }

        return array_slice($unique, 0, 4);
    }

    /**
     * Generate image alt text
     *
     * @param string $image_context
     * @param string $keyword
     * @return string
     */
    public function generate_image_alt_text($image_context, $keyword) {
        $image_context = trim($image_context);
        $keyword = trim($keyword);

        // Try AI generation first
        $ai_result = $this->try_ai_generation('image_alt', [
            'image_context' => $image_context,
            'keyword' => $keyword,
        ]);

        if ($ai_result) {
            $ai_result = $this->truncate_to_chars($ai_result, 125);
            if (!empty($ai_result)) {
                return $ai_result;
            }
        }

        // Rule-based fallback
        if (!empty($image_context) && !empty($keyword)) {
            if (stripos($image_context, $keyword) === false) {
                return ucfirst($keyword) . ' - ' . $image_context;
            }
            return ucfirst($image_context);
        }

        if (!empty($keyword)) {
            return ucfirst($keyword);
        }

        return ucfirst($image_context);
    }

    /**
     * Generate FAQ section (3-5 Q&A pairs)
     *
     * @param string $topic
     * @param string $keyword
     * @return array
     */
    public function generate_faq_section($topic, $keyword) {
        $topic = trim($topic);
        $keyword = trim($keyword);

        // Try AI generation first
        $ai_result = $this->try_ai_generation('faq', [
            'topic' => $topic,
            'keyword' => $keyword,
        ]);

        if ($ai_result && is_array($ai_result) && !empty($ai_result)) {
            return $this->normalize_faq($ai_result);
        }

        // Rule-based fallback
        return $this->rule_based_faq($topic, $keyword);
    }

    /**
     * Generate Table of Contents from headings
     *
     * @param array $headings Array of headings with 'level' and 'text'
     * @return array
     */
    public function generate_table_of_contents($headings) {
        $toc = [];

        if (!is_array($headings)) {
            return $toc;
        }

        foreach ($headings as $heading) {
            $level = isset($heading['level']) ? absint($heading['level']) : 2;
            $text = isset($heading['text']) ? trim($heading['text']) : '';

            if (empty($text)) {
                continue;
            }

            // Skip H1 for TOC
            if ($level === 1) {
                continue;
            }

            $toc[] = [
                'level' => $level,
                'text' => $text,
                'anchor' => $this->generate_url_slug($text),
            ];
        }

        return $toc;
    }

    /**
     * Generate Schema markup
     *
     * @param string $type Schema type: 'article', 'faq', 'blog', 'howto'
     * @param array $data Schema data
     * @return string JSON-LD script tag
     */
    public function generate_schema_markup($type, $data) {
        $type = strtolower(trim($type));
        $schema = [];

        switch ($type) {
            case 'article':
            case 'blog':
                $schema = $this->build_article_schema($data);
                break;
            case 'faq':
                $schema = $this->build_faq_schema($data);
                break;
            case 'howto':
                $schema = $this->build_howto_schema($data);
                break;
            default:
                $schema = $this->build_article_schema($data);
                break;
        }

        if (empty($schema)) {
            return '';
        }

        $json = wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return '<script type="application/ld+json">' . "\n" . $json . "\n" . '</script>';
    }

    /**
     * Generate Open Graph tags
     *
     * @param string $title
     * @param string $description
     * @param string $image
     * @return string HTML meta tags
     */
    public function generate_open_graph($title, $description, $image = '') {
        $tags = [];
        $tags[] = '<meta property="og:title" content="' . esc_attr($title) . '" />';
        $tags[] = '<meta property="og:description" content="' . esc_attr($description) . '" />';
        $tags[] = '<meta property="og:type" content="article" />';

        if (!empty($image)) {
            $tags[] = '<meta property="og:image" content="' . esc_url($image) . '" />';
        }

        $tags[] = '<meta name="twitter:card" content="summary_large_image" />';
        $tags[] = '<meta name="twitter:title" content="' . esc_attr($title) . '" />';
        $tags[] = '<meta name="twitter:description" content="' . esc_attr($description) . '" />';

        if (!empty($image)) {
            $tags[] = '<meta name="twitter:image" content="' . esc_url($image) . '" />';
        }

        return implode("\n", $tags);
    }

    /**
     * Generate social media description
     *
     * @param string $content
     * @return string
     */
    public function generate_social_description($content) {
        $content = wp_strip_all_tags($content);

        // Try AI generation first
        $ai_result = $this->try_ai_generation('social_description', [
            'content' => $content,
        ]);

        if ($ai_result) {
            return $this->truncate_to_chars($ai_result, 280);
        }

        // Rule-based: extract first meaningful sentence, truncate
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $description = '';

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) > 40) {
                $description = $sentence;
                break;
            }
        }

        if (empty($description)) {
            $description = $this->truncate_to_chars($content, 200);
        }

        return $this->truncate_to_chars($description, 280);
    }

    /**
     * Try AI generation for a specific element type
     *
     * @param string $type
     * @param array $params
     * @return string|array|null
     */
    private function try_ai_generation($type, $params) {
        if (!$this->ai_providers) {
            return null;
        }

        if (!method_exists($this->ai_providers, 'has_configured_provider') || !$this->ai_providers->has_configured_provider()) {
            return null;
        }

        $system_prompt = $this->build_seo_system_prompt($type);
        $user_prompt = $this->build_seo_user_prompt($type, $params);

        if (empty($system_prompt) || empty($user_prompt)) {
            return null;
        }

        try {
            $result = $this->ai_providers->quick_generate($system_prompt, $user_prompt, [
                'temperature' => 0.5,
                'max_tokens' => 500,
            ]);

            if (is_wp_error($result) || empty($result['content'])) {
                return null;
            }

            $content = trim($result['content']);

            // Parse JSON responses for structured types
            if (in_array($type, ['headings', 'faq'], true)) {
                $parsed = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                    return $parsed;
                }
                // Try to extract JSON from text
                if (preg_match('/\{[\s\S]*\}|\[[\s\S]*\]/', $content, $matches)) {
                    $parsed = json_decode($matches[0], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                        return $parsed;
                    }
                }
            }

            return $content;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Build system prompt for SEO element generation
     *
     * @param string $type
     * @return string
     */
    private function build_seo_system_prompt($type) {
        $prompts = [
            'seo_title' => 'You are an SEO expert. Generate a compelling SEO title tag. Must be 50-60 characters. Include the target keyword naturally. Make it click-worthy. Return ONLY the title text, no quotes or explanations.',
            'meta_description' => 'You are an SEO expert. Generate an engaging meta description. Must be 150-160 characters. Include the target keyword. Create a compelling call-to-action. Return ONLY the description text, no quotes or explanations.',
            'headings' => 'You are an SEO expert and content strategist. Generate a logical heading hierarchy (H1, H2, H3) for an article. Return as JSON array with objects containing "level" (1-3) and "text" fields. The H1 should include the keyword. H2s should cover main subtopics. H3s should break down H2 sections. Return ONLY valid JSON.',
            'image_alt' => 'You are an SEO expert. Generate descriptive image alt text. Include the keyword naturally. Keep it under 125 characters. Describe what is in the image. Return ONLY the alt text.',
            'faq' => 'You are an SEO expert. Generate 3-5 relevant FAQ questions and concise answers about the topic. Each answer should be 1-3 sentences. Return as JSON array with objects containing "question" and "answer" fields. Return ONLY valid JSON.',
            'social_description' => 'You are a social media expert. Generate an engaging social media post description based on the article content. Keep it under 280 characters. Make it shareable and intriguing. Return ONLY the description text.',
        ];

        return $prompts[$type] ?? '';
    }

    /**
     * Build user prompt for SEO element generation
     *
     * @param string $type
     * @param array $params
     * @return string
     */
    private function build_seo_user_prompt($type, $params) {
        switch ($type) {
            case 'seo_title':
                return sprintf("Topic: %s\nTarget Keyword: %s", $params['topic'], $params['keyword']);
            case 'meta_description':
                return sprintf("Topic: %s\nTarget Keyword: %s", $params['topic'], $params['keyword']);
            case 'headings':
                return sprintf("Topic: %s\nTarget Keyword: %s\nNumber of sections: %d", $params['topic'], $params['keyword'], $params['sections']);
            case 'image_alt':
                return sprintf("Image context: %s\nTarget Keyword: %s", $params['image_context'], $params['keyword']);
            case 'faq':
                return sprintf("Topic: %s\nTarget Keyword: %s", $params['topic'], $params['keyword']);
            case 'social_description':
                return sprintf("Article content summary:\n%s", $this->truncate_to_chars($params['content'], 1000));
            default:
                return '';
        }
    }

    /**
     * Rule-based SEO title generation
     *
     * @param string $topic
     * @param string $keyword
     * @return string
     */
    private function rule_based_seo_title($topic, $keyword) {
        $site_name = get_bloginfo('name');
        $patterns = [
            '%keyword%: %topic% | %site%',
            '%topic%: %keyword% Explained | %site%',
            '%keyword% - %topic% | %site%',
            'The Complete Guide to %keyword% | %site%',
            '%topic%: Everything About %keyword% | %site%',
        ];

        $title = '';
        foreach ($patterns as $pattern) {
            $candidate = str_replace(
                ['%keyword%', '%topic%', '%site%'],
                [ucfirst($keyword), ucfirst($topic), $site_name],
                $pattern
            );
            $candidate = $this->truncate_to_chars($candidate, 60);
            if (strlen($candidate) >= 40) {
                $title = $candidate;
                break;
            }
        }

        if (empty($title)) {
            $title = $this->truncate_to_chars(ucfirst($keyword) . ' - ' . ucfirst($topic), 60);
        }

        return $title;
    }

    /**
     * Rule-based meta description generation
     *
     * @param string $topic
     * @param string $keyword
     * @return string
     */
    private function rule_based_meta_description($topic, $keyword) {
        $patterns = [
            'Discover everything about %keyword%. This comprehensive guide covers %topic% with expert insights, practical tips, and actionable advice. Learn more now.',
            'Looking for information on %keyword%? Our in-depth article on %topic% provides everything you need to know. Expert tips and detailed explanations inside.',
            'Learn all about %keyword% in this complete guide to %topic%. Packed with valuable insights, practical strategies, and up-to-date information for you.',
            'Master %keyword% with our comprehensive guide on %topic%. Get expert tips, detailed analysis, and practical advice to help you succeed.',
        ];

        $description = '';
        foreach ($patterns as $pattern) {
            $candidate = str_replace(['%keyword%', '%topic%'], [$keyword, $topic], $pattern);
            $candidate = $this->truncate_to_chars($candidate, 160);
            if (strlen($candidate) >= 140) {
                $description = $candidate;
                break;
            }
        }

        if (empty($description)) {
            $description = $this->truncate_to_chars(
                "Learn everything about $keyword in this comprehensive guide to $topic. Expert insights, practical tips, and detailed information await.",
                160
            );
        }

        return $description;
    }

    /**
     * Rule-based headings generation
     *
     * @param string $topic
     * @param string $keyword
     * @param int $sections
     * @return array
     */
    private function rule_based_headings($topic, $keyword, $sections) {
        $headings = [];

        // H1
        $headings[] = [
            'level' => 1,
            'text' => ucfirst($topic) . ': A Complete Guide to ' . ucfirst($keyword),
        ];

        // H2 sections
        $h2_templates = [
            'What is ' . ucfirst($keyword) . '?',
            'Key Benefits of ' . ucfirst($keyword),
            'How ' . ucfirst($keyword) . ' Works',
            'Common Challenges with ' . ucfirst($keyword),
            'Best Practices for ' . ucfirst($keyword),
            ucfirst($keyword) . ' vs Alternatives: A Comparison',
            'Practical Tips for Implementing ' . ucfirst($keyword),
            'Future Trends in ' . ucfirst($keyword),
            'Frequently Asked Questions About ' . ucfirst($keyword),
            'Final Thoughts on ' . ucfirst($keyword),
        ];

        $selected = array_slice($h2_templates, 0, $sections);

        foreach ($selected as $index => $h2) {
            $headings[] = ['level' => 2, 'text' => $h2];

            // Add H3 subheadings for some sections
            if ($index % 2 === 0 && $index < count($selected) - 1) {
                $headings[] = [
                    'level' => 3,
                    'text' => 'Understanding the Core Concepts',
                ];
                $headings[] = [
                    'level' => 3,
                    'text' => 'Practical Applications',
                ];
            }
        }

        return $headings;
    }

    /**
     * Rule-based FAQ generation
     *
     * @param string $topic
     * @param string $keyword
     * @return array
     */
    private function rule_based_faq($topic, $keyword) {
        $kw_uc = ucfirst($keyword);
        $topic_uc = ucfirst($topic);

        return [
            [
                'question' => "What is $kw_uc?",
                'answer' => "$kw_uc is a key aspect of $topic that refers to the fundamental concepts and practices within this field. Understanding it is essential for anyone looking to deepen their knowledge.",
            ],
            [
                'question' => "Why is $kw_uc important?",
                'answer' => "$kw_uc plays a crucial role in $topic because it provides the foundation for effective decision-making and successful outcomes. It helps professionals achieve better results consistently.",
            ],
            [
                'question' => "How can I get started with $kw_uc?",
                'answer' => "To get started with $kw_uc, begin by learning the basic principles, then gradually apply them through practical exercises. Start small, measure your progress, and refine your approach over time.",
            ],
            [
                'question' => "What are the best practices for $kw_uc?",
                'answer' => "The best practices for $kw_uc include staying updated with the latest trends, being consistent in your approach, measuring results regularly, and continuously optimizing based on feedback and data.",
            ],
        ];
    }

    /**
     * Build Article schema
     *
     * @param array $data
     * @return array
     */
    private function build_article_schema($data) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => isset($data['@type']) ? $data['@type'] : 'Article',
            'headline' => $data['headline'] ?? $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'datePublished' => $data['date_published'] ?? current_time('c'),
            'dateModified' => $data['date_modified'] ?? current_time('c'),
            'author' => [
                '@type' => 'Organization',
                'name' => $data['author'] ?? get_bloginfo('name'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'url' => home_url(),
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $data['url'] ?? get_permalink(),
            ],
        ];

        if (!empty($data['image'])) {
            $schema['image'] = $data['image'];
        }

        if (!empty($data['keywords'])) {
            $schema['keywords'] = is_array($data['keywords']) ? implode(', ', $data['keywords']) : $data['keywords'];
        }

        return $schema;
    }

    /**
     * Build FAQ schema
     *
     * @param array $data
     * @return array
     */
    private function build_faq_schema($data) {
        $faq_items = [];

        if (!empty($data['faq']) && is_array($data['faq'])) {
            foreach ($data['faq'] as $item) {
                if (!empty($item['question']) && !empty($item['answer'])) {
                    $faq_items[] = [
                        '@type' => 'Question',
                        'name' => $item['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $item['answer'],
                        ],
                    ];
                }
            }
        }

        if (empty($faq_items)) {
            return [];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faq_items,
        ];
    }

    /**
     * Build HowTo schema
     *
     * @param array $data
     * @return array
     */
    private function build_howto_schema($data) {
        $steps = [];

        if (!empty($data['steps']) && is_array($data['steps'])) {
            foreach ($data['steps'] as $index => $step) {
                $steps[] = [
                    '@type' => 'HowToStep',
                    'position' => $index + 1,
                    'name' => $step['name'] ?? 'Step ' . ($index + 1),
                    'text' => $step['text'] ?? '',
                ];
            }
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $data['name'] ?? $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'step' => $steps,
        ];
    }

    /**
     * Normalize headings structure
     *
     * @param array $headings
     * @param string $topic
     * @param string $keyword
     * @return array
     */
    private function normalize_headings($headings, $topic, $keyword) {
        $normalized = [];
        $has_h1 = false;

        foreach ($headings as $heading) {
            if (isset($heading['level']) && isset($heading['text'])) {
                $level = absint($heading['level']);
                $level = max(1, min(6, $level));
                if ($level === 1) {
                    $has_h1 = true;
                }
                $normalized[] = [
                    'level' => $level,
                    'text' => trim($heading['text']),
                ];
            }
        }

        // Ensure H1 exists
        if (!$has_h1) {
            array_unshift($normalized, [
                'level' => 1,
                'text' => ucfirst($topic) . ': A Complete Guide to ' . ucfirst($keyword),
            ]);
        }

        return $normalized;
    }

    /**
     * Normalize FAQ items
     *
     * @param array $faq
     * @return array
     */
    private function normalize_faq($faq) {
        $normalized = [];

        foreach ($faq as $item) {
            if (isset($item['question']) && isset($item['answer'])) {
                $normalized[] = [
                    'question' => trim($item['question']),
                    'answer' => trim($item['answer']),
                ];
            }
        }

        return array_slice($normalized, 0, 5);
    }

    /**
     * Validate SEO title
     *
     * @param string $title
     * @param string $keyword
     * @return bool
     */
    private function validate_seo_title($title, $keyword) {
        $length = mb_strlen($title);
        if ($length < 40 || $length > 60) {
            return false;
        }
        if (!empty($keyword) && stripos($title, $keyword) === false) {
            return false;
        }
        return true;
    }

    /**
     * Truncate text to character limit
     *
     * @param string $text
     * @param int $limit
     * @return string
     */
    private function truncate_to_chars($text, $limit) {
        $text = trim($text);
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $text = mb_substr($text, 0, $limit - 1);
        // Break at last space to avoid cutting words
        $last_space = mb_strrpos($text, ' ');
        if ($last_space !== false && $last_space > $limit * 0.6) {
            $text = mb_substr($text, 0, $last_space);
        }

        return rtrim($text, '.,;:!?') . '...';
    }

    /**
     * Get recent posts for internal linking suggestions
     *
     * @return array
     */
    private function get_recent_posts_for_linking() {
        if (!function_exists('get_posts')) {
            return [];
        }

        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $result = [];
        foreach ($posts as $post) {
            $tags = wp_get_post_tags($post->ID, ['fields' => 'names']);
            $categories = wp_get_post_categories($post->ID, ['fields' => 'names']);
            $keywords = array_merge($tags, $categories);

            $result[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'url' => get_permalink($post->ID),
                'keywords' => $keywords,
            ];
        }

        return $result;
    }
}

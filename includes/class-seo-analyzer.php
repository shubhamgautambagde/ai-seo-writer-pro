<?php
namespace AI_SEO_Writer_Pro;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * SEO Analyzer - Analyzes content for SEO optimization
 *
 * DISCLAIMER: SEO scores provided by this analyzer are guidelines only
 * and do NOT guarantee Google rankings or search engine positions.
 * Search engine algorithms are complex, constantly evolving, and consider
 * hundreds of factors beyond what this tool analyzes.
 */
class SEO_Analyzer {

    /**
     * Scoring weights
     * @var array
     */
    private $weights = [
        'keyword_in_title'          => 10,
        'keyword_in_meta'           => 8,
        'keyword_in_first_para'     => 8,
        'keyword_density'           => 10,
        'content_length'            => 10,
        'heading_structure'         => 10,
        'meta_title_length'         => 5,
        'meta_description_length'   => 5,
        'internal_links'            => 8,
        'external_links'            => 8,
        'image_alt'                 => 8,
        'readability'               => 10,
    ];

    /**
     * Initialize the SEO Analyzer
     */
    public function init() {
        add_action('wp_ajax_ai_seo_writer_pro_analyze', [$this, 'ajax_analyze']);
    }

    /**
     * DISCLAIMER: SEO scores do NOT guarantee Google rankings.
     * These scores are guidelines based on common SEO best practices.
     * Search engine rankings depend on hundreds of factors including
     * domain authority, backlinks, user experience, competition,
     * and algorithm updates that are beyond the scope of this analysis.
     *
     * @var string
     */
    const DISCLAIMER = 'DISCLAIMER: SEO scores are guidelines only and do NOT guarantee Google rankings or search engine positions. Search engine algorithms are complex, constantly evolving, and consider hundreds of factors beyond what this tool analyzes.';

    /**
     * Main analysis method
     *
     * @param string $content The content to analyze
     * @param array $params Analysis parameters
     *   - focus_keyword: string
     *   - seo_title: string
     *   - meta_description: string
     *   - url_slug: string
     * @return array Analysis results
     */
    public function analyze($content, $params = []) {
        $defaults = [
            'focus_keyword'     => '',
            'seo_title'         => '',
            'meta_description'  => '',
            'url_slug'          => '',
        ];
        $params = wp_parse_args($params, $defaults);

        $keyword = isset($params['focus_keyword']) ? trim($params['focus_keyword']) : '';
        $title = isset($params['seo_title']) ? $params['seo_title'] : '';
        $meta_description = isset($params['meta_description']) ? $params['meta_description'] : '';

        $checks = [];

        // Keyword-related checks
        if (!empty($keyword)) {
            $checks['keyword_in_title'] = $this->check_keyword_in_title($title, $keyword);
            $checks['keyword_in_meta'] = $this->check_keyword_in_meta($meta_description, $keyword);
            $checks['keyword_in_first_para'] = $this->check_keyword_in_first_paragraph($content, $keyword);
            $checks['keyword_density'] = $this->check_keyword_density($content, $keyword);
            $checks['keyword_placement'] = $this->check_keyword_placement($content, $keyword);
        } else {
            $checks['keyword_in_title'] = [
                'passed' => false,
                'score' => 0,
                'message' => __('No focus keyword provided.', 'ai-seo-writer-pro'),
                'details' => [],
            ];
            $checks['keyword_in_meta'] = $checks['keyword_in_title'];
            $checks['keyword_in_first_para'] = $checks['keyword_in_title'];
            $checks['keyword_density'] = $checks['keyword_in_title'];
            $checks['keyword_placement'] = $checks['keyword_in_title'];
        }

        // Content structure checks
        $checks['content_length'] = $this->check_content_length($content);
        $checks['heading_structure'] = $this->check_heading_structure($content);
        $checks['meta_title_length'] = $this->check_meta_title_length($title);
        $checks['meta_description_length'] = $this->check_meta_description_length($meta_description);
        $checks['readability'] = $this->check_readability($content);
        $checks['internal_links'] = $this->check_internal_links($content);
        $checks['external_links'] = $this->check_external_links($content);
        $checks['image_alt'] = $this->check_image_alt($content);
        $checks['faq_availability'] = $this->check_faq_availability($content);

        // Calculate scores
        $seo_score = $this->calculate_seo_score($checks);
        $readability_score = $this->calculate_readability_score($content);
        $quality_score = $this->calculate_quality_score($content);

        // Generate suggestions
        $suggestions = $this->generate_suggestions($checks);

        return [
            'seo_score'           => round($seo_score, 2),
            'readability_score'   => round($readability_score, 2),
            'quality_score'       => round($quality_score, 2),
            'seo_score_color'     => $this->get_score_color($seo_score),
            'readability_color'   => $this->get_score_color($readability_score),
            'quality_color'       => $this->get_score_color($quality_score),
            'checks'              => $checks,
            'suggestions'         => $suggestions,
            'disclaimer'          => self::DISCLAIMER,
            'word_count'          => $this->count_words($content),
            'keyword_density_pct' => !empty($keyword) ? $this->get_keyword_density_percentage($content, $keyword) : 0,
        ];
    }

    /**
     * Check if keyword is in SEO title
     *
     * @param string $title
     * @param string $keyword
     * @return array
     */
    public function check_keyword_in_title($title, $keyword) {
        if (empty($title)) {
            return [
                'passed'  => false,
                'score'   => 0,
                'message' => __('SEO title is empty.', 'ai-seo-writer-pro'),
                'details' => [],
            ];
        }

        $title_lower = mb_strtolower($title);
        $keyword_lower = mb_strtolower($keyword);
        $found = strpos($title_lower, $keyword_lower) !== false;

        return [
            'passed'  => $found,
            'score'   => $found ? $this->weights['keyword_in_title'] : 0,
            'message' => $found
                ? __('Focus keyword found in SEO title.', 'ai-seo-writer-pro')
                : __('Focus keyword not found in SEO title.', 'ai-seo-writer-pro'),
            'details' => [
                'title_length' => strlen($title),
            ],
        ];
    }

    /**
     * Check if keyword is in meta description
     *
     * @param string $description
     * @param string $keyword
     * @return array
     */
    public function check_keyword_in_meta($description, $keyword) {
        if (empty($description)) {
            return [
                'passed'  => false,
                'score'   => 0,
                'message' => __('Meta description is empty.', 'ai-seo-writer-pro'),
                'details' => [],
            ];
        }

        $desc_lower = mb_strtolower($description);
        $keyword_lower = mb_strtolower($keyword);
        $found = strpos($desc_lower, $keyword_lower) !== false;

        return [
            'passed'  => $found,
            'score'   => $found ? $this->weights['keyword_in_meta'] : 0,
            'message' => $found
                ? __('Focus keyword found in meta description.', 'ai-seo-writer-pro')
                : __('Focus keyword not found in meta description.', 'ai-seo-writer-pro'),
            'details' => [
                'description_length' => strlen($description),
            ],
        ];
    }

    /**
     * Check keyword density (target 0.5-2.5%)
     *
     * @param string $content
     * @param string $keyword
     * @return array
     */
    public function check_keyword_density($content, $keyword) {
        $word_count = $this->count_words($content);
        $density = $this->get_keyword_density_percentage($content, $keyword);
        $keyword_count = $this->count_keyword_occurrences($content, $keyword);

        $passed = ($density >= 0.5 && $density <= 2.5);
        $score = 0;

        if ($density >= 0.3 && $density <= 3.0) {
            $score = $this->weights['keyword_density'] * 0.5;
        }
        if ($passed) {
            $score = $this->weights['keyword_density'];
        }
        if ($density > 3.0) {
            $score = 0; // Keyword stuffing warning
        }

        $message = '';
        if ($density == 0) {
            $message = __('Focus keyword not found in content.', 'ai-seo-writer-pro');
        } elseif ($density < 0.5) {
            $message = sprintf(
                __('Keyword density is too low (%.2f%%). Target: 0.5-2.5%%.', 'ai-seo-writer-pro'),
                $density
            );
        } elseif ($density > 2.5 && $density <= 3.0) {
            $message = sprintf(
                __('Keyword density is slightly high (%.2f%%). Target: 0.5-2.5%%.', 'ai-seo-writer-pro'),
                $density
            );
        } elseif ($density > 3.0) {
            $message = sprintf(
                __('Keyword density is too high (%.2f%%) - risk of keyword stuffing. Target: 0.5-2.5%%.', 'ai-seo-writer-pro'),
                $density
            );
        } else {
            $message = sprintf(
                __('Keyword density is optimal (%.2f%%).', 'ai-seo-writer-pro'),
                $density
            );
        }

        return [
            'passed'  => $passed,
            'score'   => $score,
            'message' => $message,
            'details' => [
                'density'      => round($density, 2),
                'word_count'   => $word_count,
                'keyword_count' => $keyword_count,
                'target_min'   => 0.5,
                'target_max'   => 2.5,
            ],
        ];
    }

    /**
     * Check keyword placement in first paragraph, headings, etc.
     *
     * @param string $content
     * @param string $keyword
     * @return array
     */
    public function check_keyword_placement($content, $keyword) {
        $keyword_lower = mb_strtolower($keyword);
        $plain_content = wp_strip_all_tags($content);
        $content_lower = mb_strtolower($plain_content);

        $placement_details = [];
        $placement_score = 0;
        $total_checks = 4;

        // Check first paragraph
        $first_para = $this->get_first_paragraph($content);
        $in_first_para = !empty($first_para) && strpos(mb_strtolower($first_para), $keyword_lower) !== false;
        $placement_details['first_paragraph'] = $in_first_para;
        if ($in_first_para) {
            $placement_score++;
        }

        // Check H1
        $h1_matches = [];
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $content, $h1_matches);
        $in_h1 = false;
        if (!empty($h1_matches[1])) {
            foreach ($h1_matches[1] as $h1) {
                if (strpos(mb_strtolower(wp_strip_all_tags($h1)), $keyword_lower) !== false) {
                    $in_h1 = true;
                    break;
                }
            }
        }
        $placement_details['in_h1'] = $in_h1;
        if ($in_h1) {
            $placement_score++;
        }

        // Check H2/H3 headings
        $heading_matches = [];
        preg_match_all('/<h[23][^>]*>(.*?)<\/h[23]>/is', $content, $heading_matches);
        $in_subheadings = false;
        if (!empty($heading_matches[1])) {
            foreach ($heading_matches[1] as $heading) {
                if (strpos(mb_strtolower(wp_strip_all_tags($heading)), $keyword_lower) !== false) {
                    $in_subheadings = true;
                    break;
                }
            }
        }
        $placement_details['in_subheadings'] = $in_subheadings;
        if ($in_subheadings) {
            $placement_score++;
        }

        // Check last paragraph
        $in_last_para = $this->check_keyword_in_last_paragraph($content, $keyword);
        $placement_details['last_paragraph'] = $in_last_para;
        if ($in_last_para) {
            $placement_score++;
        }

        $passed = $placement_score >= 2;
        $score_ratio = $placement_score / $total_checks;

        return [
            'passed'  => $passed,
            'score'   => round($score_ratio * $this->weights['keyword_in_first_para'], 2),
            'message' => sprintf(
                __('Keyword found in %d of %d strategic locations.', 'ai-seo-writer-pro'),
                $placement_score,
                $total_checks
            ),
            'details' => $placement_details,
        ];
    }

    /**
     * Check if keyword is in first paragraph
     *
     * @param string $content
     * @param string $keyword
     * @return array
     */
    public function check_keyword_in_first_paragraph($content, $keyword) {
        $first_para = $this->get_first_paragraph($content);
        $keyword_lower = mb_strtolower($keyword);

        if (empty($first_para)) {
            return [
                'passed'  => false,
                'score'   => 0,
                'message' => __('No first paragraph found.', 'ai-seo-writer-pro'),
                'details' => [],
            ];
        }

        $found = strpos(mb_strtolower($first_para), $keyword_lower) !== false;

        return [
            'passed'  => $found,
            'score'   => $found ? $this->weights['keyword_in_first_para'] : 0,
            'message' => $found
                ? __('Focus keyword appears in the first paragraph.', 'ai-seo-writer-pro')
                : __('Focus keyword not found in the first paragraph. Try to include it within the first 150 words.', 'ai-seo-writer-pro'),
            'details' => [
                'first_paragraph_length' => strlen($first_para),
            ],
        ];
    }

    /**
     * Check content length (min 300 words, optimal 1000+)
     *
     * @param string $content
     * @return array
     */
    public function check_content_length($content) {
        $word_count = $this->count_words($content);
        $score = 0;
        $passed = false;
        $message = '';

        if ($word_count >= 1000) {
            $score = $this->weights['content_length'];
            $passed = true;
            $message = sprintf(
                __('Content length is excellent: %d words.', 'ai-seo-writer-pro'),
                $word_count
            );
        } elseif ($word_count >= 600) {
            $score = $this->weights['content_length'] * 0.8;
            $passed = true;
            $message = sprintf(
                __('Content length is good: %d words. Consider expanding to 1000+ words for better results.', 'ai-seo-writer-pro'),
                $word_count
            );
        } elseif ($word_count >= 300) {
            $score = $this->weights['content_length'] * 0.5;
            $message = sprintf(
                __('Content length is minimum acceptable: %d words. Aim for at least 600 words, ideally 1000+.', 'ai-seo-writer-pro'),
                $word_count
            );
        } else {
            $score = 0;
            $message = sprintf(
                __('Content is too short: %d words. Minimum recommended is 300 words, optimal is 1000+.', 'ai-seo-writer-pro'),
                $word_count
            );
        }

        return [
            'passed'  => $passed,
            'score'   => $score,
            'message' => $message,
            'details' => [
                'word_count'    => $word_count,
                'minimum'       => 300,
                'optimal'       => 1000,
            ],
        ];
    }

    /**
     * Check heading structure (H1 present, H2/H3 hierarchy)
     *
     * @param string $content
     * @return array
     */
    public function check_heading_structure($content) {
        $h1_count = preg_match_all('/<h1[^>]*>/i', $content, $h1_matches);
        $h2_count = preg_match_all('/<h2[^>]*>/i', $content, $h2_matches);
        $h3_count = preg_match_all('/<h3[^>]*>/i', $content, $h3_matches);

        $score = 0;
        $issues = [];
        $details = [
            'h1_count' => $h1_count,
            'h2_count' => $h2_count,
            'h3_count' => $h3_count,
        ];

        // Check H1
        if ($h1_count === 0) {
            $issues[] = __('No H1 heading found.', 'ai-seo-writer-pro');
        } elseif ($h1_count > 1) {
            $issues[] = sprintf(__('Multiple H1 headings found (%d). Should have exactly one.', 'ai-seo-writer-pro'), $h1_count);
        } else {
            $score += 3;
        }

        // Check H2 presence
        if ($h2_count === 0) {
            $issues[] = __('No H2 subheadings found. Break content into logical sections.', 'ai-seo-writer-pro');
        } else {
            $score += 4;
        }

        // Check H3 presence (for hierarchy)
        if ($h3_count > 0) {
            $score += 3;
        }

        $max_score = 10;
        $final_score = ($score / $max_score) * $this->weights['heading_structure'];
        $passed = $score >= 7;

        $message = $passed
            ? __('Heading structure is well-organized.', 'ai-seo-writer-pro')
            : __('Heading structure needs improvement.', 'ai-seo-writer-pro');

        if (!empty($issues)) {
            $message .= ' ' . implode(' ', $issues);
        }

        return [
            'passed'  => $passed,
            'score'   => round($final_score, 2),
            'message' => $message,
            'details' => $details,
        ];
    }

    /**
     * Check meta title length (50-60 chars)
     *
     * @param string $title
     * @return array
     */
    public function check_meta_title_length($title) {
        $length = mb_strlen($title);
        $score = 0;
        $passed = false;
        $message = '';

        if (empty($title)) {
            $message = __('SEO title is empty.', 'ai-seo-writer-pro');
        } elseif ($length >= 50 && $length <= 60) {
            $score = $this->weights['meta_title_length'];
            $passed = true;
            $message = sprintf(
                __('SEO title length is optimal: %d characters.', 'ai-seo-writer-pro'),
                $length
            );
        } elseif ($length >= 40 && $length < 50) {
            $score = $this->weights['meta_title_length'] * 0.6;
            $message = sprintf(
                __('SEO title is a bit short: %d characters. Target: 50-60.', 'ai-seo-writer-pro'),
                $length
            );
        } elseif ($length > 60 && $length <= 70) {
            $score = $this->weights['meta_title_length'] * 0.6;
            $message = sprintf(
                __('SEO title is slightly long: %d characters. May get truncated. Target: 50-60.', 'ai-seo-writer-pro'),
                $length
            );
        } elseif ($length > 70) {
            $message = sprintf(
                __('SEO title is too long: %d characters. Will likely be truncated in search results. Target: 50-60.', 'ai-seo-writer-pro'),
                $length
            );
        } else {
            $message = sprintf(
                __('SEO title is too short: %d characters. Target: 50-60.', 'ai-seo-writer-pro'),
                $length
            );
        }

        return [
            'passed'  => $passed,
            'score'   => $score,
            'message' => $message,
            'details' => [
                'length'     => $length,
                'target_min' => 50,
                'target_max' => 60,
            ],
        ];
    }

    /**
     * Check meta description length (150-160 chars)
     *
     * @param string $description
     * @return array
     */
    public function check_meta_description_length($description) {
        $length = mb_strlen($description);
        $score = 0;
        $passed = false;
        $message = '';

        if (empty($description)) {
            $message = __('Meta description is empty.', 'ai-seo-writer-pro');
        } elseif ($length >= 150 && $length <= 160) {
            $score = $this->weights['meta_description_length'];
            $passed = true;
            $message = sprintf(
                __('Meta description length is optimal: %d characters.', 'ai-seo-writer-pro'),
                $length
            );
        } elseif ($length >= 120 && $length < 150) {
            $score = $this->weights['meta_description_length'] * 0.6;
            $message = sprintf(
                __('Meta description is a bit short: %d characters. Target: 150-160.', 'ai-seo-writer-pro'),
                $length
            );
        } elseif ($length > 160 && $length <= 180) {
            $score = $this->weights['meta_description_length'] * 0.6;
            $message = sprintf(
                __('Meta description is slightly long: %d characters. May get truncated. Target: 150-160.', 'ai-seo-writer-pro'),
                $length
            );
        } elseif ($length > 180) {
            $message = sprintf(
                __('Meta description is too long: %d characters. Will likely be truncated. Target: 150-160.', 'ai-seo-writer-pro'),
                $length
            );
        } else {
            $message = sprintf(
                __('Meta description is too short: %d characters. Target: 150-160.', 'ai-seo-writer-pro'),
                $length
            );
        }

        return [
            'passed'  => $passed,
            'score'   => $score,
            'message' => $message,
            'details' => [
                'length'     => $length,
                'target_min' => 150,
                'target_max' => 160,
            ],
        ];
    }

    /**
     * Check readability using Flesch-Kincaid, sentence length, paragraph length
     *
     * @param string $content
     * @return array
     */
    public function check_readability($content) {
        $plain_text = wp_strip_all_tags($content);
        $flesch_score = $this->calculate_flesch_reading_ease($plain_text);
        $avg_sentence_length = $this->get_average_sentence_length($plain_text);
        $avg_paragraph_length = $this->get_average_paragraph_length($content);
        $syllable_count = $this->count_syllables_text($plain_text);
        $word_count = $this->count_words($plain_text);
        $sentence_count = $this->count_sentences($plain_text);

        $score = 0;
        $issues = [];

        // Flesch reading ease scoring
        if ($flesch_score >= 60) {
            $score += 5;
        } elseif ($flesch_score >= 50) {
            $score += 3;
        } elseif ($flesch_score >= 40) {
            $score += 1;
        } else {
            $issues[] = __('Text is difficult to read.', 'ai-seo-writer-pro');
        }

        // Sentence length check
        if ($avg_sentence_length > 0 && $avg_sentence_length <= 20) {
            $score += 3;
        } elseif ($avg_sentence_length > 25) {
            $issues[] = sprintf(
                __('Average sentence length is high: %.1f words. Try to keep sentences under 20 words.', 'ai-seo-writer-pro'),
                $avg_sentence_length
            );
        }

        // Paragraph length check
        if ($avg_paragraph_length > 0 && $avg_paragraph_length <= 5) {
            $score += 2;
        } elseif ($avg_paragraph_length > 8) {
            $issues[] = sprintf(
                __('Average paragraph length is high: %.1f sentences. Break long paragraphs into shorter ones.', 'ai-seo-writer-pro'),
                $avg_paragraph_length
            );
        }

        $max_score = 10;
        $final_score = ($score / $max_score) * $this->weights['readability'];
        $passed = $score >= 7;

        $message = $passed
            ? __('Readability is good.', 'ai-seo-writer-pro')
            : __('Readability needs improvement.', 'ai-seo-writer-pro');

        if (!empty($issues)) {
            $message .= ' ' . implode(' ', $issues);
        }

        return [
            'passed'  => $passed,
            'score'   => round($final_score, 2),
            'message' => $message,
            'details' => [
                'flesch_reading_ease'    => round($flesch_score, 2),
                'avg_sentence_length'    => round($avg_sentence_length, 1),
                'avg_paragraph_length'   => round($avg_paragraph_length, 1),
                'word_count'             => $word_count,
                'sentence_count'         => $sentence_count,
                'syllable_count'         => $syllable_count,
            ],
        ];
    }

    /**
     * Check presence of internal links
     *
     * @param string $content
     * @return array
     */
    public function check_internal_links($content) {
        $site_url = get_site_url();
        $site_host = wp_parse_url($site_url, PHP_URL_HOST);

        $link_matches = [];
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $link_matches);

        $internal_count = 0;
        if (!empty($link_matches[1])) {
            foreach ($link_matches[1] as $href) {
                if ($this->is_internal_link($href, $site_host)) {
                    $internal_count++;
                }
            }
        }

        $passed = $internal_count > 0;
        $score = $passed ? $this->weights['internal_links'] : 0;

        return [
            'passed'  => $passed,
            'score'   => $score,
            'message' => $passed
                ? sprintf(__('Found %d internal link(s). Good for SEO.', 'ai-seo-writer-pro'), $internal_count)
                : __('No internal links found. Add links to other relevant pages on your site.', 'ai-seo-writer-pro'),
            'details' => [
                'internal_link_count' => $internal_count,
                'total_link_count'    => count($link_matches[1]),
            ],
        ];
    }

    /**
     * Check presence of external links
     *
     * @param string $content
     * @return array
     */
    public function check_external_links($content) {
        $site_url = get_site_url();
        $site_host = wp_parse_url($site_url, PHP_URL_HOST);

        $link_matches = [];
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $link_matches);

        $external_count = 0;
        if (!empty($link_matches[1])) {
            foreach ($link_matches[1] as $href) {
                if (!$this->is_internal_link($href, $site_host) && $this->is_external_link($href)) {
                    $external_count++;
                }
            }
        }

        $passed = $external_count > 0;
        $score = $passed ? $this->weights['external_links'] : 0;

        return [
            'passed'  => $passed,
            'score'   => $score,
            'message' => $passed
                ? sprintf(__('Found %d external link(s). Good for credibility.', 'ai-seo-writer-pro'), $external_count)
                : __('No external links found. Add links to authoritative external sources when relevant.', 'ai-seo-writer-pro'),
            'details' => [
                'external_link_count' => $external_count,
                'total_link_count'    => count($link_matches[1]),
            ],
        ];
    }

    /**
     * Check if images have alt attributes
     *
     * @param string $content
     * @return array
     */
    public function check_image_alt($content) {
        $img_matches = [];
        preg_match_all('/<img[^>]+>/i', $content, $img_matches);

        $total_images = count($img_matches[0]);
        $images_with_alt = 0;

        if ($total_images > 0) {
            foreach ($img_matches[0] as $img_tag) {
                if (preg_match('/alt=["\']([^"\']*)["\']/i', $img_tag, $alt_match)) {
                    if (!empty(trim($alt_match[1]))) {
                        $images_with_alt++;
                    }
                }
            }
        }

        $score = 0;
        $passed = false;
        $message = '';

        if ($total_images === 0) {
            $message = __('No images found in content. Consider adding relevant images with descriptive alt text.', 'ai-seo-writer-pro');
        } else {
            $ratio = $images_with_alt / $total_images;
            if ($ratio >= 1.0) {
                $score = $this->weights['image_alt'];
                $passed = true;
                $message = sprintf(
                    __('All %d image(s) have alt text.', 'ai-seo-writer-pro'),
                    $total_images
                );
            } elseif ($ratio >= 0.7) {
                $score = $this->weights['image_alt'] * 0.7;
                $message = sprintf(
                    __('%d of %d images have alt text. Add alt text to remaining images.', 'ai-seo-writer-pro'),
                    $images_with_alt,
                    $total_images
                );
            } else {
                $message = sprintf(
                    __('%d of %d images have alt text. Alt text is important for image SEO and accessibility.', 'ai-seo-writer-pro'),
                    $images_with_alt,
                    $total_images
                );
            }
        }

        return [
            'passed'  => $passed,
            'score'   => $score,
            'message' => $message,
            'details' => [
                'total_images'      => $total_images,
                'images_with_alt'   => $images_with_alt,
                'images_without_alt' => $total_images - $images_with_alt,
            ],
        ];
    }

    /**
     * Check if FAQ section is present
     *
     * @param string $content
     * @return array
     */
    public function check_faq_availability($content) {
        $content_lower = mb_strtolower($content);

        $faq_indicators = [
            'faq',
            'frequently asked questions',
            'common questions',
            'questions and answers',
            'q&a',
        ];

        $found = false;
        $matched_indicator = '';

        // Check headings for FAQ
        $heading_matches = [];
        preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', $content, $heading_matches);

        if (!empty($heading_matches[1])) {
            foreach ($heading_matches[1] as $heading) {
                $heading_lower = mb_strtolower(wp_strip_all_tags($heading));
                foreach ($faq_indicators as $indicator) {
                    if (strpos($heading_lower, $indicator) !== false) {
                        $found = true;
                        $matched_indicator = $indicator;
                        break 2;
                    }
                }
            }
        }

        // Also check for FAQ schema or class
        if (!$found) {
            if (strpos($content_lower, 'class="faq') !== false ||
                strpos($content_lower, "class='faq") !== false ||
                strpos($content_lower, 'schema.org/faqpage') !== false) {
                $found = true;
                $matched_indicator = 'faq-schema';
            }
        }

        return [
            'passed'  => $found,
            'score'   => 0, // FAQ is informational, not part of weighted SEO score
            'message' => $found
                ? sprintf(__('FAQ section detected (%s). This can help with featured snippets.', 'ai-seo-writer-pro'), $matched_indicator)
                : __('No FAQ section detected. Consider adding an FAQ section to target long-tail keywords and featured snippets.', 'ai-seo-writer-pro'),
            'details' => [
                'faq_detected' => $found,
                'matched'      => $matched_indicator,
            ],
        ];
    }

    /**
     * Calculate overall SEO score (0-100)
     *
     * @param array $checks
     * @return float
     */
    public function calculate_seo_score($checks) {
        $total_possible = array_sum($this->weights);
        $total_earned = 0;

        foreach ($this->weights as $key => $weight) {
            if (isset($checks[$key]['score'])) {
                $total_earned += (float) $checks[$key]['score'];
            }
        }

        if ($total_possible === 0) {
            return 0;
        }

        return ($total_earned / $total_possible) * 100;
    }

    /**
     * Calculate readability score (0-100) based on Flesch reading ease
     *
     * @param string $content
     * @return float
     */
    public function calculate_readability_score($content) {
        $plain_text = wp_strip_all_tags($content);
        $flesch = $this->calculate_flesch_reading_ease($plain_text);

        // Map Flesch score (0-100) to our 0-100 scale
        // Flesch: higher = easier to read
        $score = max(0, min(100, $flesch));

        return $score;
    }

    /**
     * Calculate quality score based on structure, variety, length
     *
     * @param string $content
     * @return float
     */
    public function calculate_quality_score($content) {
        $score = 0;
        $components = [];

        // Word count component (up to 30 points)
        $word_count = $this->count_words($content);
        if ($word_count >= 2000) {
            $components['length'] = 30;
        } elseif ($word_count >= 1500) {
            $components['length'] = 25;
        } elseif ($word_count >= 1000) {
            $components['length'] = 20;
        } elseif ($word_count >= 600) {
            $components['length'] = 12;
        } elseif ($word_count >= 300) {
            $components['length'] = 6;
        } else {
            $components['length'] = 0;
        }

        // Heading structure component (up to 25 points)
        $h1_count = preg_match_all('/<h1[^>]*>/i', $content, $m);
        $h2_count = preg_match_all('/<h2[^>]*>/i', $content, $m);
        $h3_count = preg_match_all('/<h3[^>]*>/i', $content, $m);

        $heading_score = 0;
        if ($h1_count === 1) {
            $heading_score += 8;
        }
        if ($h2_count >= 3) {
            $heading_score += 10;
        } elseif ($h2_count >= 1) {
            $heading_score += 5;
        }
        if ($h3_count >= 2) {
            $heading_score += 7;
        } elseif ($h3_count >= 1) {
            $heading_score += 3;
        }
        $components['headings'] = min(25, $heading_score);

        // Media variety component (up to 20 points)
        $img_count = preg_match_all('/<img[^>]+>/i', $content, $m);
        $list_count = preg_match_all('/<[ou]l[^>]*>/i', $content, $m);
        $table_count = preg_match_all('/<table[^>]*>/i', $content, $m);
        $blockquote_count = preg_match_all('/<blockquote[^>]*>/i', $content, $m);

        $media_score = 0;
        if ($img_count > 0) {
            $media_score += min(10, $img_count * 2);
        }
        if ($list_count > 0) {
            $media_score += min(5, $list_count * 2);
        }
        if ($table_count > 0) {
            $media_score += 3;
        }
        if ($blockquote_count > 0) {
            $media_score += 2;
        }
        $components['media'] = min(20, $media_score);

        // Link variety component (up to 15 points)
        $internal_check = $this->check_internal_links($content);
        $external_check = $this->check_external_links($content);

        $link_score = 0;
        if (!empty($internal_check['details']['internal_link_count'])) {
            $link_score += min(8, $internal_check['details']['internal_link_count'] * 2);
        }
        if (!empty($external_check['details']['external_link_count'])) {
            $link_score += min(7, $external_check['details']['external_link_count'] * 2);
        }
        $components['links'] = $link_score;

        // Paragraph structure component (up to 10 points)
        $paragraphs = $this->get_paragraphs($content);
        $para_count = count($paragraphs);
        if ($para_count >= 8) {
            $components['paragraphs'] = 10;
        } elseif ($para_count >= 5) {
            $components['paragraphs'] = 7;
        } elseif ($para_count >= 3) {
            $components['paragraphs'] = 4;
        } elseif ($para_count >= 1) {
            $components['paragraphs'] = 2;
        } else {
            $components['paragraphs'] = 0;
        }

        $total = array_sum($components);
        return max(0, min(100, $total));
    }

    /**
     * Generate human-readable improvement suggestions
     *
     * @param array $checks
     * @return array
     */
    public function generate_suggestions($checks) {
        $suggestions = [];

        $suggestion_map = [
            'keyword_in_title' => [
                'false' => __('Add your focus keyword to the SEO title. Place it near the beginning for best results.', 'ai-seo-writer-pro'),
            ],
            'keyword_in_meta' => [
                'false' => __('Include your focus keyword in the meta description to improve click-through rates.', 'ai-seo-writer-pro'),
            ],
            'keyword_in_first_para' => [
                'false' => __('Place your focus keyword within the first paragraph (first 150 words) of your content.', 'ai-seo-writer-pro'),
            ],
            'keyword_density' => [
                'false' => __('Adjust your keyword density to be within the 0.5-2.5% range. Avoid keyword stuffing.', 'ai-seo-writer-pro'),
            ],
            'content_length' => [
                'false' => __('Expand your content to at least 600 words. Aim for 1000+ words for comprehensive coverage.', 'ai-seo-writer-pro'),
            ],
            'heading_structure' => [
                'false' => __('Improve your heading structure. Use exactly one H1, and organize content with H2 and H3 subheadings.', 'ai-seo-writer-pro'),
            ],
            'meta_title_length' => [
                'false' => __('Adjust your SEO title to be between 50-60 characters to avoid truncation in search results.', 'ai-seo-writer-pro'),
            ],
            'meta_description_length' => [
                'false' => __('Adjust your meta description to be between 150-160 characters for optimal display.', 'ai-seo-writer-pro'),
            ],
            'readability' => [
                'false' => __('Improve readability by using shorter sentences, shorter paragraphs, and simpler language where possible.', 'ai-seo-writer-pro'),
            ],
            'internal_links' => [
                'false' => __('Add internal links to other relevant pages on your website to improve site structure and user navigation.', 'ai-seo-writer-pro'),
            ],
            'external_links' => [
                'false' => __('Add links to authoritative external sources when relevant to boost content credibility.', 'ai-seo-writer-pro'),
            ],
            'image_alt' => [
                'false' => __('Add descriptive alt text to all images. This helps with image SEO and accessibility.', 'ai-seo-writer-pro'),
            ],
            'faq_availability' => [
                'false' => __('Consider adding an FAQ section to target long-tail keywords and increase chances of featured snippets.', 'ai-seo-writer-pro'),
            ],
        ];

        foreach ($suggestion_map as $check_key => $rules) {
            if (isset($checks[$check_key]) && empty($checks[$check_key]['passed'])) {
                if (isset($rules['false'])) {
                    $suggestions[] = $rules['false'];
                }
            }
        }

        if (empty($suggestions)) {
            $suggestions[] = __('Great job! Your content passes all major SEO checks. Continue refining and updating content regularly.', 'ai-seo-writer-pro');
        }

        return $suggestions;
    }

    /**
     * Get score color based on score value
     *
     * @param float $score
     * @return string 'red', 'yellow', or 'green'
     */
    public function get_score_color($score) {
        $score = (float) $score;
        if ($score >= 70) {
            return 'green';
        } elseif ($score >= 40) {
            return 'yellow';
        }
        return 'red';
    }

    /**
     * AJAX handler for analyze action
     */
    public function ajax_analyze() {
        $security = new Security_Manager();

        if (!$security->verify_ajax_request('edit_posts')) {
            return;
        }

        $content = isset($_POST['content']) ? $security->sanitize_html($_POST['content']) : '';
        $params = [
            'focus_keyword'     => isset($_POST['focus_keyword']) ? $security->sanitize_text($_POST['focus_keyword']) : '',
            'seo_title'         => isset($_POST['seo_title']) ? $security->sanitize_text($_POST['seo_title']) : '',
            'meta_description'  => isset($_POST['meta_description']) ? $security->sanitize_textarea($_POST['meta_description']) : '',
            'url_slug'          => isset($_POST['url_slug']) ? $security->sanitize_text($_POST['url_slug']) : '',
        ];

        $results = $this->analyze($content, $params);

        wp_send_json_success($results);
    }

    /*
     * Helper methods
     */

    /**
     * Count words in content
     *
     * @param string $content
     * @return int
     */
    private function count_words($content) {
        $plain_text = wp_strip_all_tags($content);
        $plain_text = trim($plain_text);
        if (empty($plain_text)) {
            return 0;
        }
        // Use str_word_count with UTF-8 compatibility approach
        $words = preg_split('/\s+/', $plain_text, -1, PREG_SPLIT_NO_EMPTY);
        return count($words);
    }

    /**
     * Count keyword occurrences
     *
     * @param string $content
     * @param string $keyword
     * @return int
     */
    private function count_keyword_occurrences($content, $keyword) {
        $plain_text = wp_strip_all_tags($content);
        $content_lower = mb_strtolower($plain_text);
        $keyword_lower = mb_strtolower(trim($keyword));

        if (empty($keyword_lower)) {
            return 0;
        }

        $count = substr_count($content_lower, $keyword_lower);
        return $count;
    }

    /**
     * Get keyword density percentage
     *
     * @param string $content
     * @param string $keyword
     * @return float
     */
    private function get_keyword_density_percentage($content, $keyword) {
        $word_count = $this->count_words($content);
        if ($word_count === 0) {
            return 0;
        }

        $keyword_words = $this->count_words($keyword);
        $occurrences = $this->count_keyword_occurrences($content, $keyword);

        if ($keyword_words === 0) {
            return 0;
        }

        // For multi-word keywords, count occurrences as single instances
        return ($occurrences / $word_count) * 100;
    }

    /**
     * Get first paragraph from content
     *
     * @param string $content
     * @return string
     */
    private function get_first_paragraph($content) {
        // Try to find first <p> tag
        if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $content, $matches)) {
            return wp_strip_all_tags($matches[1]);
        }

        // Fallback: split by double newlines
        $plain_text = wp_strip_all_tags($content);
        $paragraphs = preg_split('/\n\s*\n/', $plain_text, -1, PREG_SPLIT_NO_EMPTY);

        return !empty($paragraphs[0]) ? $paragraphs[0] : '';
    }

    /**
     * Check if keyword is in last paragraph
     *
     * @param string $content
     * @param string $keyword
     * @return bool
     */
    private function check_keyword_in_last_paragraph($content, $keyword) {
        $paragraphs = $this->get_paragraphs($content);
        if (empty($paragraphs)) {
            return false;
        }

        $last_para = end($paragraphs);
        $keyword_lower = mb_strtolower($keyword);
        return strpos(mb_strtolower($last_para), $keyword_lower) !== false;
    }

    /**
     * Get paragraphs from content
     *
     * @param string $content
     * @return array
     */
    private function get_paragraphs($content) {
        $paragraphs = [];

        // Try <p> tags first
        if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $content, $matches)) {
            foreach ($matches[1] as $para) {
                $clean = trim(wp_strip_all_tags($para));
                if (!empty($clean)) {
                    $paragraphs[] = $clean;
                }
            }
        }

        // Fallback to plain text splitting
        if (empty($paragraphs)) {
            $plain_text = wp_strip_all_tags($content);
            $split = preg_split('/\n\s*\n/', $plain_text, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($split as $para) {
                $clean = trim($para);
                if (!empty($clean)) {
                    $paragraphs[] = $clean;
                }
            }
        }

        return $paragraphs;
    }

    /**
     * Calculate Flesch Reading Ease score
     * Formula: 206.835 - 1.015 * (total words / total sentences) - 84.6 * (total syllables / total words)
     *
     * @param string $text
     * @return float
     */
    private function calculate_flesch_reading_ease($text) {
        $word_count = $this->count_words($text);
        $sentence_count = $this->count_sentences($text);
        $syllable_count = $this->count_syllables_text($text);

        if ($word_count === 0 || $sentence_count === 0) {
            return 0;
        }

        $words_per_sentence = $word_count / $sentence_count;
        $syllables_per_word = $syllable_count / max(1, $word_count);

        $score = 206.835 - (1.015 * $words_per_sentence) - (84.6 * $syllables_per_word);

        return max(0, min(100, $score));
    }

    /**
     * Count sentences in text
     *
     * @param string $text
     * @return int
     */
    private function count_sentences($text) {
        $text = wp_strip_all_tags($text);
        if (empty(trim($text))) {
            return 0;
        }

        // Split by sentence-ending punctuation
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $count = 0;

        foreach ($sentences as $sentence) {
            if (trim($sentence) !== '' && $this->count_words($sentence) > 0) {
                $count++;
            }
        }

        return max(1, $count);
    }

    /**
     * Count syllables in text
     *
     * @param string $text
     * @return int
     */
    private function count_syllables_text($text) {
        $text = wp_strip_all_tags(strtolower($text));
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $total_syllables = 0;

        foreach ($words as $word) {
            $total_syllables += $this->count_syllables_word($word);
        }

        return max(1, $total_syllables);
    }

    /**
     * Count syllables in a single word (English approximation)
     *
     * @param string $word
     * @return int
     */
    private function count_syllables_word($word) {
        $word = preg_replace('/[^a-z]/', '', $word);

        if (empty($word)) {
            return 0;
        }

        // Exception words
        $exceptions = [
            'the' => 1, 'be' => 1, 'are' => 2, 'is' => 1, 'was' => 1,
            'were' => 1, 'been' => 1, 'being' => 2, 'have' => 1, 'has' => 1,
            'had' => 1, 'having' => 2, 'do' => 1, 'does' => 1, 'did' => 1,
            'doing' => 2, 'done' => 1, 'every' => 2, 'family' => 3,
        ];

        if (isset($exceptions[$word])) {
            return $exceptions[$word];
        }

        // Count vowel groups
        $vowels = 'aeiouy';
        $count = 0;
        $prev_vowel = false;

        for ($i = 0; $i < strlen($word); $i++) {
            $is_vowel = strpos($vowels, $word[$i]) !== false;
            if ($is_vowel && !$prev_vowel) {
                $count++;
            }
            $prev_vowel = $is_vowel;
        }

        // Silent 'e' at end
        if (substr($word, -1) === 'e' && $count > 1) {
            $count--;
        }

        // Words ending in 'le' preceded by consonant
        if (strlen($word) > 2 && substr($word, -2) === 'le') {
            $prev_char = substr($word, -3, 1);
            if (strpos($vowels, $prev_char) === false) {
                $count++;
            }
        }

        return max(1, $count);
    }

    /**
     * Get average sentence length in words
     *
     * @param string $text
     * @return float
     */
    private function get_average_sentence_length($text) {
        $sentence_count = $this->count_sentences($text);
        $word_count = $this->count_words($text);

        if ($sentence_count === 0) {
            return 0;
        }

        return $word_count / $sentence_count;
    }

    /**
     * Get average paragraph length in sentences
     *
     * @param string $content
     * @return float
     */
    private function get_average_paragraph_length($content) {
        $paragraphs = $this->get_paragraphs($content);
        $para_count = count($paragraphs);

        if ($para_count === 0) {
            return 0;
        }

        $total_sentences = 0;
        foreach ($paragraphs as $para) {
            $total_sentences += $this->count_sentences($para);
        }

        return $total_sentences / $para_count;
    }

    /**
     * Check if a link is internal
     *
     * @param string $href
     * @param string $site_host
     * @return bool
     */
    private function is_internal_link($href, $site_host) {
        // Anchor links
        if (strpos($href, '#') === 0) {
            return true;
        }

        // Relative links
        if (strpos($href, '/') === 0 && strpos($href, '//') !== 0) {
            return true;
        }

        // Check host
        $link_host = wp_parse_url($href, PHP_URL_HOST);
        if (!empty($link_host) && $link_host === $site_host) {
            return true;
        }

        return false;
    }

    /**
     * Check if a link is external (has a http/https scheme)
     *
     * @param string $href
     * @return bool
     */
    private function is_external_link($href) {
        $scheme = wp_parse_url($href, PHP_URL_SCHEME);
        return in_array(strtolower($scheme), ['http', 'https'], true);
    }
}

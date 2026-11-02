/* ============================================================
   AI SEO Writer Pro - Admin JavaScript
   Handles AJAX, forms, modals, tabs, and UI interactions
   ============================================================ */

(function ($) {
    'use strict';

    // Get nonce from localized data (never hardcode)
    var nonceName = AI_SEO_Writer_Pro.nonce_name;
    var nonceValue = AI_SEO_Writer_Pro.nonce;
    var ajaxUrl = AI_SEO_Writer_Pro.ajax_url;
    var strings = AI_SEO_Writer_Pro.strings;

    /**
     * Get nonce data for AJAX requests
     */
    function getNonceData() {
        var data = {};
        data[nonceName] = nonceValue;
        return data;
    }

    /**
     * Refresh nonce after successful AJAX calls
     */
    function refreshNonce(response) {
        if (response && response.data && response.data.new_nonce) {
            nonceValue = response.data.new_nonce;
        }
    }

    /**
     * Show alert message
     */
    function showAlert(message, type, container) {
        type = type || 'info';
        var $container = container ? $(container) : $('.ai-seo-writer-pro-content');
        var icon = '';

        switch (type) {
            case 'success':
                icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>';
                break;
            case 'error':
                icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
                break;
            case 'warning':
                icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
                break;
            default:
                icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
        }

        var $alert = $('<div class="ai-seo-writer-pro-alert ai-seo-writer-pro-alert-' + type + '">' +
            icon + '<span>' + message + '</span></div>');

        $container.prepend($alert);

        setTimeout(function () {
            $alert.fadeOut(300, function () {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Show loading overlay
     */
    function showLoading($element) {
        if (!$element.find('.ai-seo-writer-pro-loading-overlay').length) {
            $element.css('position', 'relative').append(
                '<div class="ai-seo-writer-pro-loading-overlay active"><div class="ai-seo-writer-pro-loading"></div></div>'
            );
        } else {
            $element.find('.ai-seo-writer-pro-loading-overlay').addClass('active');
        }
    }

    /**
     * Hide loading overlay
     */
    function hideLoading($element) {
        $element.find('.ai-seo-writer-pro-loading-overlay').removeClass('active');
    }

    /**
     * Set button loading state
     */
    function setButtonLoading($btn, loading) {
        if (loading) {
            $btn.prop('disabled', true).data('original-text', $btn.html());
            $btn.html('<span class="ai-seo-writer-pro-loading"></span> ' + strings.loading);
        } else {
            $btn.prop('disabled', false).html($btn.data('original-text'));
        }
    }

    /**
     * Modal management
     */
    var Modal = {
        open: function (content, options) {
            options = options || {};
            var size = options.size || '';
            var title = options.title || '';

            var $overlay = $('<div class="ai-seo-writer-pro-modal-overlay active"></div>');
            var sizeClass = size === 'lg' ? ' ai-seo-writer-pro-modal-lg' : '';
            var $modal = $('<div class="ai-seo-writer-pro-modal' + sizeClass + '"></div>');

            var header = '';
            if (title) {
                header = '<div class="ai-seo-writer-pro-modal-header">' +
                    '<h3 class="ai-seo-writer-pro-modal-title">' + title + '</h3>' +
                    '<button type="button" class="ai-seo-writer-pro-modal-close" aria-label="Close">' +
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                    '</button></div>';
            }

            $modal.html(header + '<div class="ai-seo-writer-pro-modal-body">' + content + '</div>');

            if (options.footer) {
                $modal.append('<div class="ai-seo-writer-pro-modal-footer">' + options.footer + '</div>');
            }

            $overlay.html($modal);
            $('body').append($overlay).css('overflow', 'hidden');

            $overlay.on('click', function (e) {
                if ($(e.target).is($overlay)) {
                    Modal.close();
                }
            });

            $overlay.find('.ai-seo-writer-pro-modal-close').on('click', function () {
                Modal.close();
            });

            $(document).on('keydown.ai_seo_modal', function (e) {
                if (e.key === 'Escape') {
                    Modal.close();
                }
            });

            return $overlay;
        },

        close: function () {
            $('.ai-seo-writer-pro-modal-overlay').fadeOut(200, function () {
                $(this).remove();
            });
            $('body').css('overflow', '');
            $(document).off('keydown.ai_seo_modal');
        }
    };

    /**
     * Tab switching
     */
    function initTabs() {
        $(document).on('click', '.ai-seo-writer-pro-tab', function (e) {
            e.preventDefault();
            var $tab = $(this);
            var target = $tab.data('target');

            $tab.addClass('active').siblings().removeClass('active');

            if (target) {
                $(target).addClass('active').siblings('.ai-seo-writer-pro-tab-panel').removeClass('active');
            } else {
                var $container = $tab.closest('.ai-seo-writer-pro-tabs');
                var index = $tab.index();
                $container.nextAll('.ai-seo-writer-pro-tab-panel').eq(index)
                    .addClass('active').siblings('.ai-seo-writer-pro-tab-panel').removeClass('active');
            }
        });
    }

    /**
     * Form validation
     */
    function validateForm($form) {
        var isValid = true;
        $form.find('[data-required="true"]').each(function () {
            var $field = $(this);
            if (!$field.val().trim()) {
                $field.addClass('error');
                isValid = false;
            } else {
                $field.removeClass('error');
            }
        });
        return isValid;
    }

    /**
     * Article Generator
     */
    var ArticleGenerator = {
        init: function () {
            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            $(document).on('click', '#ai-seo-generate-btn', function (e) {
                e.preventDefault();
                self.generate();
            });

            $(document).on('click', '#ai-seo-save-draft-btn', function (e) {
                e.preventDefault();
                self.saveDraft();
            });

            $(document).on('click', '#ai-seo-publish-btn', function (e) {
                e.preventDefault();
                self.publish();
            });

            $(document).on('input', '#ai-seo-title-input, #ai-seo-meta-desc-input', function () {
                self.updateSeoPreview();
            });

            $(document).on('change', '#ai-seo-template-select', function () {
                self.loadTemplate($(this).val());
            });
        },

        generate: function () {
            var $form = $('#ai-seo-article-form');
            if (!validateForm($form)) {
                showAlert(strings.error + ': ' + 'Please fill in all required fields.', 'error');
                return;
            }

            var $btn = $('#ai-seo-generate-btn');
            var $output = $('#ai-seo-output-area');

            setButtonLoading($btn, true);
            showLoading($output);

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_generate_article',
                topic: $('#ai-seo-topic').val(),
                primary_keyword: $('#ai-seo-primary-keyword').val(),
                secondary_keywords: $('#ai-seo-secondary-keywords').val().split(',').map(function (s) { return s.trim(); }).filter(Boolean),
                title: $('#ai-seo-title-input').val(),
                language: $('#ai-seo-language').val(),
                tone: $('#ai-seo-tone').val(),
                content_type: $('#ai-seo-content-type').val(),
                article_length: $('#ai-seo-article-length').val(),
                search_intent: $('#ai-seo-search-intent').val(),
                target_audience: $('#ai-seo-target-audience').val(),
                ai_provider: $('#ai-seo-provider').val(),
                ai_model: $('#ai-seo-model').val(),
                template_id: $('#ai-seo-template-select').val()
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);
                hideLoading($output);

                if (response.success) {
                    var result = response.data;
                    $('#ai-seo-generated-content').val(result.content || '');
                    $('#ai-seo-generated-title').val(result.title || '');
                    $('#ai-seo-generated-seo-title').val(result.seo_title || '');
                    $('#ai-seo-generated-meta-desc').val(result.meta_description || '');

                    if (result.content) {
                        $output.find('.ai-seo-writer-pro-output-content').html(result.content);
                        $output.find('.ai-seo-writer-pro-output-empty').hide();
                        $output.find('.ai-seo-writer-pro-output-content').show();
                    }

                    $('#ai-seo-article-results').removeClass('ai-seo-writer-pro-hidden');
                    showAlert(strings.success, 'success');

                    // Update SEO preview
                    $('#ai-seo-title-input').val(result.title || '');
                    $('#ai-seo-meta-desc-input').val(result.meta_description || '');
                    self.updateSeoPreview();

                    // Update token usage display
                    if (result.tokens_used) {
                        $('#ai-seo-tokens-used').text(result.tokens_used);
                    }
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                hideLoading($output);
                showAlert(strings.error, 'error');
            });
        },

        saveDraft: function () {
            var $btn = $('#ai-seo-save-draft-btn');
            setButtonLoading($btn, true);

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_save_article',
                title: $('#ai-seo-generated-title').val(),
                content: $('#ai-seo-generated-content').val(),
                seo_title: $('#ai-seo-generated-seo-title').val(),
                meta_description: $('#ai-seo-generated-meta-desc').val()
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert(strings.success, 'success');
                    if (response.data.edit_url) {
                        window.open(response.data.edit_url, '_blank');
                    }
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                showAlert(strings.error, 'error');
            });
        },

        publish: function () {
            if (!confirm(strings.confirm)) return;

            var $btn = $('#ai-seo-publish-btn');
            setButtonLoading($btn, true);

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_publish_article',
                title: $('#ai-seo-generated-title').val(),
                content: $('#ai-seo-generated-content').val(),
                seo_title: $('#ai-seo-generated-seo-title').val(),
                meta_description: $('#ai-seo-generated-meta-desc').val()
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert(strings.success, 'success');
                    if (response.data.view_url) {
                        window.open(response.data.view_url, '_blank');
                    }
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                showAlert(strings.error, 'error');
            });
        },

        updateSeoPreview: function () {
            var title = $('#ai-seo-title-input').val() || 'Example Article Title';
            var metaDesc = $('#ai-seo-meta-desc-input').val() || 'This is an example meta description that will appear in search engine results...';
            var url = window.location.hostname + '/example-article';

            $('#seo-preview-title').text(title.substring(0, 60));
            $('#seo-preview-url').text(url);
            $('#seo-preview-desc').text(metaDesc.substring(0, 160));
        },

        loadTemplate: function (templateId) {
            if (!templateId) return;

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_get_template',
                id: templateId
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                if (response.success && response.data) {
                    var tpl = response.data;
                    if (tpl.default_settings) {
                        try {
                            var settings = typeof tpl.default_settings === 'string'
                                ? JSON.parse(tpl.default_settings)
                                : tpl.default_settings;

                            if (settings.tone) $('#ai-seo-tone').val(settings.tone);
                            if (settings.content_type) $('#ai-seo-content-type').val(settings.content_type);
                            if (settings.article_length) $('#ai-seo-article-length').val(settings.article_length);
                            if (settings.language) $('#ai-seo-language').val(settings.language);
                        } catch (e) { /* ignore */ }
                    }
                }
            });
        }
    };

    /**
     * Bulk Generator
     */
    var BulkGenerator = {
        currentBatchId: null,
        progressInterval: null,

        init: function () {
            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            var $dropzone = $('#ai-seo-csv-dropzone');

            $dropzone.on('click', function () {
                $('#ai-seo-csv-file').click();
            });

            $dropzone.on('dragover', function (e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            $dropzone.on('dragleave', function () {
                $(this).removeClass('dragover');
            });

            $dropzone.on('drop', function (e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                var files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    self.uploadFile(files[0]);
                }
            });

            $('#ai-seo-csv-file').on('change', function () {
                if (this.files.length) {
                    self.uploadFile(this.files[0]);
                }
            });

            $(document).on('click', '#ai-seo-pause-bulk', function () {
                self.pause();
            });

            $(document).on('click', '#ai-seo-resume-bulk', function () {
                self.resume();
            });

            $(document).on('click', '#ai-seo-cancel-bulk', function () {
                if (confirm(strings.confirm)) {
                    self.cancel();
                }
            });
        },

        uploadFile: function (file) {
            var self = this;
            var $dropzone = $('#ai-seo-csv-dropzone');
            showLoading($dropzone);

            var formData = new FormData();
            formData.append('action', 'ai_seo_writer_pro_upload_csv');
            formData.append(nonceName, nonceValue);
            formData.append('csv_file', file);

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    refreshNonce(response);
                    hideLoading($dropzone);

                    if (response.success) {
                        self.currentBatchId = response.data.batch_id;
                        showAlert(response.data.message || strings.success, 'success');
                        $('#ai-seo-batch-info').removeClass('ai-seo-writer-pro-hidden');
                        $('#ai-seo-batch-id').text(self.currentBatchId);
                        $('#ai-seo-total-items').text(response.data.total || 0);
                        self.startProgressTracking();
                    } else {
                        showAlert(response.data.message || strings.error, 'error');
                    }
                },
                error: function () {
                    hideLoading($dropzone);
                    showAlert(strings.error, 'error');
                }
            });
        },

        startProgressTracking: function () {
            var self = this;
            this.stopProgressTracking();

            this.progressInterval = setInterval(function () {
                self.getProgress();
            }, 3000);

            self.getProgress();
        },

        stopProgressTracking: function () {
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }
        },

        getProgress: function () {
            if (!this.currentBatchId) return;

            var self = this;
            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_get_bulk_progress',
                batch_id: this.currentBatchId
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                if (response.success) {
                    var p = response.data;
                    var total = p.total || 0;
                    var completed = p.completed || 0;
                    var failed = p.failed || 0;
                    var percent = total > 0 ? Math.round(((completed + failed) / total) * 100) : 0;

                    $('#ai-seo-progress-bar').css('width', percent + '%');
                    $('#ai-seo-progress-percent').text(percent + '%');
                    $('#ai-seo-completed-count').text(completed);
                    $('#ai-seo-failed-count').text(failed);
                    $('#ai-seo-pending-count').text(p.pending || 0);

                    if (percent >= 100) {
                        self.stopProgressTracking();
                        showAlert('Bulk generation complete!', 'success');
                    }
                }
            });
        },

        pause: function () {
            if (!this.currentBatchId) return;

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_pause_bulk',
                batch_id: this.currentBatchId
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                if (response.success) {
                    showAlert(strings.paused, 'success');
                    $('#ai-seo-pause-bulk').addClass('ai-seo-writer-pro-hidden');
                    $('#ai-seo-resume-bulk').removeClass('ai-seo-writer-pro-hidden');
                }
            });
        },

        resume: function () {
            if (!this.currentBatchId) return;

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_resume_bulk',
                batch_id: this.currentBatchId
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                if (response.success) {
                    showAlert(strings.resumed, 'success');
                    $('#ai-seo-resume-bulk').addClass('ai-seo-writer-pro-hidden');
                    $('#ai-seo-pause-bulk').removeClass('ai-seo-writer-pro-hidden');
                    this.startProgressTracking();
                }
            }.bind(this));
        },

        cancel: function () {
            if (!this.currentBatchId) return;

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_cancel_bulk',
                batch_id: this.currentBatchId
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                if (response.success) {
                    this.stopProgressTracking();
                    this.currentBatchId = null;
                    showAlert('Batch cancelled.', 'info');
                    $('#ai-seo-batch-info').addClass('ai-seo-writer-pro-hidden');
                }
            }.bind(this));
        }
    };

    /**
     * SEO Analyzer
     */
    var SEOAnalyzer = {
        init: function () {
            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            $(document).on('click', '#ai-seo-analyze-btn', function (e) {
                e.preventDefault();
                self.analyze();
            });
        },

        analyze: function () {
            var $btn = $('#ai-seo-analyze-btn');
            var $results = $('#ai-seo-analysis-results');

            setButtonLoading($btn, true);
            showLoading($results);

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_analyze_seo',
                content: $('#ai-seo-analyze-content').val(),
                keyword: $('#ai-seo-analyze-keyword').val(),
                title: $('#ai-seo-analyze-title').val()
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);
                hideLoading($results);

                if (response.success) {
                    self.renderResults(response.data);
                    $results.removeClass('ai-seo-writer-pro-hidden');
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                hideLoading($results);
                showAlert(strings.error, 'error');
            });
        },

        renderResults: function (data) {
            var score = data.score || 0;
            var circumference = 2 * Math.PI * 52;
            var offset = circumference - (score / 100) * circumference;
            var scoreColor = score >= 80 ? '#10b981' : score >= 60 ? '#f59e0b' : '#ef4444';

            var $circle = $('#ai-seo-score-circle');
            $circle.find('.score-fill')
                .attr('stroke', scoreColor)
                .attr('stroke-dasharray', circumference)
                .attr('stroke-dashoffset', offset);
            $circle.find('.score-value').text(Math.round(score));

            var $suggestions = $('#ai-seo-suggestions-list');
            $suggestions.empty();

            if (data.suggestions && data.suggestions.length) {
                data.suggestions.forEach(function (item) {
                    var iconType = item.passed ? 'success' : (item.severity === 'high' ? 'danger' : 'warning');
                    var icon = item.passed
                        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'
                        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="10"/></svg>';

                    $suggestions.append(
                        '<li class="ai-seo-writer-pro-suggestion-item">' +
                        '<div class="ai-seo-writer-pro-suggestion-icon ' + iconType + '">' + icon + '</div>' +
                        '<div class="ai-seo-writer-pro-suggestion-content">' +
                        '<h4>' + item.title + '</h4>' +
                        '<p>' + item.message + '</p>' +
                        '</div></li>'
                    );
                });
            }

            // Update detailed scores
            if (data.details) {
                $('#ai-seo-readability-score').text(Math.round(data.details.readability || 0));
                $('#ai-seo-keyword-score').text(Math.round(data.details.keyword_usage || 0));
                $('#ai-seo-structure-score').text(Math.round(data.details.structure || 0));
                $('#ai-seo-meta-score').text(Math.round(data.details.meta || 0));
            }
        }
    };

    /**
     * Image Generator
     */
    var ImageGenerator = {
        init: function () {
            this.bindEvents();
        },

        bindEvents: function () {
            var self = this;

            $(document).on('click', '#ai-seo-generate-image-btn', function (e) {
                e.preventDefault();
                self.generate();
            });

            $(document).on('click', '.ai-seo-upload-to-media', function (e) {
                e.preventDefault();
                self.uploadToMedia($(this));
            });
        },

        generate: function () {
            var $btn = $('#ai-seo-generate-image-btn');
            var $gallery = $('#ai-seo-image-gallery');

            setButtonLoading($btn, true);

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_generate_image',
                prompt: $('#ai-seo-image-prompt').val(),
                size: $('#ai-seo-image-size').val(),
                style: $('#ai-seo-image-style').val(),
                n: $('#ai-seo-image-count').val()
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);

                if (response.success && response.data.images) {
                    response.data.images.forEach(function (img) {
                        var $item = $(
                            '<div class="ai-seo-writer-pro-image-item">' +
                            '<img src="' + img.url + '" alt="Generated image" loading="lazy"/>' +
                            '<div class="ai-seo-writer-pro-image-item-overlay">' +
                            '<button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary ai-seo-writer-pro-btn-sm ai-seo-upload-to-media" data-url="' + img.url + '">' +
                            'Upload to Media' +
                            '</button>' +
                            '<a href="' + img.url + '" target="_blank" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary ai-seo-writer-pro-btn-sm">View</a>' +
                            '</div></div>'
                        );
                        $gallery.prepend($item);
                    });
                    showAlert(strings.success, 'success');
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                showAlert(strings.error, 'error');
            });
        },

        uploadToMedia: function ($btn) {
            var url = $btn.data('url');
            setButtonLoading($btn, true);

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_upload_to_media',
                image_url: url,
                title: $('#ai-seo-image-prompt').val().substring(0, 50) || 'AI Generated Image'
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert('Image uploaded to media library!', 'success');
                    $btn.replaceWith('<span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success">Uploaded</span>');
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                showAlert(strings.error, 'error');
            });
        }
    };

    /**
     * Templates management
     */
    var Templates = {
        init: function () {
            this.bindEvents();
        },

        bindEvents: function () {
            $(document).on('click', '#ai-seo-new-template-btn', function () {
                Templates.openModal();
            });

            $(document).on('click', '.ai-seo-edit-template', function (e) {
                e.preventDefault();
                Templates.openModal($(this).data('id'));
            });

            $(document).on('click', '.ai-seo-delete-template', function (e) {
                e.preventDefault();
                if (confirm(strings.confirm)) {
                    Templates.delete($(this).data('id'));
                }
            });

            $(document).on('submit', '#ai-seo-template-form', function (e) {
                e.preventDefault();
                Templates.save();
            });
        },

        openModal: function (id) {
            var title = id ? 'Edit Template' : 'Create Template';
            var content = $('#ai-seo-template-form-template').html();

            Modal.open(content, {
                title: title,
                size: 'lg',
                footer: '<button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-secondary" onclick="AI_SEO_Writer_Pro_Modal.close()">Cancel</button>' +
                    '<button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-primary" id="ai-seo-save-template-btn">Save Template</button>'
            });

            if (id) {
                $('#ai-seo-template-form #template-id').val(id);
                this.loadTemplateData(id);
            }

            $('#ai-seo-save-template-btn').on('click', function () {
                Templates.save();
            });
        },

        loadTemplateData: function (id) {
            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_get_template',
                id: id
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                if (response.success && response.data) {
                    var t = response.data;
                    $('#template-name').val(t.name);
                    $('#template-description').val(t.description);
                    $('#template-category').val(t.category);
                    $('#template-prompt').val(t.prompt_template);
                    $('#template-system-prompt').val(t.system_prompt);
                }
            });
        },

        save: function () {
            var $btn = $('#ai-seo-save-template-btn');
            setButtonLoading($btn, true);

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_save_template',
                id: $('#template-id').val(),
                name: $('#template-name').val(),
                description: $('#template-description').val(),
                category: $('#template-category').val(),
                prompt_template: $('#template-prompt').val(),
                system_prompt: $('#template-system-prompt').val()
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert(strings.success, 'success');
                    Modal.close();
                    location.reload();
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                showAlert(strings.error, 'error');
            });
        },

        delete: function (id) {
            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_delete_template',
                id: id
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                if (response.success) {
                    showAlert('Template deleted.', 'success');
                    location.reload();
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            });
        }
    };

    /**
     * History management
     */
    var History = {
        currentPage: 1,
        perPage: 20,

        init: function () {
            this.bindEvents();
            this.load();
        },

        bindEvents: function () {
            var self = this;

            $(document).on('change', '#ai-seo-history-filter-status', function () {
                self.currentPage = 1;
                self.load();
            });

            $(document).on('click', '.ai-seo-history-delete', function (e) {
                e.preventDefault();
                if (confirm(strings.confirm)) {
                    self.deleteItem($(this).data('id'));
                }
            });

            $(document).on('click', '.ai-seo-writer-pro-pagination-btn', function () {
                var page = $(this).data('page');
                if (page) {
                    self.currentPage = page;
                    self.load();
                }
            });
        },

        load: function () {
            var self = this;
            var $table = $('#ai-seo-history-table');
            showLoading($table.closest('.ai-seo-writer-pro-table-wrap'));

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_get_history',
                status: $('#ai-seo-history-filter-status').val(),
                limit: this.perPage,
                offset: (this.currentPage - 1) * this.perPage
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                hideLoading($table.closest('.ai-seo-writer-pro-table-wrap'));

                if (response.success && response.data.items) {
                    self.renderTable(response.data.items);
                }
            });
        },

        renderTable: function (items) {
            var $tbody = $('#ai-seo-history-table tbody');
            $tbody.empty();

            if (!items.length) {
                $tbody.append('<tr><td colspan="7" class="ai-seo-writer-pro-table-empty">' + strings.no_results + '</td></tr>');
                return;
            }

            items.forEach(function (item) {
                var statusBadge = '';
                switch (item.status) {
                    case 'published':
                        statusBadge = '<span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-success">Published</span>';
                        break;
                    case 'draft':
                        statusBadge = '<span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-gray">Draft</span>';
                        break;
                    case 'failed':
                        statusBadge = '<span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-danger">Failed</span>';
                        break;
                    default:
                        statusBadge = '<span class="ai-seo-writer-pro-badge ai-seo-writer-pro-badge-info">' + item.status + '</span>';
                }

                var seoScore = item.seo_score
                    ? '<span class="ai-seo-writer-pro-badge ' + (item.seo_score >= 80 ? 'ai-seo-writer-pro-badge-success' : item.seo_score >= 60 ? 'ai-seo-writer-pro-badge-warning' : 'ai-seo-writer-pro-badge-danger') + '">' + Math.round(item.seo_score) + '</span>'
                    : '-';

                var editLink = item.post_id
                    ? '<a href="' + ajaxUrl.replace('admin-ajax.php', 'post.php?post=' + item.post_id + '&action=edit') + '" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-ghost ai-seo-writer-pro-btn-sm" target="_blank">Edit</a>'
                    : '';

                $tbody.append(
                    '<tr>' +
                    '<td><strong>' + (item.title || '-') + '</strong><div class="ai-seo-writer-pro-text-small ai-seo-writer-pro-text-muted">' + (item.topic || '').substring(0, 60) + '</div></td>' +
                    '<td>' + item.primary_keyword + '</td>' +
                    '<td>' + item.ai_provider + '</td>' +
                    '<td>' + (item.word_count || 0) + '</td>' +
                    '<td>' + seoScore + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td>' + item.created_at + '</td>' +
                    '<td class="ai-seo-writer-pro-table-actions">' +
                    editLink +
                    '<button type="button" class="ai-seo-writer-pro-btn ai-seo-writer-pro-btn-ghost ai-seo-writer-pro-btn-sm ai-seo-history-delete" data-id="' + item.id + '">Delete</button>' +
                    '</td>' +
                    '</tr>'
                );
            });
        },

        deleteItem: function (id) {
            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_delete_history_item',
                id: id
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                if (response.success) {
                    showAlert('Item deleted.', 'success');
                    History.load();
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            });
        }
    };

    /**
     * API Settings
     */
    var APISettings = {
        init: function () {
            this.bindEvents();
        },

        bindEvents: function () {
            $(document).on('submit', '.ai-seo-provider-form', function (e) {
                e.preventDefault();
                APISettings.saveKey($(this));
            });

            $(document).on('click', '.ai-seo-test-connection', function (e) {
                e.preventDefault();
                APISettings.testConnection($(this));
            });
        },

        saveKey: function ($form) {
            var $btn = $form.find('button[type="submit"]');
            setButtonLoading($btn, true);

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_save_api_key',
                provider: $form.data('provider'),
                api_key: $form.find('.api-key-input').val(),
                model: $form.find('.model-select').val()
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert(response.data.message || strings.success, 'success');
                    if (response.data.masked_key) {
                        $form.find('.api-key-display').text(response.data.masked_key);
                    }
                    $form.find('.api-key-input').val('');
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                showAlert(strings.error, 'error');
            });
        },

        testConnection: function ($btn) {
            var provider = $btn.data('provider');
            setButtonLoading($btn, true);

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_test_api_connection',
                provider: provider
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert(strings.connection_ok, 'success');
                } else {
                    showAlert(response.data.message || strings.connection_failed, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                showAlert(strings.connection_failed, 'error');
            });
        }
    };

    /**
     * General Settings
     */
    var GeneralSettings = {
        init: function () {
            this.bindEvents();
        },

        bindEvents: function () {
            $(document).on('submit', '#ai-seo-general-settings-form', function (e) {
                e.preventDefault();
                GeneralSettings.save();
            });

            $(document).on('click', '#ai-seo-activate-license-btn', function (e) {
                e.preventDefault();
                GeneralSettings.activateLicense();
            });
        },

        save: function () {
            var $btn = $('#ai-seo-save-settings-btn');
            setButtonLoading($btn, true);

            var formData = $('#ai-seo-general-settings-form').serializeArray();
            var settings = {};

            formData.forEach(function (item) {
                if (item.name.startsWith('settings[')) {
                    var key = item.name.match(/settings\[(.+?)\]/);
                    if (key) {
                        if (item.name.includes('[]')) {
                            if (!settings[key[1]]) settings[key[1]] = [];
                            settings[key[1]].push(item.value);
                        } else {
                            settings[key[1]] = item.value;
                        }
                    }
                }
            });

            // Handle checkboxes
            $('#ai-seo-general-settings-form input[type="checkbox"]').each(function () {
                var name = $(this).attr('name');
                var key = name.match(/settings\[(.+?)\]/);
                if (key) {
                    settings[key[1]] = $(this).is(':checked') ? '1' : '0';
                }
            });

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_save_general_settings',
                settings: settings
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert(strings.success, 'success');
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                showAlert(strings.error, 'error');
            });
        },

        activateLicense: function () {
            var $btn = $('#ai-seo-activate-license-btn');
            setButtonLoading($btn, true);

            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_activate_license',
                license_key: $('#license-key').val(),
                api_url: $('#license-api-url').val()
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                setButtonLoading($btn, false);

                if (response.success) {
                    showAlert(response.data.message || 'License activated!', 'success');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    showAlert(response.data.message || strings.error, 'error');
                }
            }).fail(function () {
                setButtonLoading($btn, false);
                showAlert(strings.error, 'error');
            });
        }
    };

    /**
     * Dashboard stats
     */
    var Dashboard = {
        init: function () {
            this.loadStats();
        },

        loadStats: function () {
            var data = $.extend({}, getNonceData(), {
                action: 'ai_seo_writer_pro_get_dashboard_stats'
            });

            $.post(ajaxUrl, data, function (response) {
                refreshNonce(response);
                if (response.success) {
                    Dashboard.renderChart(response.data.monthly);
                }
            });
        },

        renderChart: function (monthly) {
            if (!monthly || !monthly.length) return;

            var $chart = $('#ai-seo-monthly-chart');
            if (!$chart.length) return;

            var maxValue = Math.max.apply(null, monthly.map(function (d) { return parseInt(d.articles_generated) || 0; })) || 1;

            $chart.empty();
            monthly.forEach(function (day) {
                var height = maxValue > 0 ? ((parseInt(day.articles_generated) || 0) / maxValue) * 100 : 0;
                var date = new Date(day.stat_date);
                var label = date.getDate();
                $chart.append(
                    '<div class="ai-seo-writer-pro-chart-bar" style="height:' + height + '%" data-value="' + (day.articles_generated || 0) + '" title="' + day.stat_date + '"></div>'
                );
            });
        }
    };

    /**
     * Copy to clipboard
     */
    function initCopyToClipboard() {
        $(document).on('click', '[data-copy]', function (e) {
            e.preventDefault();
            var text = $(this).data('copy');
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();

            var $btn = $(this);
            var original = $btn.text();
            $btn.text(strings.copied);
            setTimeout(function () {
                $btn.text(original);
            }, 1500);
        });
    }

    /**
     * Table sorting (simple client-side)
     */
    function initTableSorting() {
        $(document).on('click', '.ai-seo-writer-pro-table th[data-sort]', function () {
            var $th = $(this);
            var $table = $th.closest('.ai-seo-writer-pro-table');
            var column = $th.data('sort');
            var direction = $th.data('direction') === 'asc' ? 'desc' : 'asc';
            $th.data('direction', direction);

            var $tbody = $table.find('tbody');
            var rows = $tbody.find('tr').toArray();

            rows.sort(function (a, b) {
                var aVal = $(a).find('td').eq($th.index()).text().trim();
                var bVal = $(b).find('td').eq($th.index()).text().trim();

                if (!isNaN(parseFloat(aVal)) && !isNaN(parseFloat(bVal))) {
                    aVal = parseFloat(aVal);
                    bVal = parseFloat(bVal);
                }

                if (aVal < bVal) return direction === 'asc' ? -1 : 1;
                if (aVal > bVal) return direction === 'asc' ? 1 : -1;
                return 0;
            });

            $tbody.empty().append(rows);
        });
    }

    /**
     * Initialize everything on document ready
     */
    $(document).ready(function () {
        initTabs();
        initCopyToClipboard();
        initTableSorting();

        ArticleGenerator.init();
        BulkGenerator.init();
        SEOAnalyzer.init();
        ImageGenerator.init();
        Templates.init();
        History.init();
        APISettings.init();
        GeneralSettings.init();
        Dashboard.init();

        // Expose Modal globally for inline onclick handlers
        window.AI_SEO_Writer_Pro_Modal = Modal;
    });

})(jQuery);

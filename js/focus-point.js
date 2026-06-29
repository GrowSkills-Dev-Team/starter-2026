/**
 * GrowSkills Focus Point
 * Adds a focus point button to the WordPress Media Library attachment details.
 */
(function ($) {
    'use strict';

    if (typeof wp === 'undefined' || !wp.media) return;

    let currentAttachmentId = null;
    let $modal = null;

    function buildModal() {
        $modal = $(`
            <div class="gs-fp-overlay" id="gs-fp-overlay" role="dialog" aria-modal="true" aria-label="Focus punt instellen">
                <div class="gs-fp-modal">
                    <div class="gs-fp-modal__header">
                        <span>${gsFocusPoint.modalTitle}</span>
                        <button class="gs-fp-close" aria-label="${gsFocusPoint.close}">&times;</button>
                    </div>
                    <div class="gs-fp-modal__image-wrap">
                        <img class="gs-fp-modal__img" src="" alt="" draggable="false" />
                        <div class="gs-fp-crosshair" aria-hidden="true">
                            <div class="gs-fp-crosshair__ring"></div>
                            <div class="gs-fp-crosshair__dot"></div>
                        </div>
                    </div>
                    <div class="gs-fp-modal__footer">
                        <button class="button gs-fp-reset">${gsFocusPoint.reset}</button>
                        <button class="button button-primary gs-fp-save">${gsFocusPoint.save}</button>
                    </div>
                </div>
            </div>
        `);
        $('body').append($modal);

        $modal.on('click', '.gs-fp-close', function () {
            closeModal();
        });
        $modal.on('click', function (e) {
            if ($(e.target).hasClass('gs-fp-overlay')) closeModal();
        });

        $(document).on('keydown.gsfp', function (e) {
            if (e.key === 'Escape') closeModal();
        });

        $modal.on('click', '.gs-fp-modal__image-wrap', function (e) {
            const $wrap = $(this);
            const offset = $wrap.offset();
            const x = ((e.pageX - offset.left) / $wrap.width()) * 100;
            const y = ((e.pageY - offset.top) / $wrap.height()) * 100;
            moveCrosshair(x, y);
        });

        $modal.on('click', '.gs-fp-reset', function () {
            moveCrosshair(50, 50);
        });

        $modal.on('click', '.gs-fp-save', function () {
            saveFocusPoint();
        });
    }

    function openModal(attachmentId, imageUrl) {
        if (!$modal) buildModal();

        currentAttachmentId = attachmentId;
        $modal.find('.gs-fp-modal__img').attr('src', imageUrl);
        $modal.addClass('is-visible');

        $.post(gsFocusPoint.ajaxurl, {
            action: 'gs_get_focus_point',
            nonce: gsFocusPoint.nonce,
            attachment_id: attachmentId,
        }, function (res) {
            if (res.success) {
                moveCrosshair(res.data.x, res.data.y);
            }
        });
    }

    function closeModal() {
        if ($modal) $modal.removeClass('is-visible');
        currentAttachmentId = null;
    }

    function moveCrosshair(x, y) {
        x = Math.max(0, Math.min(100, x));
        y = Math.max(0, Math.min(100, y));
        $modal.find('.gs-fp-crosshair').css({ left: x + '%', top: y + '%' });
        $modal.data('fp-x', x).data('fp-y', y);
    }

    function saveFocusPoint() {
        const $btn = $modal.find('.gs-fp-save');
        const x = $modal.data('fp-x') !== undefined ? $modal.data('fp-x') : 50;
        const y = $modal.data('fp-y') !== undefined ? $modal.data('fp-y') : 50;

        $btn.text(gsFocusPoint.saving).prop('disabled', true);

        $.post(gsFocusPoint.ajaxurl, {
            action: 'gs_save_focus_point',
            nonce: gsFocusPoint.nonce,
            attachment_id: currentAttachmentId,
            x: x,
            y: y,
        }, function (res) {
            $btn.text(gsFocusPoint.saved);
            setTimeout(function () {
                $btn.text(gsFocusPoint.save).prop('disabled', false);
                closeModal();
            }, 800);
        });
    }

    function injectButtonIntoSidebar($sidebar, attachmentId, imageUrl) {
        if (!attachmentId || !imageUrl) return;
        if ($sidebar.find('.gs-fp-btn').length) return;

        const $btn = $(`
            <span class="setting gs-fp-field">
                <label class="name">${gsFocusPoint.label}</label>
                <button type="button" class="button gs-fp-btn" data-id="${attachmentId}" data-url="${imageUrl}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
                    ${gsFocusPoint.btnText}
                </button>
            </span>
        `);

        // Try to insert before the File URL row, otherwise append
        const $anchor = $sidebar.find('.setting[data-setting="url"], .attachment-details-copy-link, .copy-to-clipboard-container');
        if ($anchor.length) {
            $anchor.first().before($btn);
        } else {
            $sidebar.find('.attachment-info, .details, .settings').append($btn);
        }
    }

    // ── Via wp.media Backbone model (standalone media modal) ─────────────────
    function injectFromModel(model) {
        if (!model || typeof model.get !== 'function') return;

        const id   = model.get('id');
        const url  = model.get('url');
        const type = model.get('type');

        if (type !== 'image' || !id || !url) return;

        setTimeout(function () {
            const $sidebar = $('.media-sidebar, .attachment-details');
            if ($sidebar.length) {
                injectButtonIntoSidebar($sidebar, id, url);
            }
        }, 200);
    }

    // ── MutationObserver: catches ACF modal + any other media sidebar ─────────
    function startObserver() {
        const observer = new MutationObserver(function () {
            // Look for any visible attachment details sidebar
            const $sidebar = $('.attachment-details:visible, .media-sidebar:visible');
            if (!$sidebar.length) return;
            if ($sidebar.find('.gs-fp-btn').length) return; // already done

            // Try to get attachment ID from the sidebar itself or from wp.media
            let attachmentId = null;
            let imageUrl     = null;

            // Method 1: from wp.media selection
            try {
                if (wp.media.frame) {
                    const state = wp.media.frame.state();
                    if (state) {
                        const selection = state.get('selection');
                        if (selection && selection.single && selection.single()) {
                            const model = selection.single();
                            if (model && model.get('type') === 'image') {
                                attachmentId = model.get('id');
                                imageUrl     = model.get('url');
                            }
                        }
                    }
                }
            } catch (e) {}

            // Method 2: from data attribute on sidebar
            if (!attachmentId) {
                const dataId = $sidebar.attr('data-id') || $sidebar.find('[data-id]').first().attr('data-id');
                if (dataId) attachmentId = parseInt(dataId, 10);
            }

            // Method 3: from file URL field
            if (!imageUrl) {
                const $urlInput = $sidebar.find('input[value*="/wp-content/uploads/"]');
                if ($urlInput.length) {
                    imageUrl = $urlInput.val();
                }
            }

            // Method 4: from the thumbnail img src
            if (!imageUrl) {
                const $thumb = $sidebar.find('.thumbnail img, .attachment-media-view img');
                if ($thumb.length) {
                    imageUrl = $thumb.attr('src');
                }
            }

            if (attachmentId && imageUrl) {
                injectButtonIntoSidebar($sidebar, attachmentId, imageUrl);
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    // ── Init ─────────────────────────────────────────────────────────────────
    $(document).ready(function () {

        // Delegated click — works regardless of when button was injected
        $(document).on('click', '.gs-fp-btn', function () {
            openModal($(this).data('id'), $(this).data('url'));
        });

        // Hook into Backbone view ready (standalone modal)
        try {
            const _origRender = wp.media.view.Attachment.Details.prototype.render;
            wp.media.view.Attachment.Details.prototype.render = function () {
                const result = _origRender.apply(this, arguments);
                injectFromModel(this.model);
                return result;
            };
        } catch (e) {}

        // Start MutationObserver for ACF / Gutenberg media modals
        startObserver();
    });

})(jQuery);
/**
 * My Custom Widget - Editor Script
 * 
 * Handles popup form integration in Elementor editor.
 * Intercepts widget clicks in sidebar and displays a product creation popup.
 * 
 * @package My_Elementor_Widget
 * @version 1.0.0
 */

(function ($, window) {
    'use strict';

    /**
     * Product Creation Module
     * 
     * Manages product creation popup and widget interaction in Elementor editor.
     */
    var ProductCreationModule = {
        // Configuration
        config: {
            widgetName: 'My Custom Widget',
            popupId: 'custom-widget-popup'
        },

        // State
        state: {
            isPopupShowing: false,
            allowNormalDrag: false
        },

        /**
         * Initialize the module
         */
        init: function () {
            this.bindEvents();
        },

        /**
         * Bind event listeners
         */
        bindEvents: function () {
            var self = this;

            // Intercept widget clicks
            $(document).on('click mousedown', '.elementor-element', function (e) {
                self.handleWidgetClick(e, $(this));
            });

            // Reset drag flag when widget panel opens
            elementor.hooks.addAction(
                'panel/open_editor/widget/my_custom_widget',
                function (panel, model, view) {
                    self.state.allowNormalDrag = false;
                }
            );
        },

        /**
         * Handle click on widget element
         * 
         * @param {Event} e Click event
         * @param {jQuery} $widget Widget element
         */
        handleWidgetClick: function (e, $widget) {
            var widgetTitle = this.getWidgetTitle($widget);

            if (widgetTitle.indexOf(this.config.widgetName) === -1) {
                return;
            }

            // Allow normal drag if flag is set
            if (this.state.allowNormalDrag) {
                return;
            }

            // Prevent default and show popup
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            this.showProductPopup();

            return false;
        },

        /**
         * Get widget title from element
         * 
         * @param {jQuery} $widget Widget element
         * @return {string} Widget title
         */
        getWidgetTitle: function ($widget) {
            return $widget.find('.title').text() ||
                $widget.find('.elementor-element-title').text() ||
                $widget.text();
        },

        /**
         * Show product popup form
         * 
         * Creates and displays a modal popup for WooCommerce product creation.
         */
        showProductPopup: function () {
            var self = this;

            // Prevent multiple popups
            if (this.state.isPopupShowing) {
                return;
            }
            this.state.isPopupShowing = true;

            // Create popup HTML structure (styles in editor.css)
            var popupHTML = `
                <div id="custom-widget-popup">
                    <div class="custom-popup-content">
                        <h2>Tạo Sản Phẩm WooCommerce</h2>
                        <form id="product-form">
                            <div class="form-group">
                                <label for="popup-product-name">Tên Sản Phẩm: <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    id="popup-product-name" 
                                    name="product_name"
                                    required 
                                    placeholder="VD: iPhone 15 Pro Max"
                                    autocomplete="off"
                                />
                            </div>
                            <div class="form-group">
                                <label for="popup-product-price">Giá (VNĐ): <span class="required">*</span></label>
                                <input 
                                    type="number" 
                                    id="popup-product-price" 
                                    name="product_price"
                                    step="0.01" 
                                    min="0"
                                    required 
                                    placeholder="VD: 29990000"
                                    autocomplete="off"
                                />
                            </div>
                            <div class="form-group">
                                <label for="popup-product-description">Mô Tả:</label>
                                <textarea 
                                    id="popup-product-description" 
                                    name="product_description"
                                    rows="3"
                                    placeholder="Nhập mô tả sản phẩm (tùy chọn)"
                                    autocomplete="off"
                                ></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="submit-btn">
                                    <span class="btn-text">Tạo Sản Phẩm</span>
                                    <span class="btn-loading" style="display:none;">Đang tạo...</span>
                                </button>
                                <button type="button" class="cancel-btn">Hủy</button>
                            </div>
                            <div class="form-message" style="display:none;"></div>
                        </form>
                    </div>
                </div>
            `;

            // Add popup to body
            $('body').append(popupHTML);

            // Initialize popup UI
            var popup = new PopupUI(self);
            popup.init();
        }
    };

    /**
     * Popup UI Handler
     * 
     * Manages popup form UI and interactions.
     */
    var PopupUI = function (module) {
        this.module = module;
        this.$popup = $('#custom-widget-popup');
        this.$form = $('#product-form');
        this.$elements = {
            nameInput: $('#popup-product-name'),
            priceInput: $('#popup-product-price'),
            descInput: $('#popup-product-description'),
            submitBtn: this.$form.find('.submit-btn'),
            btnText: this.$form.find('.btn-text'),
            btnLoading: this.$form.find('.btn-loading'),
            cancelBtn: $('.cancel-btn'),
            message: this.$form.find('.form-message')
        };
    };

    PopupUI.prototype = {
        /**
         * Initialize popup
         */
        init: function () {
            this.bindEvents();
            this.focusFirstInput();
        },

        /**
         * Bind popup events
         */
        bindEvents: function () {
            var self = this;

            // Form submission
            this.$form.on('submit', function (e) {
                e.preventDefault();
                self.handleFormSubmit();
            });

            // Cancel button
            this.$elements.cancelBtn.on('click', function () {
                self.closePopup();
            });

            // Click outside to close
            this.$popup.on('click', function (e) {
                if (e.target.id === 'custom-widget-popup') {
                    self.closePopup();
                }
            });

            // Stop propagation on content clicks
            $('.custom-popup-content').on('click', function (e) {
                e.stopPropagation();
            });

            // Escape key to close
            $(document).on('keydown.popup', function (e) {
                if (e.key === 'Escape') {
                    self.closePopup();
                    $(document).off('keydown.popup');
                }
            });
        },

        /**
         * Focus first input field
         */
        focusFirstInput: function () {
            var self = this;
            setTimeout(function () {
                self.$elements.nameInput.focus();
            }, 100);
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function () {
            var formData = this.getFormData();

            // Validate
            if (!this.validateFormData(formData)) {
                this.showMessage('Vui lòng nhập đầy đủ thông tin hợp lệ!', 'error');
                return;
            }

            // Submit
            this.disableForm();
            this.submitProduct(formData);
        },

        /**
         * Get form data
         * 
         * @return {Object} Form data
         */
        getFormData: function () {
            return {
                name: this.$elements.nameInput.val().trim(),
                price: parseFloat(this.$elements.priceInput.val()),
                description: this.$elements.descInput.val().trim()
            };
        },

        /**
         * Validate form data
         * 
         * @param {Object} data Form data
         * @return {boolean} Is valid
         */
        validateFormData: function (data) {
            return data.name && data.price && data.price > 0;
        },

        /**
         * Submit product creation request
         * 
         * @param {Object} formData Form data
         */
        submitProduct: function (formData) {
            var self = this;

            $.ajax({
                url: myElementorWidget.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'my_elementor_widget_create_product',
                    nonce: myElementorWidget.nonce,
                    product_name: formData.name,
                    product_price: formData.price,
                    product_description: formData.description
                },
                success: function (response) {
                    self.handleSubmitSuccess(response);
                },
                error: function (xhr, status, error) {
                    self.handleSubmitError(error);
                }
            });

            // use backbone.js
            // const Product = Backbone.Model.extend({
            //     urlRoot: 'http://localhost:8080/wp-json/wc/v3/products',
            //     defaults: {
            //         name: '',
            //         type: 'simple',
            //         regular_price: '',
            //         description: ''
            //     },
            //     sync: function (method, model, options) {
            //         options.beforeSend = function (xhr) {
            //             xhr.setRequestHeader('Authorization', 'Basic ' + btoa('ck_6fd743e37397ca8c9c296c6259bf6cfca43c3e0d:cs_d9d6d6f6be283d77ef163869dabcs_0e06989c9eb8b2f6583004de12de32e58724a27f78923e0b9ae52'));
            //         };
            //         return Backbone.sync(method, model, options);
            //     }
            // });

            // const newProduct = new Product({
            //     name: formData.name,
            //     regular_price: String(formData.price),
            //     description: formData.description
            // });

            // newProduct.save(null, {
            //     success: function (model, response) {
            //         self.handleSubmitSuccess(response);
            //     },
            //     error: function (model, response) {
            //         self.handleSubmitError(error);
            //     }
            // });
        },

        /**
         * Handle successful submission
         * 
         * @param {Object} response Server response
         */
        handleSubmitSuccess: function (response) {
            if (response.success) {
                this.$form.hide();
                this.showSuccessOptions(response.data);
            } else {
                this.showMessage(response.data.message || 'Có lỗi xảy ra!', 'error');
                this.enableForm();
            }
        },

        /**
         * Handle submission error
         * 
         * @param {string} error Error message
         */
        handleSubmitError: function (error) {
            this.showMessage('Lỗi kết nối: ' + error, 'error');
            this.enableForm();
        },

        /**
         * Disable form during submission
         */
        disableForm: function () {
            var elements = this.$elements;
            [elements.submitBtn, elements.nameInput, elements.priceInput, elements.descInput].forEach(function ($el) {
                $el.prop('disabled', true);
            });
            elements.btnText.hide();
            elements.btnLoading.show();
        },

        /**
         * Enable form after submission
         */
        enableForm: function () {
            var elements = this.$elements;
            [elements.submitBtn, elements.nameInput, elements.priceInput, elements.descInput].forEach(function ($el) {
                $el.prop('disabled', false);
            });
            elements.btnText.show();
            elements.btnLoading.hide();
        },

        /**
         * Show success options after product creation
         * 
         * @param {Object} data Product data from server
         */
        showSuccessOptions: function (data) {
            var self = this;
            var successHTML = `
                    <div class="success-options">
                        <div class="success-icon">✓</div>
                        <h3>Sản Phẩm Đã Được Tạo!</h3>
                        <div class="product-preview">
                            <strong>${data.product_name}</strong>
                            <span class="preview-price">${data.product_price} VNĐ</span>
                        </div>
                        <div class="success-actions">
                            <button type="button" class="close-later-btn">Đóng & Kéo Thả Sau</button>
                        </div>
                        <a href="${data.edit_url}" target="_blank" class="edit-product-link">
                            <span>Chỉnh sửa sản phẩm</span>
                        </a>
                    </div>
                `;

            $('.custom-popup-content').append(successHTML);

            // Handle "Close" - allow drag later
            $('.close-later-btn').on('click', function () {
                self.closePopup();
                self.module.state.allowNormalDrag = true;
                self.showEditorNotification();
            });
        },

        /**
         * Close popup and cleanup
         */
        closePopup: function () {
            this.$popup.remove();
            this.module.state.isPopupShowing = false;
        },

        /**
         * Show message in popup
         * 
         * @param {string} message Message text
         * @param {string} type Message type ('success' or 'error')
         */
        showMessage: function (message, type) {
            var $message = this.$elements.message;

            $message
                .removeClass('success error')
                .addClass(type)
                .html(message)
                .fadeIn();

            // Auto-hide after 5 seconds
            setTimeout(function () {
                $message.fadeOut();
            }, 5000);
        },

        /**
         * Show notification in Elementor editor
         */
        showEditorNotification: function () {
            if (typeof elementorCommon !== 'undefined' && elementorCommon.dialogsManager) {
                elementorCommon.dialogsManager.createWidget('confirm', {
                    headerMessage: 'Sản Phẩm Đã Tạo',
                    message: 'Bạn có thể kéo widget "My Custom Widget" từ sidebar để hiển thị sản phẩm vừa tạo.',
                    position: {
                        my: 'center center',
                        at: 'center center'
                    },
                    strings: {
                        confirm: 'OK'
                    },
                    hide: {
                        onButtonClick: true
                    },
                    onConfirm: function () {
                        this.hide();
                    }
                }).show();
            }
        }
    };

    // Initialize when Elementor is ready
    $(window).on('elementor:init', function () {
        ProductCreationModule.init();
    });

})(jQuery, window);


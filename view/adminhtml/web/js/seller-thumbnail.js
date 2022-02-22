/**
 * @api
 */
define([
    'Magento_Ui/js/grid/columns/thumbnail',
    'jquery',
    'mage/template',
    'text!Marketplacer_Seller/template/seller-preview.html',
    'underscore',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function (Column, $, mageTemplate, thumbnailPreviewTemplate, _) {
    'use strict';

    return Column.extend({
        /**
         * Build preview.
         *
         * @param {Object} row
         */
        preview: function (row) {
            var modalHtml = mageTemplate(
                    thumbnailPreviewTemplate,
                    {
                        src: this.getOrigSrc(row), alt: this.getAlt(row), link: this.getLink(row)
                    }
                ),
                previewPopup = $('<div></div>').html(modalHtml);

            previewPopup.modal({
                title: this.getAlt(row),
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []
            }).trigger('openModal');
        },
    });
});

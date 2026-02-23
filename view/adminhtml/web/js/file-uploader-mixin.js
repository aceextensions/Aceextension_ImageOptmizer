define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    return function (FileUploader) {
        return FileUploader.extend({
            /**
             * Extend allowed extensions to include WebP, SVG, and AVIF
             */
            initialize: function () {
                this._super();

                if (this.allowedExtensions) {
                    var additional = ['webp', 'svg', 'avif'];
                    var current = this.allowedExtensions.split(' ');

                    additional.forEach(function (ext) {
                        if (current.indexOf(ext) === -1) {
                            current.push(ext);
                        }
                    });

                    this.allowedExtensions = current.join(' ');
                }

                return this;
            }
        });
    };
});

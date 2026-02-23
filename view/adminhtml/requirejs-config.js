var config = {
    map: {
        '*': {
            'mediaUploader': 'Aceextension_ImageOptmizer/js/media-uploader',
            'Magento_Backend/js/media-uploader': 'Aceextension_ImageOptmizer/js/media-uploader'
        }
    },
    config: {
        mixins: {
            'Magento_Ui/js/form/element/file-uploader': {
                'Aceextension_ImageOptmizer/js/file-uploader-mixin': true
            }
        }
    }
};

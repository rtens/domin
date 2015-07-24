$(function () {
    var updateSize = function (parent, data) {
        var img = parent.find('.image-placeholder');
        var width = parent.find('.image-width');
        var height = parent.find('.image-height');

        width.val(Math.round(data.width * factor(img) * zoomLevel(img)));
        height.val(Math.round(data.height * factor(img) * zoomLevel(img)));
    };

    var zoomLevel = function (img) {
        var container = img.cropper('getContainerData');
        var image = img.cropper('getImageData');
        if ((container.height / container.width) > (image.height / image.width)) {
            return image.width / container.width;
        } else {
            return image.height / container.height;
        }
    };

    var factor = function(img) {
        return parseFloat(img.data('factor'));
    };

    $('.image-controls .btn').each(function () {
        $(this).init.prototype.setOption = function (option, value) {
            $(this).parents('.image-cropper').find('.image-placeholder').cropper(option, value);
        };

        $(this).init.prototype.changeFactor = function (byFactor) {
            var parent = $(this).parents('.image-cropper');
            var img = parent.find('.image-placeholder');

            img.data('factor', factor(img) * byFactor);
            updateSize(parent, img.cropper('getData'));
        };
    });

    $('.image-cropper .image-input').change(function (e) {
        var target = $(e.target);
        var file = target.prop('files')[0];
        var parent = target.parents('.image-cropper');
        var img = parent.find('.image-placeholder');
        var width = parent.find('.image-width');
        var height = parent.find('.image-height');

        img.data('factor', 1.0);

        var set = function (prop) {
            return function (e) {
                var data = img.cropper('getData');
                data[prop] = parseInt($(e.target).val()) / zoomLevel(img) / factor(img);
                img.cropper('setData', data);
            };
        };

        width.change(set('width'));
        height.change(set('height'));

        parent.find('.image-container').show();

        var options = $cropperOptions$;
        options.crop = function (data) {
            updateSize(parent, data);
        };

        img.cropper(options);

        img.cropper('replace', URL.createObjectURL(file));

        var resize = function (callback) {
            var cropped = document.createElement('img');
            $(cropped).load(function () {
                var canvas = document.createElement('canvas');
                canvas.width = parseInt(width.val());
                canvas.height = parseInt(height.val());
                canvas.getContext('2d').drawImage(cropped, 0, 0, canvas.width, canvas.height);

                parent.find('.image-data').val(file.name + ';;' + canvas.toDataURL(file.type));

                callback();
            });
            $(cropped).attr('src', img.cropper('getCroppedCanvas').toDataURL(file.type));
        };

        var resized = false;
        img.parents('form').submit(function (e) {
            if (!resized) {
                resize(function () {
                    console.log(e.target);
                    resized = true;
                    $(e.target).submit();
                });
                return false;
            }
        });
    });
});
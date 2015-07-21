$(function () {
    $('.image-cropper .image-input').change(function (e) {
        var target = $(e.target);
        var file = target.prop('files')[0];
        var parent = target.parents('.image-cropper');
        var img = parent.find('.image-placeholder');
        var width = parent.find('.image-width');
        var height = parent.find('.image-height');

        var zoomLevel = function () {
            var container = img.cropper('getContainerData');
            var image = img.cropper('getImageData');
            if ((container.height / container.width) > (image.height / image.width)) {
                return image.width / container.width;
            } else {
                return image.height / container.height;
            }
        };

        var set = function (prop) {
            return function (e) {
                var data = img.cropper('getData');
                data[prop] = parseInt($(e.target).val()) / zoomLevel();
                img.cropper('setData', data);
            };
        };

        width.change(set('width'));
        height.change(set('height'));

        parent.find('.image-container').show();

        img.cropper({
            autoCropArea: 1,
            minContainerHeight: 400,
            crop: function (data) {
                width.val(Math.round(data.width * zoomLevel()));
                height.val(Math.round(data.height * zoomLevel()));
            }
        });
        img.cropper('replace', URL.createObjectURL(file));
        img.parents('form').submit(function () {
            var cropped = document.createElement('img');
            $(cropped).attr('src', img.cropper('getCroppedCanvas').toDataURL(file.type));

            var canvas = document.createElement('canvas');
            canvas.width = parseInt(width.val());
            canvas.height = parseInt(height.val());
            canvas.getContext('2d').drawImage(cropped, 0, 0, canvas.width, canvas.height);

            parent.find('.image-data').val(file.name + ';;' + canvas.toDataURL(file.type));
        });
    });
});
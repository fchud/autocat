var carAddLib = carAddLib || (function ($) {
    var _args = {};
    var _errs;

    return {
        init: function (args, errs) {
            _args = args;
            _errs = errs;

            var self = this;
            $(document).ready(function () {

                $('#random').click(function () {
                    self.test(this);
                });

                $('input[type=file]').change(function () {
                    self.validateFiles(this);
                });

                $('form').submit(function (event) {
                    self.validateForm(event);
                });

                self.pointErrors();
            });
        },
        pointErrors: function () {
            var self = this;
            $.each(_errs, function (key, val) {
                self.setInfoError(key, val);
            });
        },
        getAllIds: function () {
            var ids = $.map($('form').find(':input'), function (i) {
                if (!i.id) {
                    return null;
                }
                return i.id;
            });
            return ids;
        },
        getInfoById: function (id) {
            var infoId = $('#' + id + 'Info');
            return (infoId.length > 0) ? infoId : undefined;
        },
        getInfoTip: function (info) {
            return info.attr('data-tip') ? info.attr('data-tip') : '*';
        },
        setInfoError: function (id, message) {
            var info = this.getInfoById(id);
            if (info === undefined) {
                alert(id + ': ' + message);
                return;
            }

            info.removeClass('hint--info');
            info.addClass('hint--error');
            info.attr('data-hint', message);
            info.html('?');
        },
        resetInfo: function (id) {
            var info = this.getInfoById(id);
            if (info === undefined) {
                return;
            }

            var tip = this.getInfoTip(info);
            if (tip === '*') {
                info.attr('data-hint', 'required');
                info.html(tip);
            } else if (tip === '?') {
                info.removeAttr('data-hint');
                info.html('&nbsp;');
            }
            info.removeClass('hint--error');
            info.addClass('hint--info');
        },
        resetInfoAll: function () {
            var self = this;
            $.each(this.getAllIds(), function () {
                self.resetInfo(this);
            });
        },
        validateFiles: function (input) {
            var postMaxSize = _args[0];
            var maxFileSize = _args[1];
            var maxFiles = _args[2];

            var id = input.id;
            var postSize = 0;

            var error = '';

            if (input.files.length > maxFiles) {
                error = 'max files: ' + maxFiles;
            }

            var files = '';
            var count = 0;
            for (var i = 0; i < input.files.length; i++) {
                var fileSize = input.files[i].size;
                if (fileSize > maxFileSize) {
                    files += (count > 0) ? ', ' : '';
                    files += input.files[i].name;
                    count++;
                }
                postSize += fileSize;
            }

            if (count > 0) {
                error += (error.length > 0) ? '; ' : '';
                error += '"' + files + '" exceed';
                error += (count > 1) ? '' : 's';
                error += ' max size: ' + maxFileSize + 'b';
            }

            if (postSize > postMaxSize) {
                error += (error.length > 0) ? '; ' : '';
                error += 'POST max size: ' + postMaxSize + 'b';
            }

            if (error !== '') {
                _errs[id] = error;
                this.setInfoError(id, error);
            } else {
                delete _errs[id];
                this.resetInfo(id);
            }
        },
        validateAll: function () {
            var ids = this.getAllIds();

            $.each(ids, function () {
                var hint = $('#' + this + 'Info').attr('data-hint');
                if (hint === 'required') {
                    if (!$('#' + this).val()) {
                        _errs[this] = hint;
                    } else {
                        delete _errs[this];
                    }
                }
            });
        },
        validateForm: function (event) {
            this.resetInfoAll();
            this.validateAll();
            this.pointErrors();

            if (Object.keys(_errs).length > 0) {
                event.preventDefault();
            }
        },
        randInt: function (min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        },
        test: function () {

            var rId = this.randInt(1000, 9999);

            $('#brand').val('brand #' + rId);
            $('#model').val('model #' + rId);
            $('#price').val(rId);
            $('#body').val('body #' + rId);
            $('#description').val('description #' + rId);

            $('option').each(function () {
                if (Math.round(Math.random())) {
                    $(this).attr('selected', 'selected');
                } else {
                    $(this).removeAttr('selected');
                }
            });
        }
    };
})(jQuery);

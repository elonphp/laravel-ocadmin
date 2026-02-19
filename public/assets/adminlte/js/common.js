function getURLVar(key) {
    var value = [];

    var query = String(document.location).split('?');

    if (query[1]) {
        var part = query[1].split('&');

        for (i = 0; i < part.length; i++) {
            var data = part[i].split('=');

            if (data[0] && data[1]) {
                value[data[0]] = data[1];
            }
        }

        if (value[key]) {
            return value[key];
        } else {
            return '';
        }
    }
}

$(document).ready(function () {
    // Tooltip
    var oc_tooltip = function () {
        tooltip = bootstrap.Tooltip.getOrCreateInstance(this);

        if (!tooltip) {
            tooltip.show();
        }
    }

    $(document).on('mouseenter', '[data-bs-toggle=\'tooltip\']', oc_tooltip);

    $(document).on('click', 'button', function () {
        $('.tooltip').remove();
    });

    $(document).on('click', '[data-bs-toggle=\'pagination\'] a', function (e) {
        e.preventDefault();

        $(this.target).load(this.href);
    });

    // Alert Fade
    $('#alert').observe(function () {
        window.setTimeout(function () {
            $('#alert .alert-dismissible').fadeTo(3000, 0, function () {
                $(this).remove();
            });
        }, 3000);
    });

    // Button
    +function ($) {
        $.fn.button = function (state) {
            return this.each(function () {
                var element = this;

                if (state == 'loading') {
                    this.html = $(element).html();
                    this.state = $(element).prop('disabled');

                    $(element).prop('disabled', true).width($(element).width()).html('<i class="bi bi-arrow-repeat spinning text-light"></i>');
                }

                if (state == 'reset') {
                    $(element).prop('disabled', this.state).width('').html(this.html);
                }
            });
        }
    }(jQuery);
});

function decodeHTMLEntities(html) {
    var d = document.createElement('div');

    d.innerHTML = html;

    return d.textContent;
}

// Observe
+function ($) {
    $.fn.observe = function (callback) {
        observer = new MutationObserver(callback);

        observer.observe($(this)[0], {
            characterData: false,
            childList: true,
            attributes: false
        });
    };
}(jQuery);

// Chain ajax calls.
class Chain {
    constructor() {
        this.start = false;
        this.data = [];
    }

    attach(call) {
        this.data.push(call);

        if (!this.start) {
            this.execute();
        }
    }

    execute() {
        if (this.data.length) {
            this.start = true;

            var call = this.data.shift();

            var jqxhr = call();

            jqxhr.done(function () {
                chain.execute();
            });
        } else {
            this.start = false;
        }
    }
}

var chain = new Chain();

// JSON Response Handler
function handleJsonResponse(json, element) {
    if (json['success'] === true && json['message']) {
        $('#alert').prepend(
            '<div class="alert alert-success alert-dismissible">' +
            '<i class="bi bi-check-circle-fill"></i> ' + json['message'] +
            ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>'
        );
    }

    if (json['success'] === false && json['message']) {
        $('#alert').prepend(
            '<div class="alert alert-danger alert-dismissible">' +
            '<i class="bi bi-exclamation-circle-fill"></i> ' + json['message'] +
            ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>'
        );
    }

    if (typeof json['errors'] == 'object') {
        for (var key in json['errors']) {
            $('#input-' + key).addClass('is-invalid');
            $('#error-' + key).html(json['errors'][key]).addClass('d-block');
        }
    }
}

// Forms
$(document).on('submit', 'form', function (e) {
    var element = this;
    var button = (e.originalEvent !== undefined && e.originalEvent.submitter !== undefined) ? e.originalEvent.submitter : '';

    if ($(element).attr('data-oc-toggle') == 'ajax' || $(button).attr('data-oc-toggle') == 'ajax') {
        e.preventDefault();

        var form = e.target;
        var action = $(button).attr('formaction') || $(form).attr('action');
        var method = $(button).attr('formmethod') || $(form).attr('method') || 'post';
        var enctype = $(button).attr('formenctype') || $(form).attr('enctype') || 'application/x-www-form-urlencoded';

        if (typeof CKEDITOR != 'undefined') {
            for (instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
            }
        }

        $.ajax({
            url: action.replaceAll('&amp;', '&'),
            type: method,
            data: $(form).serialize(),
            dataType: 'json',
            contentType: enctype,
            beforeSend: function () {
                $(button).button('loading');
            },
            complete: function () {
                $(button).button('reset');
            },
            success: function (json, textStatus) {
                console.log(json);

                $('.alert-dismissible').remove();
                $(element).find('.is-invalid').removeClass('is-invalid');
                $(element).find('.invalid-feedback').removeClass('d-block');

                if (json['redirect']) {
                    location = json['redirect'];
                }

                if (json['replace_url']) {
                    window.history.pushState(null, null, json['replace_url']);

                    if (json['form_action']) {
                        $(element).attr('action', json['form_action']);

                        if ($(element).find('input[name="_method"]').length === 0) {
                            $(element).prepend('<input type="hidden" name="_method" value="PUT">');
                        } else {
                            $(element).find('input[name="_method"]').val('PUT');
                        }
                    }
                }

                handleJsonResponse(json, element);

                if (json['success'] === true) {
                    var url = $(form).attr('data-oc-load');
                    var target = $(form).attr('data-oc-target');

                    if (url !== undefined && target !== undefined) {
                        $(target).load(url);
                    }
                }

                for (key in json) {
                    $(element).find('[name=\'' + key + '\']').val(json[key]);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

                $('.alert-dismissible').remove();
                $(element).find('.is-invalid').removeClass('is-invalid');
                $(element).find('.invalid-feedback').removeClass('d-block');

                var json = {};
                try {
                    json = JSON.parse(xhr.responseText);
                } catch (e) {
                    json = { success: false, message: '發生錯誤，請稍後再試' };
                }

                handleJsonResponse(json, element);
            }
        });
    }
});

// Autocomplete
+function ($) {
    $.fn.autocomplete = function (option) {
        return this.each(function () {
            var element = this;
            var $dropdown = $('#' + $(element).attr('data-oc-target'));

            this.timer = null;
            this.items = [];

            $.extend(this, option);

            $(element).on('focusin', function () {
                element.request();
            });

            $(element).on('focusout', function (e) {
                if (!e.relatedTarget || !$(e.relatedTarget).hasClass('dropdown-item')) {
                    this.timer = setTimeout(function (object) {
                        object.removeClass('show');
                    }, 50, $dropdown);
                }
            });

            $(element).on('input', function (e) {
                element.request();
            });

            $dropdown.on('click', 'a', function (e) {
                e.preventDefault();

                var value = $(this).attr('href');

                if (element.items[value] !== undefined) {
                    element.select(element.items[value]);

                    $dropdown.removeClass('show');
                }
            });

            this.request = function () {
                clearTimeout(this.timer);

                $('#autocomplete-loading').remove();
                $dropdown.find('li').remove();

                $dropdown.prepend('<li id="autocomplete-loading"><span class="dropdown-item text-center disabled"><i class="bi bi-arrow-repeat spinning"></i></span></li>');
                $dropdown.addClass('show');

                this.timer = setTimeout(function (object) {
                    object.source($(object).val(), $.proxy(object.response, object));
                }, 150, this);
            }

            this.response = function (json) {
                var html = '';
                var category = {};
                var name;
                var i = 0, j = 0;

                if (json.length) {
                    for (i = 0; i < json.length; i++) {
                        this.items[json[i]['value']] = json[i];

                        if (!json[i]['category']) {
                            html += '<li><a href="' + json[i]['value'] + '" class="dropdown-item">' + json[i]['label'] + '</a></li>';
                        } else {
                            name = json[i]['category'];

                            if (!category[name]) {
                                category[name] = [];
                            }

                            category[name].push(json[i]);
                        }
                    }

                    for (name in category) {
                        html += '<li><h6 class="dropdown-header">' + name + '</h6></li>';

                        for (j = 0; j < category[name].length; j++) {
                            html += '<li><a href="' + category[name][j]['value'] + '" class="dropdown-item">' + category[name][j]['label'] + '</a></li>';
                        }
                    }

                    $dropdown.html(html);
                } else {
                    $dropdown.removeClass('show');
                }
            }
        });
    }
}(jQuery);

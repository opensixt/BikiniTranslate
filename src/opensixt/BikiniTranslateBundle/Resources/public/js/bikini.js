jQuery(document).ready(function($) {
    // symfony flasher
    // wait 5 seconds, then fade out slowly each second.
    // if mouseover was triggered, stop fading out
    $('div.flash', 'section.main').each(function(idx, ele) {
        var $ele = $(ele);
        var callback = function() {
            $ele.fadeOut('slow');
        };

        $ele.mouseover(function() {
            clearTimeout($ele.data('timer'));
        }).click(function() {
            $ele.data('timer', setTimeout(callback, 1000));
        }).data('timer', setTimeout(callback, 5000 + idx * 1000));
    });
});

function bikiniConfirm(confirmMessage, okLabel, cancelLabel, okCallback, cancelCallback)
{
    var html = [];
    html.push('<p class="validateTips">' + confirmMessage + '</p>');

    var $dialog = jQuery('<div></div>')
        .html(html.join('\n'))
        .dialog({
            autoOpen: true,
            title: '',
            modal: true,
            buttons: [
                {
                    text: okLabel,
                    click: function() {
                        if (typeof okCallback == 'function') {
                            setTimeout(okCallback, 50);
                        }
                        jQuery(this).dialog("destroy");
                    }
                },
                {
                    text: cancelLabel,
                    click: function() {
                        if (typeof cancelCallback == 'function') {
                            setTimeout(cancelCallback, 50);
                        }
                        jQuery(this).dialog("destroy");
                    }
                }
            ]
        });
}

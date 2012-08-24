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

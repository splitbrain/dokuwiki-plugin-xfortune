/**
 * Script for plugin_xfortune
 *
 * Fetches a new cookie
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

jQuery(function () {
    jQuery('div.plugin_xfortune').each(function () {
        var $self = jQuery(this);
        if(!$self.data('time')) return;
        if(!$self.data('cookie')) return;

        window.setInterval(function () {
            jQuery.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                {
                    call: 'plugin_xfortune',
                    cookie: $self.data('cookie')
                },
                function (data) {
                    $self.html(data)
                }
            )
        }, $self.data('time') * 1000);
    });
});


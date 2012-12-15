/**
 * Script for plugin_xfortune
 *
 * Fetches a new cookie
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

function plugin_xfortune() {
    if (jQuery('#plugin_xfortune').length === 0) {
        return;
    }

    jQuery.post(
        DOKU_BASE + 'lib/plugins/xfortune/ajax.php',
        { cookie: encodeURI(plugin_xfortune_cookie) },
        function (data) {
            if (data === '') { 
                return; 
            }

            jQuery('#plugin_xfortune').html(data);

            // restart timer
            window.setTimeout("plugin_xfortune()", plugin_xfortune_time);
        }
    );
}

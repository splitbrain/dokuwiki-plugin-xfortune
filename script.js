/**
 * Script for plugin_xfortune
 *
 * Fetches a new cookie
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

function plugin_xfortune(){
    if(!document.getElementById){
        return;
    }
    var obj = document.getElementById('plugin_xfortune');
    if(obj === null){
        return;
    }

    // We use SACK to do the AJAX requests
    var ajax = new sack(DOKU_BASE+'lib/plugins/xfortune/ajax.php');
    ajax.AjaxFailedAlert = '';
    ajax.encodeURIString = false;

    // define callback
    ajax.onCompletion = function(){
        var data = this.response;
        if(data === ''){ return; }
        var out = document.getElementById('plugin_xfortune');

        out.style.visibility = 'hidden';
        out.innerHTML = data;
        out.style.visibility = 'visible';

        // restart timer
        window.setTimeout("plugin_xfortune()",plugin_xfortune_time);
    };

    ajax.runAJAX('cookie='+encodeURI(plugin_xfortune_cookie)+'&type='+encodeURI(plugin_xfortune_type));
}

<?php
/**
 * Display Fortune cookies
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

class action_plugin_xfortune extends DokuWiki_Action_Plugin {

    /** @inheritdoc */
    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax_call_unknown');
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'handle_claim');
    }

    /**
     * Handle the ajax call
     *
     * @param Doku_Event $event
     * @param $param
     */
    function handle_ajax_call_unknown(Doku_Event $event, $param) {
        if($event->data != 'plugin_xfortune') return;
        $event->preventDefault();
        $event->stopPropagation();
        global $INPUT;
        echo helper_plugin_xfortune::getCookieHTML($INPUT->str('cookie'));
    }

    /**
     * Set a small cookie as tagline
     *
     * @param Doku_Event $event
     * @param $param
     */
    function handle_claim(Doku_Event $event, $param) {
        if($this->getConf('claim') === '') return;
        global $conf;

        $cookie = helper_plugin_xfortune::getCookieHTML($this->getConf('claim'), 2, 130);
        $conf['tagline'] = $cookie;

    }
}

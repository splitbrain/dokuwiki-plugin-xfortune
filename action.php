<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

/**
 * Display Fortune cookies
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
class action_plugin_xfortune extends ActionPlugin
{
    /** @inheritdoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handleAjaxCallUnknown');
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'handleClaim');
    }

    /**
     * Handle the ajax call
     *
     * @param Event $event
     * @param $param
     */
    public function handleAjaxCallUnknown(Event $event, $param)
    {
        if ($event->data != 'plugin_xfortune') return;
        $event->preventDefault();
        $event->stopPropagation();
        global $INPUT;
        echo helper_plugin_xfortune::getCookieHTML($INPUT->str('cookie'));
    }

    /**
     * Set a small cookie as tagline
     *
     * @param Event $event
     * @param $param
     */
    public function handleClaim(Event $event, $param)
    {
        if ($this->getConf('claim') === '') return;
        global $conf;

        $cookie = helper_plugin_xfortune::getCookieHTML($this->getConf('claim'), 2, 130);
        $conf['tagline'] = $cookie;
    }
}

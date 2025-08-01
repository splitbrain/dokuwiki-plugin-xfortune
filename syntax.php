<?php

use dokuwiki\Extension\SyntaxPlugin;

/**
 * Display Fortune cookies
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */


class syntax_plugin_xfortune extends SyntaxPlugin
{
    /**
     * What kind of syntax are we?
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    public function getPType()
    {
        return 'block';
    }

    /**
     * Where to sort in?
     */
    public function getSort()
    {
        return 302;
    }

    /**
     * Connect pattern to lexer
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('\{\{xfortune>[^}]*\}\}', $mode, 'plugin_xfortune');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $match = substr($match, 11, -2); //strip markup from start and end

        $data = [];

        //handle params
        [$cookie, $params] = explode('?', $match, 2);

        //xfortune cookie file
        $data['cookie'] = cleanID($cookie);

        //time interval for changing cookies
        $data['time'] = 30;
        if (preg_match('/\b(\d+)\b/i', $params, $match)) {
            $data['time'] = (int) $match[1];
        }

        //no hammering please!
        if ($data['time'] < 5) $data['time'] = 5;

        return $data;
    }

    /**
     * Create output
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode != 'xhtml') return false;

        $attr = ['class' => 'plugin_xfortune', 'data-time' => $data['time'], 'data-cookie' => $data['cookie']];

        $renderer->doc .= '<div ' . buildAttributes($attr) . '>';
        $renderer->doc .= helper_plugin_xfortune::getCookieHTML($data['cookie']);
        $renderer->doc .= '</div>';

        return true;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :

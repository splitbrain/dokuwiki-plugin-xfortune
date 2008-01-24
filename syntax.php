<?php
/**
 * Display Fortune cookies
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_xfortune extends DokuWiki_Syntax_Plugin {
    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Andreas Gohr',
            'email'  => 'andi@splitbrain.org',
            'date'   => '2005-11-18',
            'name'   => 'Fortune Plugin',
            'desc'   => 'Displays random fortune cookies using AJAX requests',
            'url'    => 'http://wiki.splitbrain.org/plugin:gallery',
        );
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 302;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{xfortune>[^}]*\}\}',$mode,'plugin_xfortune');
    }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        $match = substr($match,11,-2); //strip markup from start and end

        $data = array();

        //handle params
        list($cookie,$params) = explode('?',$match,2);

        //xfortune cookie file
        $data['cookie'] = cleanID($cookie);

        //time interval for changing cookies
        if(preg_match('/\b(\d+)\b/i',$params,$match)){
            $data['time'] = $match[1];
        }else{
            $data['time'] = 30;
        }
        //no hammering please!
        if($data['time'] < 5) $data['time'] = 5;

        return $data;
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
            $renderer->doc .= '<div id="plugin_xfortune">';
            $renderer->doc .= $this->_getCookie($data['cookie']);
            $renderer->doc .= '</div>';
            $renderer->doc .= $this->_script($data['cookie'],$data['time']);
            return true;
        }
        return false;
    }

    function _script($cookie,$time){
        $str  = '<script type="text/javascript" language="javascript">';
        $str .= 'var plugin_xfortune_time = '.($time*1000).';';
        $str .= 'var plugin_xfortune_cookie = \''.$cookie."';";
        $str .= "addEvent(window,'load',plugin_xfortune);";
        $str .= '</script>';
        return $str;
    }

    /**
     * Returns one random cookie
     *
     * Uses code from phpmyxfortune.php by Robbie B.
     *
     * @author Robbie B.
     * @author Andreas Gohr <andi@splitbrain.org>
     * @link   http://freshmeat.net/projects/phpmyxfortune/
     */
    function _getCookie($cookie){
        $file = mediaFN($cookie);
        if(!@file_exists($file)) return 'ERROR: cookie file not found';

        $dimFile = filesize($file);
        mt_srand( (double) microtime() * 1000000);
        $numRandom = mt_rand(0,$dimFile);

        $fd = fopen($file, 'r');
        if (!$fd) return 'ERROR: reading cookie file failed';

        // jump to random place in file
        fseek($fd, $numRandom);

        // seek cookie start
        $Character  = '';
        $StringTemp = '';
        while (strcmp($Character,"%\n")!=0 && !feof($fd)){
            $Character = fgets($fd, 1024);
        }
        if (feof($fd)) fseek($fd,1);

        // read cookie
        $Character = '';
        $Character = fgets($fd, 1024);
        while (strcmp($Character,"%\n")!=0 ) {
            $StringTemp .= htmlspecialchars($Character).'<br />';
            $Character   = fgets($fd, 1024);
        }
        fclose($fd);

        return $StringTemp;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :

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

        //handle params
        list($cookie,$params) = explode('?',$match,2);

        //xfortune cookie file
        $data = array(
            'cookie' => cleanID($cookie),
            'time' => 30,
            'page_type' => 'media',
        );

        list($time, $more) = explode(' ', $params, 2);

        //time interval for changing cookies
        if(preg_match('/\b(\d+)\b/i',$time,$match)){
            $data['time'] = $match[1];
        }

        if(preg_match('/\bwiki\b/i',$more,$match)){
            $data['type'] = 'wiki';
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
            $renderer->doc .= $this->_getCookie($data['cookie'], $data['type']);
            $renderer->doc .= '</div>';
            $renderer->doc .= $this->_script($data['cookie'],$data['time'],$data['type']);
            return true;
        }
        return false;
    }

    function _script($cookie,$time,$type){
        $str  = '<script type="text/javascript" language="javascript">';
        $str .= 'var plugin_xfortune_time = '.($time*1000).';';
        $str .= 'var plugin_xfortune_type = \''.$type.'\';';
        $str .= 'var plugin_xfortune_cookie = \''.$cookie."';";
        $str .= "addEvent(window,'load',plugin_xfortune);";
        $str .= '</script>';
        return $str;
    }

    /**
     * Returns one random cookie from a wiki page
     *
     * @author Franck Brignoli <fbrignoli@gmail.com>
     */
    function _getCookieFromWiki($cookie){
        $file = wikiFN($cookie);
        if(!@file_exists($file)) return 'ERROR: cookie file not found ('.$file.')';

        $content = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $dim = count($content);
        
        mt_srand( (double) microtime() * 1000000);
        $rnd = mt_rand(0, $dim - 1);
        $cookie = str_replace('  * ', '', $content[$rnd]);

        return $cookie;
    }

    /**
     * Returns one random cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _getCookieFromMedia($cookie){
        $file = mediaFN($cookie);
        if(!@file_exists($file)) return 'ERROR: cookie file not found ('.$file.')';

        $dim = filesize($file);
        if($dim < 2) return "ERROR: invalid cookie file $file";
        mt_srand( (double) microtime() * 1000000);
        $rnd = mt_rand(0,$dim);

        $fd = fopen($file, 'r');
        if (!$fd) return "ERROR: reading cookie file $file failed";

        // jump to random place in file
        fseek($fd, $rnd);

        $text   = '';
        $line   = '';
        $cookie = false;
        $test   = 0;
        while(true){
            $seek = ftell($fd);
            $line = fgets($fd, 1024);

            if($seek == 0){
                // start of file always starts a cookie
                $cookie = true;
                if($line == "%\n"){
                    // ignore delimiter if exists
                    continue;
                }else{
                    // part of the cookie
                    $text .= htmlspecialchars($line).'<br />';
                    continue;
                }
            }

            if(feof($fd)){
                if($cookie){
                    // we had a cookie already, stop here
                    break;
                }else{
                    // no cookie yet, wrap around
                    fseek($fd,0);
                    continue;
                }
            }

            if($line == "%\n"){
                if($cookie){
                    // we had a cookie already, stop here
                    break;
                }elseif($seek == $dim -2){
                    // it's the end of file delimiter, wrap around
                    fseek($fd,0);
                    continue;
                }else{
                    // start of the cookie
                    $cookie = true;
                    continue;
                }
            }

            // part of the cookie?
            if($cookie){
                $text .= htmlspecialchars($line).'<br />';
            }
        }
        fclose($fd);

        // if it is not valid UTF-8 assume it's latin1
        if(!utf8_check($text)) return utf8_encode($text);

        return $text;
    }

    /**
     * Returns one random cookie
     *
     * @author Franck Brignoli <fbrignoli@gmail.com>
     */
    function _getCookie($cookie, $type){      
        if ($type == 'wiki'){
            return self::_getCookieFromWiki($cookie);
        }else{
            return self::_getCookieFromMedia($cookie);
        }
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :

<?php
/**
 * AJAX Backend Function for plugin_xfortune
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

//fix for Opera XMLHttpRequests
if(!count($_POST) && $HTTP_RAW_POST_DATA){
  parse_str($HTTP_RAW_POST_DATA, $_POST);
}

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_INC.'inc/init.php');
//close sesseion
session_write_close();

require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'inc/utf8.php');
require_once(DOKU_PLUGIN.'xfortune/syntax.php');

header('Content-Type: text/html; charset=utf-8');
$cookie = cleanID($_POST['cookie']);
$type = ($_POST['type'] == 'wiki') ? 'wiki' : 'media';
print syntax_plugin_xfortune::_getCookie($cookie, $type);

?>

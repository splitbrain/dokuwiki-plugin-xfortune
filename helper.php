<?php
/**
 * Display Fortune cookies
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

class helper_plugin_xfortune extends DokuWiki_Plugin {

    /**
     * Get a random cookie properly escaped
     *
     * @param string $cookieID the media file id to the cookie file
     * @param int $maxlines
     * @return string
     */
    static public function getCookieHTML($cookieID, $maxlines=0, $maxchars=0) {
        if($maxlines) {
            $cookie = self::getSmallCookie($cookieID, $maxlines, $maxchars);
        } else {
            $cookie = self::getCookie($cookieID);
        }

        return nl2br(hsc($cookie));
    }

    /**
     * Tries to find a cookie with a maximum number of lines
     *
     * gives up after a 100 tries
     *
     * @param $cookieID
     * @param int $maxlines maximum lines to return
     * @return string
     */
    static public function getSmallCookie($cookieID, $maxlines, $maxchars){
        $runaway = 100;
        $tries = 0;
        if($maxlines < 1) $maxlines = 1;
        if($maxchars < 1) $maxlines = 250;

        do {
            $cookie = self::getCookie($cookieID);
            $lines = count(explode("\n", $cookie));
        } while( ($lines > $maxlines || strlen($cookie) > $maxchars) && $tries++ < $runaway);

        return $cookie;
    }

    /**
     * Get a file for the given ID
     *
     * If the ID ends with a colon a namespace is assumed and a random txt file is picked from there
     *
     * @param $cookieID
     * @return string
     * @throws Exception
     */
    static public function id2file($cookieID) {
        $file = mediaFN($cookieID);
        $isns = is_dir($file);
        if($isns) $cookieID .= ':dir';

        if(auth_quickaclcheck($cookieID) < AUTH_READ) throw new Exception("No read permissions for $cookieID");

        if($isns) {
            $dir = $file;
            $files = glob("$dir/*.txt");
            if(!count($files))  throw new Exception("Could not find fortune files in $cookieID");
            $file = $files[array_rand($files)];
        }

        // we now should have a valid file
        if(!file_exists($file)) throw new Exception("No fortune file at $cookieID");

        return $file;
    }

    /**
     * Returns one random cookie
     *
     * @param string $cookieID the media file id to the cookie file
     * @return string
     */
    static public function getCookie($cookieID) {
        try {
            $file = self::id2file($cookieID);
        } catch(Exception $e) {
            return 'ERROR: '.$e->getMessage();
        }

        $dim = filesize($file);
        if($dim < 2) return "ERROR: invalid cookie file $file";
        mt_srand((double) microtime() * 1000000);
        $rnd = mt_rand(0, $dim);

        $fd = fopen($file, 'r');
        if(!$fd) return "ERROR: reading cookie file $file failed";

        // jump to random place in file
        fseek($fd, $rnd);

        $text = '';
        $cookie = false;
        while(true) {
            $seek = ftell($fd);
            $line = fgets($fd, 1024);

            if($seek == 0) {
                // start of file always starts a cookie
                $cookie = true;
                if($line == "%\n") {
                    // ignore delimiter if exists
                    continue;
                } else {
                    // part of the cookie
                    $text .= $line;
                    continue;
                }
            }

            if(feof($fd)) {
                if($cookie) {
                    // we had a cookie already, stop here
                    break;
                } else {
                    // no cookie yet, wrap around
                    fseek($fd, 0);
                    continue;
                }
            }

            if($line == "%\n") {
                if($cookie) {
                    // we had a cookie already, stop here
                    break;
                } elseif($seek == $dim - 2) {
                    // it's the end of file delimiter, wrap around
                    fseek($fd, 0);
                    continue;
                } else {
                    // start of the cookie
                    $cookie = true;
                    continue;
                }
            }

            // part of the cookie?
            if($cookie) {
                $text .= $line;
            }
        }
        fclose($fd);

        $text = trim($text);

        // if it is not valid UTF-8 assume it's latin1
        if(!utf8_check($text)) return utf8_encode($text);

        return $text;
    }

}

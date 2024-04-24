<?php
namespace Rcs\Utils;

final class Utils
{

    public function __construct()
    {
        
    }

    public static function toTimeAgo($datetime)
    {
        return Utils::time_elapsed_string($datetime);
    }

    public static function time_elapsed_string($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    public static function GetRemoteResponse($host, $port, $timeout, $out, &$sResp)
    {
        $s = '';
        $bTimeout = 0;

        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);

        if (!$fp) {
            $bTimeout = 999;
        } else {
            $n = fwrite($fp, $out, strlen($out));
            $n = fwrite($fp, chr(- 1));
            @stream_set_timeout($fp, $timeout);
            $c = '';
            $bDone = false;
            $bHead = false;
            $lenCount = 0;
            $i = 0;
            while ((!feof($fp)) && ($bTimeout == 0) && (!$bDone)) {
                $info = @stream_get_meta_data($fp);
                if ($info['timed_out']) {
                    $bTimeout = 1;
                }
                if ($bTimeout == 0) {
                    $c = fread($fp, 1);
                    if ($c != chr(- 1)) {
                        $s .= $c;
                    } else {
                        $bDone = true;
                    }
                }
            }
            fclose($fp);
        }
        $sResp = $s;

        return $bTimeout;
    }

    /**
     * Generate 12 digit unique Number
     * @return integer
     */
    public static function c_nid()
    {
        // 12 in length
        return sprintf(
            '%d%d%d%d%d%d%d%d%d%d%d%d',
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9)
        );
    }


    /**
     * generate 6 digit random number with 7 as first character in range 700000 - 799999
     * @return integer
     */
    public static function c_stan()
    {
        // 700000 - 799999
        return sprintf(
            '7%d%d%d%d%d',
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9),
            mt_rand(0, 9)
        );
    }

    /**
     * Generate unique code
     *
     * @param type $sDelim
     *
     * @return type
     */
    public static function c_uuid($sDelim = '')
    {
        // The field names refer to RFC 4122 section 4.1.2
        $uuid = sprintf(
            '%04x%04x%s%04x%s%03x4%s%04x%s%04x%04x%04x',
            mt_rand(0, 65535),
            mt_rand(0, 65535), // 32 bits for "time_low"
            $sDelim,
            mt_rand(0, 65535), // 16 bits for "time_mid"
            $sDelim,
            mt_rand(0, 4095), // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
            $sDelim,
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
            $sDelim,
            // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
            // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
            // 8 bits for "clk_seq_low"
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535) // 48 bits for "node"
        );
        return $uuid;
    }

    public static function getHttpResponse($url, $data, &$Resp, $Opt)
    {
        $postdata = (is_array($data)) ? http_build_query($data) : $data;
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context = stream_context_create($opts);
        $Resp = file_get_contents($url, false, $context);
        if ($Resp) {
            return true;
        }
        return false;
    }

    public static function getMessage($oDB, $MTI, $RC, $module = "", $custom = "")
    {
        $retval = "ERROR Tidak Terdefinisi";
//if ($RC == "" || $RC == null) $RC = "68";
//if ($MTI == "" || $MTI == null) $MTI = '2110';
        try {
            $sQ = "SELECT * FROM CPPMOD_RC_GENERAL WHERE CPM_RC_MTI = '" . $MTI .
                "' AND CPM_RC_RC = '" . $RC . "'";
            $prep = $oDB->prepare($sQ);
            $result = $prep->execute();
            if ($result) {
                if ($row = $result->fetch()) {//$oDB->fetch_array($result)) {
                    $retval = $row['CPM_RC_MESSAGE'];
                }
            }
        } catch (Exception $err) {
            
        }

        if ($module != "") {
            try {
                $sQ = "SELECT * FROM CPPMOD_RC_MODULE WHERE CPM_RC_MODULE = '" . $module .
                    "' AND CPM_RC_MTI = '" . $MTI .
                    "' AND CPM_RC_RC = '" . $RC . "'";
                $prep = $oDB->prepare($sQ);
                $result = $prep->execute();
                if ($result) {
                    if ($row = $result->fetch()) {//$oDB->fetch_array($result)) {
                        $retval = $row['CPM_RC_MESSAGE'];
                    }
                }
            } catch (Exception $err) {
                
            }
        }

        if ($module != "" && $custom != "") {
            try {
                $sQ = "SELECT * FROM CPPMOD_RC_CUSTOM WHERE CPM_RC_CUSTOM = '" . $custom .
                    "' AND CPM_RC_MODULE = '" . $module .
                    "' AND CPM_RC_MTI = '" . $MTI .
                    "' AND CPM_RC_RC = '" . $RC .
                    "'";
                $prep = $oDB->prepare($sQ);
                $result = $prep->execute();
                if ($result) {
                    if ($row = $result->fetch()) {//$oDB->fetch_array($result)) {
                        $retval = $row['CPM_RC_MESSAGE'];
                    }
                }
            } catch (Exception $err) {
                
            }
        }

//permintaan 8 april 2015
//Tambah kurung siku [RC] di RCM
//ex: [5] ERROR Lainnya
        $rcMessage = $retval;
        $pos = strpos($rcMessage, "]");
        if ($pos === false) {
            $rcMessage = $rcMessage;
        }

//13 April 2015
//untuk RC <> 0000
//tambahan RCM ke:msn

        return $rcMessage;
    }
}

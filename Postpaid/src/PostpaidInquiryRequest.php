<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Postpaid;

use Rcs\Iso8583\CISO8583Message;

/**
 * Description of PostpaidInquiryRequest
 *
 * @author RCS
 */
class PostpaidInquiryRequest extends CISO8583Message
{

    function __construct()
    {
        parent::__construct();
        $this->SetVersion("2003");
        //set defaults
        $this->SetValueForDataElement(0, 2100); //mti
    }

    private function GetMappingKeyIdx($sKey)
    {
        $iKey = null;
        if ($sKey == 'pan') $iKey = 2;
        elseif ($sKey == 'stan') $iKey = 11;
        elseif ($sKey == 'dt') $iKey = 12;
        elseif ($sKey == 'merchant') $iKey = 26;
        elseif ($sKey == 'aiic') $iKey = 32;
        elseif ($sKey == 'fiic') $iKey = 33;
        elseif ($sKey == 'rc') $iKey = 39;
        elseif ($sKey == 'ppid') $iKey = 41;
        elseif ($sKey == 'priv') $iKey = 48;
        elseif ($sKey == 'trxid') $iKey = 120;

        return $iKey;
    }

    private function ConstructPrivateData($aPriv)
    {
        $sPriv = '';
        //defaults
        $sPriv .= '0000000'; //switchid
        //end defaults
        $sPriv .= str_pad($aPriv['subid'], 12, " ", STR_PAD_LEFT);

        return $sPriv;
    }

    public function SetComponentTmp($sKey, $value)
    {
        $keyIdx = $this->GetMappingKeyIdx($sKey);
        if ($sKey == 'priv') {
            $value = $this->ConstructPrivateData($value);
        } elseif ($sKey == 'stan') {
            $value = str_pad($value, 12, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'trxid') {
            $value = substr($value, 0, 50);
        }
        $this->SetValueForDataElement($keyIdx, $value);
    }
}
<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Pdam;

use Rcs\Iso8583\CISO8583Message;

/**
 * Description of PdamInquiryRequest
 *
 * @author yudoh
 */
class PdamInquiryRequest extends CISO8583Message
{
    public function __construct()
    {
        parent::__construct();
        $this->SetVersion("2003");
        //set defaults
        $this->SetValueForDataElement(0, 2100); //mti
        $this->SetValueForDataElement(26, "6012"); //Merchant code for Internet
        $this->SetValueForDataElement(32, "0000000"); //Bank code
    }

    private function GetMappingKeyIdx($sKey)
    {
        $iKey = "";
        if ($sKey == 'pan') {
            $iKey = 2;
        } elseif ($sKey == 'stan') {
            $iKey = 11;
        } elseif ($sKey == 'dt') {
            $iKey = 12;
        } elseif ($sKey == 'bankcode') {
            $iKey = 32;
        } elseif ($sKey == 'centralid') {
            $iKey = 33;
        } elseif ($sKey == 'ppid') {
            $iKey = 41;
        } elseif ($sKey == 'priv') {
            $iKey = 48;
        } elseif ($sKey == 'priv2') {
            $iKey = 62;
        } elseif ($sKey == 'trxid') {
            $iKey = 120;
        }

        return $iKey;
    }

    private function ConstructPrivateData($aPriv)
    {
        $sPriv = '';
        $sPriv .= str_pad($aPriv['switcherid'], 7, "0", STR_PAD_LEFT);  // switcher identification code
        $sPriv .= str_pad($aPriv['customerid'], 20, " ", STR_PAD_LEFT); // customer ID
        if ($this->GetValueForDataElement(2) == '87004') {
            $sPriv .= str_pad($aPriv['billid'], 20, "0", STR_PAD_LEFT);  // bill ID
        } else {
            $sPriv .= str_pad($aPriv['billid'], 20, " ", STR_PAD_LEFT);  // bill ID
        }
        $sPriv .= $aPriv['flag'];          // reserved, customer ID used then 0 bill ID used then 1
        $sPriv .= $aPriv['distcode']; //str_pad($aPriv['distcode'], 7, "0", STR_PAD_LEFT);         // reserved

        return $sPriv;
    }

    public function SetComponentTmp($sKey, $value)
    {
        $keyIdx = $this->GetMappingKeyIdx($sKey);
        if ($sKey == 'priv') {
            $value = $this->ConstructPrivateData($value);
        } elseif ($sKey == 'ppid') {
            $value = str_pad($value, 16, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'stan') {
            $value = str_pad($value, 12, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'central_id') {
            $value = str_pad($value, 7, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'bankcode') {
            $value = str_pad($value, 7, "0", STR_PAD_LEFT);
        }
        $this->SetValueForDataElement($keyIdx, $value);
    }
}

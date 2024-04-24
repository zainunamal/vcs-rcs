<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Prepaid;

use Rcs\Iso8583\CISO8583Message;

/**
 * Description of PrepaidAdviceRequest
 *
 * @author RCS
 */
class PrepaidAdviceRequest extends CISO8583Message
{
    public function __construct($sMTI = 2220)
    {
        parent::__construct();
        $this->SetVersion("2003");
        //set defaults
        $this->SetValueForDataElement(0, $sMTI); //mti for advice
        $this->SetValueForDataElement(26, "6021"); //Merchant code for Internet
        $this->SetValueForDataElement(32, "0000000"); //Bank code
    }

    private function GetMappingKeyIdx($sKey)
    {
        $iKey='';
        if ($sKey == 'pan') {
            $iKey = 2;
        } elseif ($sKey == 'rp') {
            $iKey = 4;
        } elseif ($sKey == 'stan') {
            $iKey = 11;
        } elseif ($sKey == 'dt') {
            $iKey = 12;
        } elseif ($sKey == 'bank_code') {
            $iKey = 32;
        } elseif ($sKey == 'central_id') {
            $iKey = 33;
        } elseif ($sKey == 'ppid') {
            $iKey = 41;
        } elseif ($sKey == 'priv') {
            $iKey = 48;
        } elseif ($sKey == 'trxid') {
            $iKey = 120;
        }

        return $iKey;
    }

    private function ConstructPrivateData($aPriv)
    {
        $sPriv = '';
        $sPriv .= str_pad($aPriv['switcherid'], 7, "0", STR_PAD_LEFT); //switchid
        $sPriv .= $aPriv['msn']; //Meter Serial Number
        $sPriv .= str_pad($aPriv['sid'], 12, "0", STR_PAD_LEFT); //subscriber id
        $sPriv .= $aPriv['flag']; //flag
        $sPriv .= str_pad($aPriv['plnrefnum'], 32, " ", STR_PAD_RIGHT); //PLN Reference Number
        $sPriv .= str_pad($aPriv['swrefnum'], 32, " ", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['name'], 25, " ", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['segment'], 4, " ", STR_PAD_RIGHT);
        $sPriv .= $aPriv['pcc']; //Power Consuming Category
        $sPriv .= $aPriv['mu']; //Minor Unit
        $sPriv .= str_pad($aPriv['ac'], 10, "0", STR_PAD_LEFT);
        $sPriv .= $aPriv['opt'];

        return $sPriv;
    }

    public function SetComponentTmp($sKey, $value)
    {
        $keyIdx = $this->GetMappingKeyIdx($sKey);
        if ($sKey == 'priv') {
            $value = $this->ConstructPrivateData($value);
        } elseif ($sKey == 'rp') {
            $value = '3600'.str_pad($value, 12, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'ppid') {
            $value = str_pad($value, 16, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'stan') {
            $value = str_pad($value, 12, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'central_id') {
            $value = str_pad($value, 7, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'bank_code') {
            $value = str_pad($value, 7, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'trxid') {
            $value = substr($value, 0, 50);
        }
        $this->SetValueForDataElement($keyIdx, $value);
    }
}

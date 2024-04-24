<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Nontaglis;

use Rcs\Iso8583\CISO8583Message;

/**
 * Description of NontaglisInquiryRequest
 *
 * @author yudo
 */
class NontaglisInquiryRequest extends CISO8583Message
{
    public function __construct()
    {
        parent::__construct();
        $this->SetVersion("2003");
        //set defaults
        $this->SetValueForDataElement(0, 2100); //mti
    }

    private function GetMappingKeyIdx($sKey)
    {
        $iKey ="";
        if ($sKey == 'pan') {
            $iKey = 2;
        } elseif ($sKey == 'stan') {
            $iKey = 11;
        } elseif ($sKey == 'dt') {
            $iKey = 12;
        } elseif ($sKey == 'merchant') {
            $iKey = 26;
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
        $sPriv .= str_pad($aPriv['noreg'], 32, " ", STR_PAD_RIGHT); //nomor registrasi
        $sPriv .= str_pad($aPriv['areacode'], 2, "0", STR_PAD_LEFT); //area
        $sPriv .= str_pad($aPriv['transcode'], 3, "0", STR_PAD_LEFT); //kode transaksi

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
        }
        $this->SetValueForDataElement($keyIdx, $value);
    }
}

<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Nontaglis;

use Rcs\Iso8583\CISO8583Message;

/**
 * Description of NontaglisReversalRequest
 *
 * @author yudo
 */
class NontaglisReversalRequest extends CISO8583Message
{
    public function __construct($sMTI)
    {
        parent::__construct();
        $this->SetVersion("2003");
        //set defaults
        $this->SetValueForDataElement(0, $sMTI);
    }

    private function GetMappingKeyIdx($sKey)
    {
        $iKey = '';
        if ($sKey == 'mti') {
            $iKey = 0;
        } elseif ($sKey == 'pan') {
            $iKey = 2;
        } elseif ($sKey == 'rp') {
            $iKey = 4;
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
        } elseif ($sKey == 'ode') {
            $iKey = 56;
        } elseif ($sKey == 'nf') {
            $iKey = 61;
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
        $sPriv .= str_pad($aPriv['switcherid'], 7, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['noreg'], 32, " ", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['areacode'], 2, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['transcode'], 3, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['transname'], 25, " ", STR_PAD_RIGHT);
        $sPriv .= $aPriv['regdate'];
        $sPriv .= $aPriv['expdate'];
        $sPriv .= str_pad($aPriv['subid'], 12, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['subname'], 25, " ", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['refnum'], 32, "0", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['rrn'], 32, "0", STR_PAD_RIGHT);
        $sPriv .= $aPriv['su'];
        $sPriv .= str_pad($aPriv['sua'], 35, " ", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['sup'], 15, " ", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['ttamu'], 1, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['tta'], 17, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['bmu'], 1, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['bv'], 17, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['acmu'], 1, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['ac'], 10, "0", STR_PAD_LEFT);

        return $sPriv;
    }

    private function ConstructOrigData($aODE)
    {
        $sODE = '';
        $sODE .= '2200';
        $sODE .= $aODE['stan'];
        $sODE .= $aODE['dt'];
        $sODE .= str_pad($aODE['bank_code'], 7, "0", STR_PAD_LEFT);
        return $sODE;
    }

    private function ConstructNationalField($aNF)
    {
        $sNF = '';
        $sNF .= str_pad($aNF['mn'], 32, " ", STR_PAD_RIGHT);
        $sNF .= str_pad($aNF['segment'], 4, " ", STR_PAD_RIGHT);
        $sNF .= str_pad($aNF['power'], 9, "0", STR_PAD_LEFT);
        return $sNF;
    }

    private function ConstructSecondPrivateData($aPriv2)
    {
        $sPriv2 = '';
        $sPriv2 .= str_pad($aPriv2['total'], 2, "0", STR_PAD_LEFT);
        for ($i = 0; $i < $aPriv2['total']; $i++) {
            $idx = $i + 1;
            $sPriv2 .= str_pad($aPriv2['cdc' . $idx], 2, "0", STR_PAD_LEFT);
            $sPriv2 .= str_pad($aPriv2['cdmu' . $idx], 1, "0", STR_PAD_LEFT);
            $sPriv2 .= str_pad($aPriv2['cdva' . $idx], 17, "0", STR_PAD_LEFT);
        }
        return $sPriv2;
    }

    public function SetComponentTmp($sKey, $value)
    {
        $keyIdx = $this->GetMappingKeyIdx($sKey);
        if ($sKey == 'ode') {
            $value = $this->ConstructOrigData($value);
        } elseif ($sKey == 'rp') {
            $value = '3600' . str_pad($value, 12, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'priv') {
            $value = $this->ConstructPrivateData($value);
        } elseif ($sKey == 'nf') {
            $value = $this->ConstructNationalField($value);
        } elseif ($sKey == 'priv2') {
            $value = $this->ConstructSecondPrivateData($value);
        } elseif ($sKey == 'ppid') {
            $value = str_pad($value, 16, "0", STR_PAD_LEFT);
        }
        $this->SetValueForDataElement($keyIdx, $value);
    }
}

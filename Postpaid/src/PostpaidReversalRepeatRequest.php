<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Postpaid;

use Rcs\Postpaid\PostpaidReversalRequest;

/**
 * Description of PostpaidReversalRepeatRequest
 *
 * @author RCS
 */
class PostpaidReversalRepeatRequest extends PostpaidReversalRequest
{

    function __construct()
    {
        parent::__construct();
        $this->SetValueForDataElement(0, 2401);
        $this->SetVersion("2003");
    }

    private function GetMappingKeyIdx($sKey)
    {
        if ($sKey == 'mti') $iKey = 0;
        elseif ($sKey == 'pan') $iKey = 2;
        elseif ($sKey == 'rp') $iKey = 4;
        elseif ($sKey == 'stan') $iKey = 11;
        elseif ($sKey == 'dt') $iKey = 12;
        elseif ($sKey == 'merchant') $iKey = 26;
        elseif ($sKey == 'aiic') $iKey = 32;
        elseif ($sKey == 'fiic') $iKey = 33;
        elseif ($sKey == 'ppid') $iKey = 41;
        elseif ($sKey == 'priv') $iKey = 48;
        elseif ($sKey == 'ode') $iKey = 56;
        elseif ($sKey == 'trxid') $iKey = 120;

        return $iKey;
    }

    private function ConstructPrivateData($aPriv)
    {

        $sPriv = '';
        $sPriv .= str_pad($aPriv['switcherid'], 7, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['subid'], 12, " ", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['nb'], 1, "0", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['nbo'], 2, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['refnum'], 32, " ", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['nm'], 25, " ", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['su'], 5, " ", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['sup'], 15, " ", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['segmen'], 4, " ", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['power'], 9, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['adm'], 9, "0", STR_PAD_LEFT);
        for ($i = 0; $i < intval(trim($aPriv['nb'])); $i++) {
            $idx   = $i + 1;
            $sPriv .= str_pad($aPriv['bp'.$idx], 6, " ", STR_PAD_RIGHT);
            $sPriv .= str_pad($aPriv['dd'.$idx], 8, " ", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['mrd'.$idx], 8, " ", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['tb'.$idx], 12, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['inc'.$idx], 11, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['tax'.$idx], 10, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['pen'.$idx], 12, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['pmr1'.$idx], 8, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['cmr1'.$idx], 8, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['pmr2'.$idx], 8, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['cmr2'.$idx], 8, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['pmr3'.$idx], 8, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['cmr3'.$idx], 8, "0", STR_PAD_LEFT);
        }
        return $sPriv;
    }

    private function ConstructOrigData($aODE)
    {
        $sODE = '';
        $sODE .= $aODE['mti'];
        $sODE .= str_pad($aODE['stan'], 12, "0", STR_PAD_LEFT);
        $sODE .= $aODE['dt'];
        $sODE .= str_pad($aODE['aiic'], 7, " ", STR_PAD_RIGHT);
        return $sODE;
    }

    public function SetComponentTmp($sKey, $value)
    {
        $keyIdx = $this->GetMappingKeyIdx($sKey);
        if ($sKey == 'ode') {
            $value = $this->ConstructOrigData($value);
        } elseif ($sKey == 'rp') {
            $value = str_pad($value, 12, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'priv') {
            $value = $this->ConstructPrivateData($value);
        } elseif ($sKey == 'ppid') {
            $value = str_pad($value, 16, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'trxid') {
            $value = substr($value, 0, 50);
        }
        $this->SetValueForDataElement($keyIdx, $value);
    }
}
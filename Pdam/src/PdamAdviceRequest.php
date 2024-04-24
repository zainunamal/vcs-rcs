<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Pdam;

use Rcs\Iso8583\CISO8583Message;

/**
 * Description of PdamAdviceRequest
 *
 * @author yudoh
 */
class PdamAdviceRequest extends CISO8583Message
{
    public function __construct()
    {
        parent::__construct();
        $this->SetVersion("2003");
        //set defaults
        $this->SetValueForDataElement(0, 2220); //mti for advice
        $this->SetValueForDataElement(26, "6012"); //Merchant code for Internet
        $this->SetValueForDataElement(32, "0000000"); //Bank code
    }

    private function GetMappingKeyIdx($sKey)
    {
        if ($sKey == 'pan') {
            $iKey = 2;
        } elseif ($sKey == 'rp') {
            $iKey = 4;
        } elseif ($sKey == 'stan') {
            $iKey = 11;
        } elseif ($sKey == 'dt') {
            $iKey = 12;
        } elseif ($sKey == 'merchant') {
            $iKey = 26;
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
        }

        return $iKey;
    }

    private function ConstructPrivateData($aPriv)
    {
        $sPriv = '';
        $sPriv .= str_pad($aPriv['switcherid'], 7, "0", STR_PAD_LEFT);  // switcher identification code
        $sPriv .= str_pad($aPriv['customerid'], 20, " ", STR_PAD_LEFT);  // customer ID
        $sPriv .= str_pad($aPriv['billid'], 20, " ", STR_PAD_LEFT);  // bill ID
        $sPriv .= $aPriv['flag'];           // reserved, customer ID used then 0 bill ID used then 1
        $sPriv .= $aPriv['distcode'];          // reserved
        $sPriv .= $aPriv['bs'];
        $sPriv .= $aPriv['ps'];
        $sPriv .= str_pad($aPriv['ob'], 2, "0", STR_PAD_LEFT);  // total outstanding bill
        $sPriv .= str_pad($aPriv['gwrefnum'], 32, "0", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['swrefnum'], 32, "0", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['subname'], 30, " ", STR_PAD_RIGHT); // subscriber name
        $sPriv .= str_pad($aPriv['subaddress'], 30, " ", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['subsegmen'], 30, "0", STR_PAD_RIGHT);
        $sPriv .= str_pad($aPriv['minor'], 1, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['admincharge'], 10, "0", STR_PAD_LEFT);

        //Repeated
        for ($i = 0; $i < trim($aPriv['bs']); $i++) {
            $idx = $i + 1;
            $sPriv .= str_pad($aPriv['repbillid'][$idx], 20, " ", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['repbilrefnum'][$idx], 32, " ", STR_PAD_LEFT);
            $sPriv .= $aPriv['billperiod'][$idx];
            $sPriv .= $aPriv['duedate'][$idx];
            $sPriv .= str_pad($aPriv['billamount'][$idx], 12, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['stamp'][$idx], 10, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['vat'][$idx], 10, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['penalty'][$idx], 12, "0", STR_PAD_LEFT);

            $sPriv .= str_pad($aPriv['danameter'][$idx], 12, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['ac'][$idx], 12, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['gr'][$idx], 12, "0", STR_PAD_LEFT);
            $sPriv .= str_pad($aPriv['installment'][$idx], 12, "0", STR_PAD_LEFT);
        }
        return $sPriv;
    }

    private function ConstructPrivateData2($aPriv)
    {
        $sPriv = '';
        $sPriv .= str_pad($aPriv['address'], 30, " ", STR_PAD_RIGHT);  // address
        $sPriv .= $aPriv['bs'];
        $sPriv .= str_pad($aPriv['segmen'], 20, " ", STR_PAD_RIGHT);
        return $sPriv;
    }

    public function SetComponentTmp($sKey, $value)
    {
        $keyIdx = $this->GetMappingKeyIdx($sKey);
        if ($sKey == 'rp') {
            $value = '3600' . str_pad($value, 12, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'priv') {
            $value = $this->ConstructPrivateData($value);
        } elseif ($sKey == 'priv2') {
            $value = $this->ConstructPrivateData2($value);
        } elseif ($sKey == 'ppid') {
            $value = str_pad($value, 16, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'stan') {
            $value = str_pad($value, 12, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'centralid') {
            $value = str_pad($value, 7, "0", STR_PAD_LEFT);
        }
        $this->SetValueForDataElement($keyIdx, $value);
    }
}

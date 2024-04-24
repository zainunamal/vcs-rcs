<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Pdam;

use Rcs\Iso8583\CISO8583Parser;
use Rcs\Iso8583\ProtocolGeneric;

/**
 * Description of PdamAdviceResponse
 *
 * @author yudoh
 */
class PdamAdviceResponse extends CISO8583Parser
{
    public $dataElement = array();
    public $privateData = array();
    public $secondPrivateData = array();
    public $privateDataHeader = array();
    public $secondPrivateDataHeader = array();

    public function __construct($isoStream)
    {
        parent::__construct($isoStream);
    }

    private function GetMappingValue($idx)
    {
        switch ($idx) {
            case 0: $sKey = 'mti';
                break;
            case 1: $sKey = 'bitmap';
                break;
            case 2: $sKey = 'pan';
                break;
            case 4: $sKey = 'rp';
                break;
            case 11: $sKey = 'stan';
                break;
            case 12: $sKey = 'dt';
                break;
            case 15: $sKey = 'dtset';
                break;
            case 26: $sKey = 'merchant';
                break;
            case 32: $sKey = 'bankcode';
                break;
            case 33: $sKey = 'centralid';
                break;
            case 39: $sKey = 'rc';
                break;
            case 41: $sKey = 'ppid';
                break;
            case 48: $sKey = 'priv';
                break;
            case 62: $sKey = 'priv2';
                break;
            case 63: $sKey = 'infotext';
                break;
        }
        return $sKey;
    }

    private function SplitPrivateData($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);

        $splitter->SetBlockSingleLengthArray(array(7, 20, 20, 1, 7, 1, 1, 2, 32, 32, 30, 30, 30, 1, 10));
        $splitter->SetBlockSingleAssocNameArray(array('switcherid', 'customerid', 'billid', 'flag', 'distcode', 'bs', 'ps', 'ob', 'gwrefnum', 'swrefnum', 'subname', 'subaddress', 'subsegmen', 'minor', 'admincharge'));

        $splitter->SetBlockRepeatLengthArray(array(20, 32, 6, 8, 12, 10, 10, 12, 12, 12, 12, 12));
        $splitter->SetBlockRepeatAssocNameArray(array('repbillid', 'repbilrefnum', 'billperiod', 'duedate', 'billamount', 'stamp', 'vat', 'penalty', 'danameter', 'ac', 'gr', 'installment'));

        $splitter->SetRepeatKey('bs');
        $splitter->Extract();

        $this->privateDataSingle = $splitter->GetComponentSingleArray();
        $this->privateDataRepeat = $splitter->GetComponentRepeatArray();

        foreach ($this->privateDataSingle as $key => $value) {
            $this->privateData[$key] = trim($value);
        }
        foreach ($this->privateDataRepeat as $key => $value) {
            for ($i = 0; $i < $this->privateDataSingle[$splitter->GetRepeatKey()]; $i++) {
                $idx = $i + 1;
                $this->privateData[$key][$idx] = trim($value[$i]);
            }
        }
    }

    private function SplitSecondPrivateData($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);

        $splitter->SetBlockSingleLengthArray(array(30, 1, 20));
        $splitter->SetBlockSingleAssocNameArray(array('address', 'bs', 'segmen'));

        $splitter->SetBlockRepeatLengthArray(array(12, 12, 12, 12));
        $splitter->SetBlockRepeatAssocNameArray(array('danameter', 'pdam_adm', 'waste', 'installment'));
        $splitter->SetRepeatKey('bs');
        $splitter->Extract();

        $this->privateDataSingle = $splitter->GetComponentSingleArray();
        $this->privateDataRepeat = $splitter->GetComponentRepeatArray();

        foreach ($this->privateDataSingle as $key => $value) {
            $this->privateData2[$key] = trim($value);
        }
        foreach ($this->privateDataRepeat as $key => $value) {
            for ($i = 0; $i < $this->privateDataSingle[$splitter->GetRepeatKey()]; $i++) {
                $idx = $i + 1;
                $this->privateData2[$key][$idx] = trim($value[$i]);
            }
        }
    }

    public function ExtractDataElement()
    {
        if ($this->Parse()) {
            $rDataElmt = $this->GetParsedDataElement();
            foreach ($rDataElmt as $iKey => $value) {
                $sKey = $this->GetMappingValue($iKey);
                $this->dataElement[$sKey] = $value;
            }
            if ($this->dataElement['rc'] == "0000") {
                $this->SplitPrivateData($this->dataElement['priv']);
                $this->SplitSecondPrivateData($this->dataElement['priv2']);
                $this->dataElement['priv'] = $this->privateData;
                $this->dataElement['priv2'] = $this->secondPrivateData;
            }
        }
    }
}

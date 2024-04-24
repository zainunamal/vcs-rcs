<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Nontaglis;

use Rcs\Iso8583\CISO8583Parser;
use Rcs\Iso8583\ProtocolGeneric;

/**
 * Description of NontaglisReversalResponse
 *
 * @author yudo
 */
class NontaglisReversalResponse extends CISO8583Parser
{
    public $dataElement = array();
    public $privateData = array();
    public $originalData = array();
    public $secondPrivateData = array();
    public $secondPrivateDataSingle = array();
    public $secondPrivateDataRepeat = array();

    public function __construct($isoStream)
    {
        parent::__construct($isoStream);
    }

    private function GetMappingValue($idx)
    {
        $sKey = '';
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
            case 26: $sKey = 'merchant';
                break;
            case 32: $sKey = 'bank_code';
                break;
            case 33: $sKey = 'central_id';
                break;
            case 39: $sKey = 'rc';
                break;
            case 41: $sKey = 'ppid';
                break;
            case 48: $sKey = 'priv';
                break;
            case 56: $sKey = 'ode';
                break;
            case 61: $sKey = 'nf';
                break;
            case 62: $sKey = 'priv2';
                break;
            case 120: $sKey = 'trxid';
                break;
        }
        return $sKey;
    }

    private function GetTrxAmount($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);
        $splitter->SetBlockHeaderLengthArray(array(3, 1, 12));
        $splitter->SetBlockHeaderAssocNameArray(array('icc', 'cmu', 'val'));
        $splitter->Extract();
        $arr = $splitter->GetComponentHeaderArray();
        $dec = pow(10, $arr['cmu']);
        return $arr['val'] / $dec;
    }

    private function SplitPrivateData($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);
        $splitter->SetBlockHeaderLengthArray(array(7, 32, 2, 3, 25, 8, 8, 12, 25, 32, 32, 5, 35, 15, 1, 17, 1, 17, 1, 10));
        $splitter->SetBlockHeaderAssocNameArray(array('switcherid', 'noreg', 'areacode', 'transcode', 'transname', 'regdate', 'expdate', 'subid', 'subname', 'refnum', 'rrn', 'su', 'sua', 'sup', 'ttamu', 'tta', 'bmu', 'bv', 'acmu', 'ac'));
        $splitter->Extract();
        $this->privateData = $splitter->GetComponentHeaderArray();
        foreach ($this->privateData as $key => $value) {
            $this->privateData[$key] = trim($value);
        }
    }

    private function SplitOriginalData($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);
        $splitter->SetBlockHeaderLengthArray(array(4, 12, 14, 7));
        $splitter->SetBlockHeaderAssocNameArray(array('mti', 'stan', 'dt', 'bank_code'));
        $splitter->Extract();
        $this->originalData = $splitter->GetComponentHeaderArray();
        foreach ($this->originalData as $key => $value) {
            $this->originalData[$key] = trim($value);
        }
    }

    private function SplitNationalField($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);
        $splitter->SetBlockHeaderLengthArray(array(32, 4, 9));
        $splitter->SetBlockHeaderAssocNameArray(array('mn', 'segment', 'power'));
        $splitter->Extract();
        $this->nationalFieldHeader = $splitter->GetComponentHeaderArray();

        foreach ($this->nationalFieldHeader as $key => $value) {
            $this->nationalField[$key] = trim($value);
        }
    }

    private function SplitSecondPrivateData($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);

        $splitter->SetBlockSingleLengthArray(array(2));
        $splitter->SetBlockRepeatLengthArray(array(2, 1, 17));

        $splitter->SetBlockSingleAssocNameArray(array('total'));
        $splitter->SetBlockRepeatAssocNameArray(array('cdc', 'cdmu', 'cdva'));
        $splitter->SetRepeatKey('total');
        $splitter->Extract();

        $this->secondPrivateDataSingle = $splitter->GetComponentSingleArray();
        $this->secondPrivateDataRepeat = $splitter->GetComponentRepeatArray();

        foreach ($this->secondPrivateDataSingle as $key => $value) {
            $this->secondPrivateData[$key] = trim($value);
        }
        if ($this->secondPrivateDataRepeat) {
            foreach ($this->secondPrivateDataRepeat as $key => $value) {
                for ($i = 0; $i < $this->secondPrivateDataSingle[$splitter->GetRepeatKey()]; $i++) {
                    $idx = $i + 1;
                    $this->secondPrivateData[$key . "$idx"] = trim($value[$i]);
                }
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
            $this->SplitPrivateData($this->dataElement['priv']);
            $this->SplitOriginalData($this->dataElement['ode']);
            if ($this->privateData['areacode'] == 54) {
                $this->SplitNationalField($this->dataElement['nf']);
            }
            $this->SplitSecondPrivateData($this->dataElement['priv2']);

            $this->dataElement['priv'] = $this->privateData;
            $this->dataElement['ode'] = $this->originalData;
            if ($this->privateData['areacode'] == 54) {
                $this->dataElement['nf'] = $this->nationalField;
            }
            $this->dataElement['priv2'] = $this->secondPrivateData;
            $this->dataElement['rp'] = $this->GetTrxAmount($this->dataElement['rp']);
        }
    }
}

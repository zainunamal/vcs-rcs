<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Prepaid;

use Rcs\Iso8583\CISO8583Parser;
use Rcs\Iso8583\ProtocolGeneric;

/**
 * Description of PrepaidInquiryResponse
 *
 * @author RCS
 */
class PrepaidInquiryResponse extends CISO8583Parser
{
    public $dataElement             = array();
    public $privateData             = array();
    public $privateDataHeader       = array();
    public $secondPrivateData       = array();
    public $secondPrivateDataSingle = array();
    public $secondPrivateDataRepeat = array();

    public function __construct($isoStream)
    {
        parent::__construct($isoStream);
    }

    private function GetMappingValue($idx)
    {
        $sKey='';
        switch ($idx) {
            case 0: $sKey = 'mti';
                break;
            case 1: $sKey = 'bitmap';
                break;
            case 2: $sKey = 'pan';
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
            case 62: $sKey = 'priv2';
                break;
            case 120: $sKey = 'trxid';
                break;
        }
        return $sKey;
    }

    private function SplitPrivateData($stream)
    {
        $splitter                = new ProtocolGeneric();
        $splitter->SetStream($stream);
        $splitter->SetBlockHeaderLengthArray(array(7, 11, 12, 1, 32, 32, 25, 4, 9,
            1, 10));
        $splitter->SetBlockHeaderAssocNameArray(array('switcherid', 'msn', 'sid',
            'flag', 'plnrefnum', 'swrefnum', 'name', 'segment', 'pcc', 'mu', 'ac'));
        $splitter->Extract();
        $this->privateDataHeader = $splitter->GetComponentHeaderArray();

        foreach ($this->privateDataHeader as $key => $value) {
            $this->privateData[$key] = trim($value);
        }
    }

    private function SplitSecondPrivateData($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);
        $splitter->SetBlockSingleLengthArray(array(2, 5, 15, 5, 1));
        $splitter->SetBlockSingleAssocNameArray(array('dc', 'su', 'sup', 'max', 'trepeat'));
        $splitter->SetBlockRepeatLengthArray(array(11));
        $splitter->SetBlockRepeatAssocNameArray(array('ppunsold'));

        $splitter->SetRepeatKey('trepeat');

        $splitter->Extract();

        $this->secondPrivateDataSingle = $splitter->GetComponentSingleArray();
        $this->secondPrivateDataRepeat = $splitter->GetComponentRepeatArray();

        foreach ($this->secondPrivateDataSingle as $key => $value) {
            $this->secondPrivateData[$key] = trim($value);
        }
        if ($this->secondPrivateDataRepeat) {
            foreach ($this->secondPrivateDataRepeat as $key => $value) {
                for ($i = 0; $i < $this->secondPrivateDataSingle[$splitter->GetRepeatKey()]; $i++) {
                    $idx                                 = $i + 1;
                    $this->secondPrivateData[$key][$idx] = trim($value[$i]);
                }
            }
        }
    }

    public function ExtractDataElement()
    {
        if ($this->Parse()) {
            $rDataElmt = $this->GetParsedDataElement();
            foreach ($rDataElmt as $iKey => $value) {
                $sKey                     = $this->GetMappingValue($iKey);
                $this->dataElement[$sKey] = $value;
            }
            if ($this->dataElement['rc'] == "0000") {
                $this->SplitPrivateData($this->dataElement['priv']);
                $this->SplitSecondPrivateData($this->dataElement['priv2']);
                $this->dataElement['priv']  = $this->privateData;
                $this->dataElement['priv2'] = $this->secondPrivateData;
            }
        }
    }
}

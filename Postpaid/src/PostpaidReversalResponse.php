<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Postpaid;

use Rcs\Iso8583\CISO8583Parser;
use Rcs\Iso8583\ProtocolGeneric;

/**
 * Description of PostpaidReversalResponse
 *
 * @author RCS
 */
class PostpaidReversalResponse extends CISO8583Parser
{
    public $dataElement  = array();
    public $privateData  = array();
    public $originalData = array();

    function __construct($isoStream)
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
            case 32: $sKey = 'aiic';
                break;
            case 33: $sKey = 'fiic';
                break;
            case 39: $sKey = 'rc';
                break;
            case 41: $sKey = 'ppid';
                break;
            case 48: $sKey = 'priv';
                break;
            case 56: $sKey = 'ode';
                break;
            case 120: $sKey = 'trxid';
                break;
        }
        return $sKey;
    }

    public function GetTrxAmount($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);
        $splitter->SetBlockHeaderLengthArray(array(3, 1, 12));
        $splitter->SetBlockHeaderAssocNameArray(array('icc', 'cmu', 'val'));
        $splitter->Extract();
        $arr      = $splitter->GetComponentHeaderArray();
        $dec      = pow(10, $arr['cmu']);
        return $arr['val'] / $dec;
    }

    private function SplitPrivateData($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);

        $splitter->SetBlockHeaderLengthArray(array(7, 12));
        $splitter->SetBlockSingleLengthArray(array(1, 1, 2, 32, 25, 5, 15, 4, 9,
            9));
        $splitter->SetBlockRepeatLengthArray(array(6, 8, 8, 12, 11, 10, 12, 8, 8,
            8, 8, 8, 8));
        $splitter->SetBlockTrackingLengthArray(array());

        $splitter->SetBlockHeaderAssocNameArray(array('switcherid', 'subid'));
        $splitter->SetBlockSingleAssocNameArray(array('nb', 'ps', 'nbo', 'refnum',
            'nm', 'su', 'sup', 'segmen', 'power', 'adm'));
        $splitter->SetBlockRepeatAssocNameArray(array('bp', 'dd', 'mrd', 'tb', 'inc',
            'tax', 'pen', 'pmr1', 'cmr1', 'pmr2', 'cmr2', 'pmr3', 'cmr3'));
        $splitter->SetBlockTrackingAssocNameArray(array());

        $splitter->SetRepeatKey('nb');
        $splitter->Extract();

        $this->privateDataHeader   = $splitter->GetComponentHeaderArray();
        $this->privateDataSingle   = $splitter->GetComponentSingleArray();
        $this->privateDataRepeat   = $splitter->GetComponentRepeatArray();
        $this->privateDataTracking = $splitter->GetComponentTrackingArray();
        foreach ($this->privateDataHeader as $key => $value)
            $this->privateData[$key]   = trim($value);
        foreach ($this->privateDataSingle as $key => $value)
            $this->privateData[$key]   = trim($value);
        foreach ($this->privateDataRepeat as $key => $value) {
            if (sizeof($this->privateDataRepeat) > 0) {
                for ($i = 0; $i < $this->privateDataSingle[$splitter->GetRepeatKey()]; $i++) {
                    $idx                            = $i + 1;
                    $this->privateData[$key."$idx"] = trim($value[$i]);
                }
            }
        }
        foreach ($this->privateDataTracking as $key => $value)
            $this->privateData[$key] = trim($value[0]);
    }

    private function SplitOriginalData($stream)
    {
        $splitter = new ProtocolGeneric();
        $splitter->SetStream($stream);

        $splitter->SetBlockHeaderLengthArray(array(4, 12, 14, 7));
        $splitter->SetBlockHeaderAssocNameArray(array('mti', 'stan', 'dt', 'aiic'));
        $splitter->Extract();

        $this->privateDataHeader   = $splitter->GetComponentHeaderArray();
        $this->privateDataSingle   = $splitter->GetComponentSingleArray();
        $this->privateDataRepeat   = $splitter->GetComponentRepeatArray();
        $this->privateDataTracking = $splitter->GetComponentTrackingArray();

        foreach ($this->privateDataHeader as $key => $value)
            $this->originalData[$key] = trim($value);
    }

    function ExtractDataElement()
    {
        if ($this->Parse()) {
            $rDataElmt = $this->GetParsedDataElement();

            foreach ($rDataElmt as $iKey => $value) {
                $sKey                     = $this->GetMappingValue($iKey);
                $this->dataElement[$sKey] = $value;
            }
            $this->SplitPrivateData($this->dataElement['priv']);
            $this->dataElement['priv'] = $this->privateData;
            $this->SplitOriginalData($this->dataElement['ode']);
            $this->dataElement['ode']  = $this->originalData;
            $this->dataElement['rp']   = $this->GetTrxAmount($this->dataElement['rp']);
        }
    }
}
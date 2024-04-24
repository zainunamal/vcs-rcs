<?php


namespace Rcs\Deposit;

use Rcs\Iso8583\CISO8583Parser;
use Rcs\Iso8583\ProtocolGeneric;

/**
 * Description of BalanceResponse
 *
 * @author rcs-yudo
 */
class BalanceResponse extends CISO8583Parser
{
    public $dataElement = array();
    public $privateData = array();
    public $secondPrivateData = array();
    public $privateDataHeader = array();

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
            case 3: $sKey = 'p_code';
                break;
            case 4: $sKey = 'rp';
                break;
            case 12: $sKey = 'dt';
                break;
            case 39: $sKey = 'rc';
                break;
            case 48: $sKey = 'priv';
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
        $splitter->SetBlockHeaderLengthArray(array(32));
        $splitter->SetBlockHeaderAssocNameArray(array('amount'));
        $splitter->Extract();
        $this->privateDataHeader = $splitter->GetComponentHeaderArray();

        foreach ($this->privateDataHeader as $key => $value) {
            $this->privateData[$key] = trim($value);
        }
    }

    public function getDataElement()
    {
        return $this->dataElement['rp'];
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
                $this->dataElement['priv'] = $this->privateData;
            }
            $this->dataElement['rp'] = $this->GetTrxAmount($this->dataElement['rp']);
        }
    }
}

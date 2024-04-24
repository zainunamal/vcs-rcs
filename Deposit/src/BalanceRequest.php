<?php


namespace Rcs\Deposit;

use Rcs\Iso8583\CISO8583Message;

/**
 * Description of BalanceRequest
 *
 * @author rcs-yudo
 */
class BalanceRequest extends CISO8583Message
{
    public function __construct()
    {
        parent::__construct();
        $this->SetVersion("2003");
        //set defaults
        $this->SetValueForDataElement(0, 2200); //mti
        $this->SetValueForDataElement(3, "310000"); //processing code for balance check
        $this->SetValueForDataElement(4, "3600000000000000"); //processing code for balance check
    }

    private function GetMappingKeyIdx($sKey)
    {
        if ($sKey == 'pan') {
            $iKey = 2;
        } elseif ($sKey == 'dt') {
            $iKey = 12;
        }

        return $iKey;
    }

    public function SetComponentTmp($sKey, $value)
    {
        $keyIdx = $this->GetMappingKeyIdx($sKey);
        $this->SetValueForDataElement($keyIdx, $value);
    }
}

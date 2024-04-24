<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Rcs\Deposit;

use Rcs\Deposit\BalanceRequest;
use Rcs\Deposit\BalanceResponse;
use Rcs\Utils\Utils;

/**
 * Description of ServiceDeposit
 *
 * @author rcs-yudo
 */
class ServiceDeposit
{
    private $ServerAddress;
    private $ServerPort;
    private $ServerTimeOut;

    public function __construct($address, $port, $timeout)
    {
        $this->ServerAddress = $address;
        $this->ServerPort = $port;
        $this->ServerTimeOut = $timeout;
    }

    /**
     * Prosedur untuk melakukan pengecekan account balance
     * param :
     *        $account : account number yang akan dicek
     * return type : boolean / string
     *        false : pengecekan gagal
     *        string : balance amount (dalam angka)
     */
    public function BalanceCheck(&$rc, $account)
    {
        $balance = null;

        $BalanceReq = new BalanceRequest();
        $BalanceReq->SetComponentTmp('pan', $account);
        $BalanceReq->SetComponentTmp('dt', strftime("%Y%m%d%H%M%S", time()));
        $BalanceReq->ConstructStream();
        $sBalanceRequestStream = $BalanceReq->GetConstructedStream();

        $bOK = Utils::GetRemoteResponse(
            $this->ServerAddress,
            $this->ServerPort,
            $this->ServerTimeOut,
            $sBalanceRequestStream,
            $sResp
        );
        if ($bOK == 0) {
            $sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
            $Res = new BalanceResponse($sResp);
            $Res->ExtractDataElement();

            if ($Res->dataElement['mti'] == '2210') {
                $rc = intval($Res->dataElement['rc']);
                if ($Res->dataElement['rc'] == '0000') { // valid balance check response
                    $balance = ltrim($Res->dataElement['priv']['amount'], "0");
                    if ($balance == "") {
                        $balance = "0";
                    }
                }
            }
        }
        return $balance;
    }
}

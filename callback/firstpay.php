<?php
/**
 * WHMCS Sample Payment Callback File
 *
 * This sample file demonstrates how a payment gateway callback should be
 * handled within WHMCS.
 *
 * It demonstrates verifying that the payment gateway module is active,
 * validating an Invoice ID, checking for the existence of a Transaction ID,
 * Logging the Transaction for debugging and Adding Payment to an Invoice.
 *
 * For more information, please refer to the online documentation.
 *
 * @see http://docs.whmcs.com/Gateway_Module_Developer_Docs
 *
 * @copyright Copyright (c) WHMCS Limited 2015
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);
    $accountId = $gatewayParams['MxID'];
    $secretKey = $gatewayParams['MxPass'];
// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Retrieve data returned in payment gateway callback
// Varies per payment gateway
	$rtnCode = trim($_POST["Code"]);
	$rtnMsg = trim($_POST["Msg"]);
	$rtnFDTid = trim($_POST["FDTid"]);



/**
 * Validate callback authenticity.
 *
 * Most payment gateways provide a method of verifying that a callback
 * originated from them. In the case of our example here, this is achieved by
 * way of a shared secret which is used to build and compare a hash.
 */

  //퍼스트페이 처리

  	$keyData = $accountID;	//가맹점 배포 PASSKEY 입력
  	$EncodeType = "U";								//인코딩 TYPE 입력(U:utf-8, E:euc-kr)
  	$fdkSendHost = "ps.firstpay.co.kr";			//FDK 요청 HOST
  	$fdkSendPath = "/jsp/common/req.jsp";			//FDK 요청 PATH
  	$hashValue = "";	//HASH DATA
  	$rtnData = "";	//FDK 수신 DATA

  	//Request
  	$fdtid = trim($_POST["FDTid"]);
  	$mxid = trim($secretKey);
  	$mxissueno = trim($_SESSION['MxIssueNO']);

  	$amount = trim($_POST["Amount"]);
  	$pids = trim($_POST["PIDS"]);

  	/*****
  	* ■ Hash DATA 생성 처리
  	* FDTid 값이 있는 경우  MxID + MxIssueNO + keyData로 HashData 생성 처리
  	* FDTid 값이 없는 경우
  	*   1. PIDS(현금영수증 신분확인번호) 값이 있는 경우
  	*     MxID + MxIssueNO + Amount + PIDS + keyData로 HashData 생성 처리
  	*   2. PIDS(현금영수증 신분확인번호) 값이 없는 경우
  	*     MxID + MxIssueNO + Amount + keyData로 HashData 생성 처리
  	***********************************/
  	if(!is_null($fdtid) && $fdtid != ""){
  		$hashValue = strtoupper(hash("sha256",$mxid.$mxissueno.$keyData));
  	}else{
  		if(!is_null($pids) && $pids != ""){
  			$hashValue = strtoupper(hash("sha256",$mxid.$mxissueno.$amount.$pids.$keyData));
  		}else{
  			$hashValue = strtoupper(hash("sha256",$mxid.$mxissueno.$amount.$keyData));
  		}
  	}

include("/var/www/html/sites/BreachNodePanel/fdk_php_ext.php");
$fdtdata=$_POST;
$fdtdata['MxID'] = $mxid;
$fdtdata['MxIssueNO'] = $mxissueno;
	//request DATA (Client - FDK SERVER) WEB(HTTPS) 통신 처리
	$rtnData = sendHttps($fdkSendHost, $fdkSendPath, $fdtdata, $hashValue, $EncodeType);

	//rtnData to JSON DATA 전환 처리
	$resData = StringToJsonProc($rtnData);

$respdata="Payment Type: Credit Card".PHP_EOL.
  "Card Number: ".$resData['CcNO'].PHP_EOL.
  "Card Issuer: ".$resData['IssName'].PHP_EOL.
  "Card Acquier Name: ".$resData['AcqName'].PHP_EOL.
  "Installment: ".$resData['Installment'].PHP_EOL.
  "Reply Code and Message: [".$resData['ReplyCode']."]".$resData['ReplyMessage'].PHP_EOL.
  "Payment ID: ".$_SESSION['paysvcid'];

  //sendToDiscord($discord_msg,"log");
  if($resData['ReplyCode'] == "0000"){
    //결제 성공
$success = true;

  } else {
      $success = false;
          $transactionStatus = "[".$resData['ReplyCode']."]".$resData['ReplyMessage'];
    ?>
    <script>alert("<?=$resData['ReplyMessage'] ?>");</script>
    <?php
echo "<script>location.href='".$_SESSION['returnUrl']."';</script>";
  }

if($success){
$invoiceId = $_SESSION["InvoiceNOFDK"];
$transactionId = $resData["MxIssueNO"]."|".$resData["MxIssueDate"];
$paymentAmount = $_SESSION['USDAmount'];
$paymentFee = 0;
}




/**
 * Validate Callback Invoice ID.
 *
 * Checks invoice ID is a valid invoice number. Note it will count an
 * invoice in any status as valid.
 *
 * Performs a die upon encountering an invalid Invoice ID.
 *
 * Returns a normalised invoice ID.
 */
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

/**
 * Check Callback Transaction ID.
 *
 * Performs a check for any existing transactions with the same given
 * transaction number.
 *
 * Performs a die upon encountering a duplicate.
 */
checkCbTransID($transactionId);

/**
 * Log Transaction.
 *
 * Add an entry to the Gateway Log for debugging purposes.
 *
 * The debug data can be a string or an array. In the case of an
 * array it will be
 *
 * @param string $gatewayName        Display label
 * @param string|array $debugData    Data to log
 * @param string $transactionStatus  Status
 */
logTransaction($gatewayParams['name'], $_POST, $transactionStatus);

if ($success) {

    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId         Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float $paymentAmount   Amount paid (defaults to full balance)
     * @param float $paymentFee      Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        0,
        $gatewayModuleName
    );

}
?>

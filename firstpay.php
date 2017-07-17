<?php
session_start();
/**
 * WHMCS Sample Payment Gateway Module
 *
 * Payment Gateway modules allow you to integrate payment solutions with the
 * WHMCS platform.
 *
 * This sample file demonstrates how a payment gateway module for WHMCS should
 * be structured and all supported functionality it can contain.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "gatewaymodule" and therefore all functions
 * begin "firstpay_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _config
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see http://docs.whmcs.com/Gateway_Module_Developer_Docs
 *
 * @copyright Copyright (c) WHMCS Limited 2015
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see http://docs.whmcs.com/Gateway_Module_Meta_Data_Parameters
 *
 * @return array
 */
function firstpay_MetaData()
{
    return array(
        'DisplayName' => 'FirstPay',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function firstpay_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'FirstPay',
        ),
        // a text field type allows for single line text input
        'MxID' => array(
            'FriendlyName' => '가맹점 ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => '퍼스트페이 가맹점 ID를 입력하세요.',
        ),
        'MxPass' => array(
            'FriendlyName' => '가맹점 시크릿',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => '퍼스트페이 가맹점 시크릿를 입력하세요.',
        )
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see http://docs.whmcs.com/Payment_Gateway_Module_Parameters
 *
 * @return string
 */
function firstpay_link($params)
{

    // Gateway Configuration Parameters
    $accountId = $params['MxID'];
    $secretKey = $params['MxPass'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    $url = 'https://pg.firstpay.co.kr/jsp/main.jsp';

    $postfields = array();
    $postfields['username'] = $username;
    $postfields['invoice_id'] = $invoiceId;
    $postfields['description'] = $description;
    $postfields['amount'] = $amount;
    $postfields['currency'] = $currencyCode;
    $postfields['first_name'] = $firstname;
    $postfields['last_name'] = $lastname;
    $postfields['email'] = $email;
    $postfields['address1'] = $address1;
    $postfields['address2'] = $address2;
    $postfields['city'] = $city;
    $postfields['state'] = $state;
    $postfields['postcode'] = $postcode;
    $postfields['country'] = $country;
    $postfields['phone'] = $phone;
    $postfields['callback_url'] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';
    $postfields['return_url'] = $returnUrl;




$_SESSION['returnUrl'] = $returnUrl;

$_SESSION['InvoiceNOFDK'] = $params['invoiceid'];
$first_paydata['MxID'] = $accountId;
$first_paydata['MxIssueNO'] = "whmcs_".$params['invoiceid'];
$_SESSION['MxIssueNO'] = $first_paydata['MxIssueNO'];
$_SESSION['MxIssueDate'] = $first_paydata['MxIssueDate'];
$first_paydata['MxIssueDate'] = date("YmdHis");
$first_paydata['CcProdDesc'] = $description;
$first_paydata['ItemInfo'] = 2;
$first_paydata['connectionType'] = "https";

$first_paydata['Amount'] = round($amount);
$first_paydata['rtnUrl'] = $postfields['callback_url'];


  $first_paydata['LangType'] = "HAN";
  $first_paydata['CardSelect'] = "00";
  $first_paydata['Amount'] = ceil($first_paydata['Amount'] / 10) * 10;

if($country == "KR"){
      $first_paydata['LangType'] = "HAN";
  $first_paydata['CardSelect'] = "00";
  $first_paydata['Amount'] = ceil($first_paydata['Amount'] / 10) * 10;
} else {
$first_paydata['SelectPayment'] = "CRDT";
  $first_paydata['LangType'] = "ENG";
  $first_paydata['CardSelect'] = "09";


}
  $first_paydata['SupportDate']="WHMCS_".$country;
$first_paydata['EncodeType'] = "U";
$_SESSION['USDAmount'] = $params['basecurrencyamount'];


$first_paydata['CallHash'] = 	strtolower(hash("sha256",$first_paydata['MxID'].$first_paydata['MxIssueNO'].$first_paydata['Amount'].$secretKey));
  $pd="";
foreach ($first_paydata as $key => $value) {
  # code...
  $pd=$pd.$key."=".$value."|";
}

















    $htmlOutput = '<form method="post" action="' . $url . '">';

        $htmlOutput .= '<input type="hidden" name="PAYDATA" value="' . $pd . '" />';

    $htmlOutput .= '<input type="submit" value="' . $langPayNow .'" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}

/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see http://docs.whmcs.com/Payment_Gateway_Module_Parameters
 *
 * @return array Transaction response status
 */
function firstpay_refund($params)
{
    //Still working on it. FDHash error won't fixed unless FirstDataKorea Help it
    // Gateway Configuration Parameters
    $accountId = $params['MxID'];
    $secretKey = $params['MxPass'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];

    // Transaction Parameters
    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

print_r($params);
$fdkref=Array();
$fdkref['MxID'] = $accountId;

$transiddata=explode("|",$transactionIdToRefund);

$fdkref['MxIssueNO'] = $transiddata[0];
$fdkref['MxIssueDate'] = $transiddata[1];
$fdkref['CcMode'] = 10;
$fdkref['PayMethod'] = "CC";
$fdkref['TxCode'] = "EC131400";
$hash = strtoupper(hash("sha256",$fdkref['MxID'].$fdkref['MxIssueNO'].$secretKey));


  	$EncodeType = "U";								//인코딩 TYPE 입력(U:utf-8, E:euc-kr)
  	$fdkSendHost = "ps.firstpay.co.kr";			//FDK 요청 HOST
  	$fdkSendPath = "/jsp/common/req.jsp";			//FDK 요청 PATH
  	$hashValue = "";	//HASH DATA
  	$rtnData = "";	//FDK 수신 DATA
      include("/var/www/html/sites/BreachNodePanel/fdk_php_ext.php");
$result = sendHttps($fdkSendHost, $fdkSendPath, $fdkref, $hash, $EncodeType);
	$resData = StringToJsonProc($result);

$success="success";
if($resData['ReplyCode'] != "0000"){
    $success="declined";
}
    // perform API call to initiate refund and interpret result

    return array(
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => $success,
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $params,
        // Unique Transaction ID for the refund transaction
        'transid' => $fdkref['MxIssueNo'],
        // Optional fee amount for the fee value refunded
        'fees' => 0,
    );
}

/**
 * Cancel subscription.
 *
 * If the payment gateway creates subscriptions and stores the subscription
 * ID in tblhosting.subscriptionid, this function is called upon cancellation
 * or request by an admin user.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see http://docs.whmcs.com/Payment_Gateway_Module_Parameters
 *
 * @return array Transaction response status
 */
function firstpay_cancelSubscription($params)
{
    // Gateway Configuration Parameters
    $accountId = $params['accountID'];
    $secretKey = $params['secretKey'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];

    // Subscription Parameters
    $subscriptionIdToCancel = $params['subscriptionID'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // perform API call to cancel subscription and interpret result

    return array(
        // 'success' if successful, any other value for failure
        'status' => 'success',
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $responseData,
    );
}

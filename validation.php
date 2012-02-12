<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/payway.php');
include(dirname(__FILE__).'/../../header.php');

global $cookie;
global $cart;

$payway = new PayWay();

$transactionId = $_GET['tid'];
$signature = $_GET['sig'];
$card = $_GET['card'];

if (!isset($_GET['tid']) OR !isset($_GET['sig']) OR !isset($_GET['card']))
{
    $payway->validateOrder($cart->id, _PS_OS_ERROR_, 0, $payway->displayName, $payway->getTranslation('missingParameters'));
}
else
{
    $total = number_format(Tools::convertPrice($cart->getOrderTotal(true, 3), $payway->getCurrency()), 2, ',', '');
    $totalDot = number_format(Tools::convertPrice($cart->getOrderTotal(true, 3), $payway->getCurrency()), 2, '.', '');

    $calculatedSignature = strtoupper(md5($payway->shopId . $payway->secretKey . $cart->id . $payway->secretKey . $total . $payway->secretKey . $transactionId . $payway->secretKey));
    if ($signature != $calculatedSignature)
    {
        $payway->validateOrder($cart->id, _PS_OS_ERROR_, 0, $payway->displayName, $payway->getTranslation('invalidSignature'));
    }
    else
    {
        if ($payway->autoPayment)
            $payway->validateOrder($cart->id, _PS_OS_PREPARATION_, $totalDot, $payway->displayName, $payway->getTranslation('transactionId') . ' ' . $transactionId . '. ' . $payway->getTranslation('cardType') . ' ' . $card);
        else
            $payway->validateOrder($cart->id, _PS_OS_PAYMENT_, $totalDot, $payway->displayName, $payway->getTranslation('transactionId') . ' ' . $transactionId . '. ' . $payway->getTranslation('cardType') . ' ' . $card);
    }
    Tools::redirect('history.php');
}

include(dirname(__FILE__).'/../../footer.php');

?>

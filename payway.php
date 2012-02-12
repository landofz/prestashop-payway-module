<?php

include(dirname(__FILE__) . '/utility.php');

class PayWay extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();

    public  $shopId;
    public  $secretKey;
    public  $autoPayment;

    public function __construct()
    {
        $this->name = 'payway';
        $this->tab = 'Payment';
        $this->version = 0.9;
        
        $this->currencies = true;
        $this->currencies_mode = 'radio';

        $config = Configuration::getMultiple(array('PAYWAY_SHOP_ID', 'PAYWAY_SECRET_KEY', 'PAYWAY_AUTO_PAYMENT'));
        if (isset($config['PAYWAY_SHOP_ID']))
            $this->shopId = $config['PAYWAY_SHOP_ID'];
        if (isset($config['PAYWAY_SECRET_KEY']))
            $this->secretKey = $config['PAYWAY_SECRET_KEY'];
        if (isset($config['PAYWAY_AUTO_PAYMENT']))
            $this->autoPayment = $config['PAYWAY_AUTO_PAYMENT'];
        else
            $this->autoPayment = 1;

        parent::__construct(); /* The parent construct is required for translations */

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('T-Com Pay Way');
        $this->description = $this->l('Accept payments by T-Com Pay Way');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
        if (!isset($this->shopId) OR !isset($this->secretKey))
            $this->warning = $this->l('Shop ID and secret key must be configured in order to use this module correctly');
        if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
            $this->warning = $this->l('No currency set for this module');
    }

    public function install()
    {
        if (!parent::install() OR !$this->registerHook('payment'))
            return false;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('PAYWAY_SHOP_ID')
                OR !Configuration::deleteByName('PAYWAY_SECRET_KEY')
                OR !Configuration::deleteByName('PAYWAY_AUTO_PAYMENT')
                OR !parent::uninstall())
            return false;
    }

    private function _postValidation()
    {
        if (isset($_POST['btnSubmit']))
        {
            if (empty($_POST['shop_id']))
                $this->_postErrors[] = $this->l('Shop ID is required.');
            elseif (empty($_POST['secret_key']))
                $this->_postErrors[] = $this->l('Secret key is required.');
            elseif (empty($_POST['paymentType']) AND ($_POST['paymentType'] != 1) AND ($_POST['paymentType'] != 0))
                $this->_postErrors[] = $this->l('Payment type is invalid.');
        }
    }

    private function _postProcess()
    {
        if (isset($_POST['btnSubmit']))
        {
            Configuration::updateValue('PAYWAY_SHOP_ID', $_POST['shop_id']);
            Configuration::updateValue('PAYWAY_SECRET_KEY', $_POST['secret_key']);
            Configuration::updateValue('PAYWAY_AUTO_PAYMENT', $_POST['paymentType']);
            $this->autoPayment = $_POST['paymentType'];
        }
        $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Settings updated').'</div>';
    }

    private function _displayPayWay()
    {
        $this->_html .= '<img src="../modules/payway/payway.gif" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows you to accept payments by T-Com Pay Way.').'</b><br /><br />
        '.$this->l('If the client chooses this payment mode, your T-Com Pay Way account will be automatically credited.').'<br />
        '.$this->l('You need to configure your T-Com Pay Way account first before using this module.').'<br /><br /><br />';
    }

    private function _displayForm()
    {
        $this->_html .=
        '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <fieldset>
            <legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>
                <table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
                    <tr><td colspan="2">'.$this->l('Please specify the shop account details').'.<br /><br /></td></tr>
                    <tr><td width="130" style="height: 35px;">'.$this->l('Shop ID').'</td><td><input type="text" name="shop_id" value="'.htmlentities(Tools::getValue('shop_id', $this->shopId), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
                    <tr><td width="130" style="height: 35px;">'.$this->l('Secret key').'</td><td><input type="text" name="secret_key" value="'.htmlentities(Tools::getValue('secret_key', $this->secretKey), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
                    <tr>
                        <td width="130" style="height: 35px;">'.$this->l('Charge automatically').'</td>
                        <td>
                            <input type="radio" name="paymentType" id="paymentType_auto" value="1" '.(($this->autoPayment) ? 'checked="checked" ' : '').'/>
                            <label class="t" for="paymentType_auto"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
                            <input type="radio" name="paymentType" id="paymentType_manual" value="0" '.((!$this->autoPayment) ? 'checked="checked" ' : '').'/>
                            <label class="t" for="paymentType_manual"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
                        </td>
                    </tr>
                    <tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
                </table>
            </fieldset>
        </form>';
    }

    public function getContent()
    {
        $this->_html = '<h2>'.$this->displayName.'</h2>';

        if (!empty($_POST))
        {
            $this->_postValidation();
            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors AS $err)
                    $this->_html .= '<div class="alert error">'. $err .'</div>';
        }
        else
            $this->_html .= '<br />';

        $this->_displayPayWay();
        $this->_displayForm();

        return $this->_html;
    }

    public function hookPayment($params)
    {
        global $smarty;

        $address = new Address(intval($params['cart']->id_address_invoice));
        $customer = new Customer(intval($params['cart']->id_customer));
        $currency = $this->getCurrency();
        $total = number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3), $currency), 2, ',', '');
        $autoPaymentString = $this->autoPayment ? 'auto' : 'manual';

        if ((!Validate::isLoadedObject($address)) OR (!Validate::isLoadedObject($customer)))
            return $this->l('T-Com Pay Way error: (invalid address or customer)');

        $signature = md5($this->shopId . $this->secretKey . $params['cart']->id . $this->secretKey . $total . $this->secretKey);

        $smarty->assign(array(
            'this_path' => $this->_path,
            'paywayUrl' => 'https://pgw.t-com.hr/payment.aspx',
            'shopId' => $this->shopId,
            'cartId' => intval($params['cart']->id),
            'total' => $total,
            'returnUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/payway/validation.php',
            'cancelUrl' => 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'order.php',
            'signature' => $signature,
            'address' => $address,
            'addressFirstname' => pw_removeDiacritics($address->firstname),
            'addressSurname' => pw_removeDiacritics($address->lastname),
            'addressAddress' => pw_removeDiacritics($address->address1),
            'addressCity' => pw_removeDiacritics($address->city),
            'customer' => $customer,
            'autoPayment' => $autoPaymentString
        ));

        return $this->display(__FILE__, 'payment.tpl');
    }

    public function getTranslation($key)
    {
        $translations = array(
            'missingParameters' => $this->l('Invalid T-Com Pay Way return feedback (missing parameters)'),
            'invalidSignature' => $this->l('Invalid T-Com Pay Way return feedback (invalid signature)'),
            'transactionId' => $this->l('T-Com Pay Way transaction ID:'),
            'cardType' => $this->l('Card type:')
        );
        return $translations[$key];
    }
}

?>

<p class="payment_module">
    <a href="javascript:$('#payway_form').submit();" title="{l s='Pay by T-Com Pay Way' mod='payway'}">
        <img src="{$this_path}payway.gif" alt="{l s='Pay by T-Com Pay Way' mod='payway'}" />
        {l s='Pay by T-Com Pay Way' mod='payway'}
    </a>
</p>

<form action="{$paywayUrl}" method="post" id="payway_form" class="hidden">
    <input type="hidden" name="ShopID"            value="{$shopId}" />
    <input type="hidden" name="ShoppingCartID"    value="{$cartId}" />
    <input type="hidden" name="TotalAmount"       value="{$total}" />
    <input type="hidden" name="ReturnURL"         value="{$returnUrl}" />
    <input type="hidden" name="CancelURL"         value="{$cancelUrl}" />
    <input type="hidden" name="Lang"              value="HR" />
    <input type="hidden" name="Curr"              value="" />
    <input type="hidden" name="Signature"         value="{$signature}" />
    <input type="hidden" name="CustomerFirstname" value="{$addressFirstname}" />
    <input type="hidden" name="CustomerSurname"   value="{$addressSurname}" />
    <input type="hidden" name="CustomerAddress"   value="{$addressAddress}" />
    <input type="hidden" name="CustomerCity"      value="{$addressCity}" />
    <input type="hidden" name="CustomerZIP"       value="{$address->postcode}" />
    <input type="hidden" name="CustomerCountry"   value="Hrvatska" />
    <input type="hidden" name="CustomerPhone"     value="{$address->phone}" />
    <input type="hidden" name="CustomerEmail"     value="{$customer->email}" />
    <input type="hidden" name="PaymentType"       value="{$autoPayment}" />
    <input type="hidden" name="Installments"      value="Y" />
</form>

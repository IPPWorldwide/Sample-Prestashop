{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}

<section>
  <script>
    var payment_settings = {
      "payw_failed_payment"       :   "Payment failed. Please try again.",
      "payw_cardholder"           :   "Card holder",
      "payw_cardno"               :   "Card number",
      "payw_expmonth"             :   "Expmonth",
      "payw_expyear"              :   "Expyear",
      "payw_cvv"                  :   "CVV",
      "payw_confirmPayment"       :   "Knap",
      "payw_confirmPayment_btn"   :   "Confirm Payment",
      "waiting_icon"              :   "/modules/ps_ippgateway/views/img/spinner.gif",
    };
    var payment_hooks = {
        "confirmation_function"    : "payment_approved",
        "js-notifications"         : "PaymentListener"
    }
  </script>
      <h1>Payment Details</h1>
      <script src="https://pay.ippeurope.com/pay_new.js?checkoutId={$checkoutId}&cryptogram={$cryptogram}"></script>
      <form role="form" action="#" id="ippgateway-card-payment" class="ippgateway-payment-form paymentWidgets" data-brands="VISA MASTER" data-theme="divs" method="post">
      </form>
</section>

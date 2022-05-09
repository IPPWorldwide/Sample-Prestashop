let payment_status;
let processing_button;
let submitInitialText;
function PaymentListener(status) {
    console.log(status);
    if(status === "failed") {
        $(processing_button).prop('disabled', false);
        $(processing_button).text(submitInitialText);
    }
}

$(function() {
    let $form = '';
    const initIPP = async () => {

        const $submit = $('#payment-confirmation button[type="submit"]');
        const $submitButtons = $('#payment-confirmation button[type="submit"], .stripe-submit-button');
        submitInitialText = $submitButtons.first().text();

        let card;

        card = $("#ippgateway-payment-method");

        $form = $('#ippgateway-card-payment');

        // Disabled card form (enter button)
        $form.on('submit', (event) => {
            event.preventDefault();
        });

        $submit.click(async event => {
            if (!$('.ippgateway-payment-form:visible').length) {
                return true;
            }
            event.preventDefault();

            /* Prestashop 1.7 */
            $form = $('.ippgateway-payment-form:visible');
            payment = $('input[name="ippgateway-payment-method"]', $form).val();
            id_payment_method = $('input[name="ippgateway-payment-method"]', $form).data('id_payment_method');
            disableText = event.currentTarget;
            cardFormPayment = $('input[data-module-name="ippgateway_official"]').is(':checked');


            ConfirmPayment();

            disableSubmit(disableText, "Processing ...");

            event.stopPropagation();

            return false;
        });

        function disableSubmit(element, text) {
            console.log("Yes Disable Submit");
            $(element).prop('disabled', true);
            $(element).text(text ? text : submitInitialText);
            processing_button = element;
        }
    }

    IPP_PaymentWindow = initIPP();

    const observer = new MutationObserver((mutations) => {
        $.each(mutations, function(i, mutation) {
            const addedNodes = $(mutation.addedNodes);
            const selector = '#ippgateway-card-payment';
            const filteredEls = addedNodes.find(selector).addBack(selector);
            if (filteredEls.length) {
                initIPP();
            }
        })
    });

    observer.observe(document.body, {childList: true, subtree: true});
});
$(document).ready(function(){
    BackToBackActivatePaymentStatus();
    BackToBackPaymentListener.register('status',BackToBackPaymentStatus);
});

$(function () {
    if (!window.accessToken) {
        alert('No access token is available');

        return;
    }

    VivaPayments.cards.setup({
        baseURL: 'https://demo.vivapayments.com/3ds/services',
        authToken: window.accessToken,
        amount: 1034,
        cardHolderAuthOptions: {
            cardHolderAuthPlaceholderId: 'threed-pane',
            cardHolderAuthInitiated: function () {
                $('#threed-pane').show();
            },
            cardHolderAuthFinished: function () {
                $('#threed-pane').hide();
            }
        },
        installmentsHandler: function (response) {
            if (response.Error ||
              response.MaxInstallments <= 1) {
                $('.js-installments').hide();

                return; 
            }

            for (i = 1; i <= response.MaxInstallments; i++) {
                $('.js-installments select').append(
                    $('<option>').val(i).text(i));
            }

            $('.js-installments').show();
        }
    });

    $('.js-btn-chargecard').on('click', function () {
        let options = {
            installments: 1
        };
        let installments = $('.js-installments select').val();

        if (installments >= 1) {
            options.installments = installments;
        }

        VivaPayments.cards.requestToken(options)
            .done(function (data) {
                if (data.Error) {
                    return;
                }

                let paymentForm = $('#native-checkout-form');
                $('.js-charge-token').val(data.chargeToken);

                $.ajax({
                    url: paymentForm.attr('action'),
                    type: 'POST',
                    data: paymentForm.serialize()
                }).done(function (responseData) {
                    if (responseData && responseData.statusId === 'F') {
                        $('.js-charge-result').html(
                            `Charge was successful. Transaction Id ${responseData.transactionId}`)
                            .addClass('alert-success')
                            .show();
                    }
                }).fail(function (xhrData) {
                    $('.js-charge-result')
                        .html('Card could not be charged')
                        .addClass('alert-danger')
                        .show();
                    });
            });
    });
});
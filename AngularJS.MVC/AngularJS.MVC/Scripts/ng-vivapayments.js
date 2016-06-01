//Private Stack
(function (window, angular, $) {

    var ngVivaPaymentsMod = angular.module('ngVivaPaymentsApp', []);

    ngVivaPaymentsMod.directive('ngVivapayment', function ($timeout) {
        return {
            restrict: 'AE',
            template: '<form id="frmCheckout" action="/Home/Checkout" method="post">' +
                      '<button class="form-control btn btn-primary" type="button"' +
                      'data-vp-publickey="aBon7Oqqme4i/d6kPz3ICnIm/KEFXnavmpAGMR+dtK0="' +
                      'data-vp-baseurl="https://demo.vivapayments.com"' +
                      'data-vp-lang="{{culture}}"' +
                      'data-vp-amount="{{totalCost * 100}}"' +
                      'data-vp-description="My product" />' +
                      '</form>',
            link: function ($scope, element) {
                $(element).submit(function (event) {
                    event.preventDefault();
                    var vivaWalletToken = $('input[name="vivaWalletToken"]', $('#frmCheckout')).val();
                    $.ajax({
                        method: 'POST',
                        url: '/Home/Checkout',
                        data: { vivaWalletToken: vivaWalletToken },
                        success: function (response) {
                            $timeout(function () {
                                $scope.step = null;
                                window.location.href = "/";
                            }, 0);
                        }
                    }).fail(function (error) {
                        $timeout(function () {
                            alert('Failed');
                        }, 0);
                    });
                });
            }
        };
    });

    ngVivaPaymentsMod.controller("VivaPaymentsCtlr", ['$scope', '$timeout', function ($scope, $timeout) {

        $scope.step = 1;
        $scope.culture = 'el'; // en

        $scope.step2 = function () {
            $.getScript("Scripts/viva.js") // Using customized js. Default: https://demo.vivapayments.com/web/checkout/js
                .done(function (script, textStatus) {
                    
                });
            $scope.step = 2;
        }

    }]);

    $(document).ready(function () {
        $('.loader').show();
    });

})(window, angular, $);
﻿//Private Stack
(function (window, angular, $) {

    var ngVivaPaymentsMod = angular.module('ngVivaPaymentsApp', []);

    ngVivaPaymentsMod.directive('postform', function ($timeout) {
        return {
            restrict: 'A',
            link: function ($scope, element, $attr) {
                $(element).submit(function (event) {
                    event.preventDefault();
                    var vivaWalletToken = $('input[name="vivaWalletToken"]', $('#frmCheckout')).val();
                    $.ajax({
                        method: 'POST',
                        url: '/Home/Checkout',
                        data: { vivaWalletToken: vivaWalletToken },
                        success: function (response) {
                            $timeout(function () {
                                alert('Success');
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
        }
    });

    ngVivaPaymentsMod.controller("VivaPaymentsCtlr", ['$scope', '$timeout', function ($scope, $timeout) {
        
        $scope.culture = 'el';
        $scope.step2 = function () {
            $scope.step = 2;
        }

    }]);

})(window, angular, $);
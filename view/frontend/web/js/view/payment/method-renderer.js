define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'efecty',
                component: 'Burst_Efecty/js/view/payment/method-renderer/efecty'
            }
        );
        return Component.extend({});
    }
);
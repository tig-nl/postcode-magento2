/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to support@postcodeservice.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact support@postcodeservice.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
/* jshint ignore:start */
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function (
    $,
    wrapper,
    quote
) {
    'use strict';

    return function (setBillingAddressAction) {
        return wrapper.wrap(setBillingAddressAction, function (originalAction) {
            var billingAddress = quote.billingAddress();

            if (billingAddress === undefined || !billingAddress) {
                return originalAction();
            }

            if (billingAddress.customAttributes === undefined) {
                return originalAction();
            }

            if (billingAddress['extension_attributes'] === undefined) {
                billingAddress['extension_attributes'] = {};
            }

            // < M2.3.0
            if (billingAddress.customAttributes !== undefined && billingAddress.customAttributes.tig_housenumber !== undefined) {
                billingAddress['extension_attributes']['tig_housenumber']          = billingAddress.customAttributes.tig_housenumber;
                billingAddress['extension_attributes']['tig_housenumber_addition'] = billingAddress.customAttributes.tig_housenumber_addition;
                return originalAction();
            }
            // >= M2.3.0
            if (billingAddress.customAttributes.length > 0) {
                billingAddress.customAttributes.forEach(function(customAttribute) {
                    if(customAttribute.attribute_code === 'tig_housenumber') {
                        billingAddress['extension_attributes']['tig_housenumber']           = customAttribute.value;
                    } else if (customAttribute.attribute_code === 'tig_housenumber_addition') {
                        billingAddress['extension_attributes']['tig_housenumber_addition']  = customAttribute.value;
                    }
                });
            }

            return originalAction();
        });
    };
});
/* jshint ignore:end */

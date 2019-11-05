<?php
/**
 * @package Modulebazaar_PayForCOD
 */

namespace Modulebazaar\PayForCOD\Api;

/**
 * Interface PayForCODInterface
 *
 * @package Modulebazaar\PayForCOD\Api
 */
interface PayForCODInterface
{
    /**
     * @param  int $orderId
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[] Array of payment methods
     */
    public function placeOrder($orderId);
}

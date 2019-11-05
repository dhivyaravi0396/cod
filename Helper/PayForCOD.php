<?php
/**
 * @package Modulebazaar_PayForCOD
 */

namespace Modulebazaar\PayForCOD\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;

/**
 * Class PayForCOD
 *
 * @package Modulebazaar\PayForCOD\Helper
 */
class PayForCOD extends AbstractHelper
{
    /**
     * @var OrderRepositoryInterfaceFactory
     */
    public $orderRepositoryInterfaceFactory;

    /**
     * @var DateTime
     */
    public $dateTime;

    /**
     * @var string
     */
    const ORDER_COMPLETE = 'complete';

    /**
     * PayForCOD constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory
     * @param DateTime $dateTime
     * @param Data $data
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory,
        DateTime $dateTime,
        Data $data
    ) {
    
        $this->orderRepositoryInterfaceFactory = $orderRepositoryInterfaceFactory;
        $this->dateTime = $dateTime;
        $this->scopeConfig = $scopeConfig;
        $this->data = $data;
        parent::__construct($context);
    }

    /**
     * Check whether the Order Can have Pay COD
     *
     * @param $orderId
     * @return bool
     */
    public function canPayForCOD($orderId)
    {
        $orderRepository = $this->orderRepositoryInterfaceFactory->create();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $statusData= $this->scopeConfig->getValue("payforcod/general_configuration/enable_on_type", $storeScope);
        $statusArray=explode(",", $statusData);
        $order = $orderRepository->get($orderId);

        if (($order->getStatus() != self::ORDER_COMPLETE) && ($order->getPayment()->getMethod()=='cashondelivery') && (in_array($order->getStatus(), $statusArray))) {
            $status = true;
        } else {
            $status = false;
        }

        return $status;
    }
}

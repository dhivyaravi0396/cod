<?php
/**
 * @package Modulebazaar_PayForCOD
 * @author  Sai Ram sairam@egrovesystems.com
 */

namespace Modulebazaar\PayForCOD\Plugin;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Modulebazaar\PayForCOD\Helper\Data;
use Modulebazaar\PayForCOD\Helper\PayForCOD;

/**
 * Class OrderRepositoryPlugin
 *
 * @package Modulebazaar\PayForCOD\Plugin
 */
class OrderRepositoryPlugin
{
    /**
     * @var OrderExtensionFactory
     */
    public $orderExtensionFactory;

    /**
     * @var Data
     */
    public $data;

    /**
     * @var DateTime
     */
    public $dateTime;

    /**
     * @var PayForCOD
     */
    public $payforCOD;

    /**
     * OrderRepositoryPlugin constructor.
     *
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param Data $data
     * @param DateTime $dateTime
     * @param PayForCOD $payforCODObject
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        Data $data,
        DateTime $dateTime,
        PayForCOD $payforCODObject
    ) {
    
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->data = $data;
        $this->dateTime = $dateTime;
        $this->payforCOD = $payforCODObject;
    }

    /**
     * Plugin on GET LIST To set Paynow Status
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $orderSearchResult
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\Data\OrderSearchResultInterface $orderSearchResult
    ) {
    
        if ($this->data->getModuleStatus()) {
            /** @var  \Magento\Sales\Api\Data\OrderInterface[] $orderItems */
            $orders = $orderSearchResult->getItems();
            foreach ($orders as $order) {
                /** @var  \Magento\Sales\Api\Data\orderExtension $extensionAttributes */
                $extensionAttributes = $order->getExtensionAttributes();
                // check status of the order
                if ($status = $this->payforCod->canPayForCOD($order->getEntityId())) {
                    $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
                    $extensionAttributes->setPayForCODStatus($status);
                    $order->setExtensionAttributes($extensionAttributes);
                }
            }
        }

        return $orderSearchResult;
    }
}

<?php

namespace Modulebazaar\PayForCOD\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Psr\Log\LoggerInterface;

class ChangeStatusOrderPlaced implements ObserverInterface
{

    public function __construct(
        OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory,
        CartRepositoryInterfaceFactory $cartRepositoryInterfaceFactory,
        \Magento\Customer\Model\Session $customerSession,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->cartRepositoryInterfaceFactory = $cartRepositoryInterfaceFactory;
        $this->orderRepositoryInterfaceFactory = $orderRepositoryInterfaceFactory;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $pay_status = $this->getCustomerSession()->getPayCODStatus();
        $parent_orderId = $this->getCustomerSession()->getParrentCODOrderId();
        $parent_cartId = $this->getCustomerSession()->getParrentCODCartId();
        $this->logger->info("ChangeStatus PayCOD Observer Called ");
        $this->logger->info("Pay Status" . $pay_status);
        $this->logger->info("Parent OrderId " . $parent_orderId);
        $this->logger->info("Parent CartId " . $parent_cartId);
        if (isset($pay_status) && ($pay_status == 1)) {
            $orderRepository = $this->orderRepositoryInterfaceFactory->create();
            $orderDetails = $orderRepository->get($parent_orderId);
            $orderDetails->setStatus('pay_cod');
            $orderDetails->setState('pay_cod_state');
            $orderRepository->save($orderDetails);
            if (isset($parent_cartId) && !empty($parent_cartId)) {
                $cartRepository = $this->cartRepositoryInterfaceFactory->create();
                $cartInfo = $cartRepository->get($parent_cartId);
                $cartInfo->setIsActive(1);
                $cartRepository->save($cartInfo);
            }

            $this->getCustomerSession()->unsPayCODStatus();
            $this->getCustomerSession()->unsParrentCODOrderId();
            $this->getCustomerSession()->unsParrentCODCartId();
            $this->logger->info("Status saved by payCOD");
        }
    }

    public function getCustomerSession()
    {
        return $this->customerSession;
    }
}

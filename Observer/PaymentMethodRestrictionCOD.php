<?php

namespace Modulebazaar\PayForCOD\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Psr\Log\LoggerInterface;

class PaymentMethodRestrictionCOD implements ObserverInterface
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
        $this->logger->info("Payment Restriction PayCOD Observer Called ");
        $this->logger->info("Pay Status" . $pay_status);
        $this->logger->info("Parent OrderId " . $parent_orderId);
        $this->logger->info("Parent CartId " . $parent_cartId);
        if (isset($pay_status) && ($pay_status == 1)) {
            $orderRepository = $this->orderRepositoryInterfaceFactory->create();
            $orderDetails = $orderRepository->get($parent_orderId);
            if ($observer->getEvent()->getMethodInstance()->getCode()=="cashondelivery") {
                $result = $observer->getEvent()->getResult();
                $result->setData('is_available', false);
            }

            $this->logger->info("Restriction of payment done by PayCOD");
        }
    }

    public function getCustomerSession()
    {
        return $this->customerSession;
    }
}

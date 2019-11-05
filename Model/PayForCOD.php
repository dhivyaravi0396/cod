<?php


namespace Modulebazaar\PayForCOD\Model;

use Modulebazaar\PayForCOD\Api\PayForCODInterface;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Psr\Log\LoggerInterface;
use Modulebazaar\PayForCOD\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\PaymentMethodManagementInterfaceFactory;

/**
 * Class PayForCOD
 *
 * @package Modulebazaar\PayForCOD\Model
 */
class PayForCOD implements PayForCODInterface
{
    const SET_INACTIVE = 0;

    /**
     * @var OrderRepositoryInterfaceFactory
     */
    public $orderRepositoryInterfaceFactory;

    /**
     * @var CartRepositoryInterfaceFactory
     */
    public $cartRepositoryInterfaceFactory;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var PaymentMethodManagementInterfaceFactory
     */
    public $paymentMethodManagementInterfaceFactory;

    /**
     * PayForCOD constructor.
     *
     * @param OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory
     * @param CartRepositoryInterfaceFactory $cartRepositoryInterfaceFactory
     * @param LoggerInterface $logger
     * @param Data $data
     */
    public function __construct(
        OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory,
        CartRepositoryInterfaceFactory $cartRepositoryInterfaceFactory,
        LoggerInterface $logger,
        Data $data,
        \Magento\Customer\Model\Session $customerSession,
        PaymentMethodManagementInterfaceFactory $paymentMethodManagementInterfaceFactory
    ) {
        $this->orderRepositoryInterfaceFactory = $orderRepositoryInterfaceFactory;
        $this->cartRepositoryInterfaceFactory = $cartRepositoryInterfaceFactory;
        $this->logger = $logger;
        $this->data = $data;
        $this->customerSession = $customerSession;
        $this->paymentMethodManagementInterfaceFactory = $paymentMethodManagementInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function placeOrder($orderId)
    {
        //checks whether module enable or disable
        if ($this->checkModuleEnabled()) {
            /** @var \Magento\Sales\Api\OrderRepository $orderRepository */
            $orderRepository = $this->orderRepositoryInterfaceFactory->create();
            /** @var \Magento\Sales\Api\Data\OrderInterface $orderDetails */
            $orderDetails = $orderRepository->get($orderId);
            $cartId = (int)$orderDetails->getQuoteId();
            $paymentManagment = $this->paymentMethodManagementInterfaceFactory->create();
            $paymentStatus = $paymentManagment->getList($cartId);
            $this->logger->info("Payment count " . count($paymentStatus));
            if (count($paymentStatus) == 1) {
                foreach ($paymentStatus as $paymentStateObject) {
                    if ($paymentStateObject->getCode() == 'cashondelivery') {
                        $this->logger->info("Payment Method Not Available");
                        return false;
                    }
                }
            }

            $customerId = (int)$orderDetails->getCustomerId();
            $quoteStatus = $this->makeQuoteActive($cartId, $customerId);
            /**
             * setting Pay now status
             * this shows the order has been tried to make re payment
             */
            $this->getCustomerSession()->setPayCODStatus(1);
            $this->getCustomerSession()->setParrentCODOrderId($orderId);

            if ($quoteStatus) {
                $paymentManagment = $this->paymentMethodManagementInterfaceFactory->create();
                try {
                    /** @return \Magento\Quote\Api\PaymentMethodManagementInterface */
                    return $paymentManagment->getList($cartId);
                } catch (\Exception $e) {
                    throw new LocalizedException(
                        new Phrase(
                            $e->getMessage()
                        )
                    );
                }
            }
        } else {
            $this->logger->info("ChangeStatus Payforcod module not enabled ");
        }
    }

    /**
     * @return bool
     */
    public function checkModuleEnabled()
    {
        return $this->data->getModuleStatus();
    }

    /**
     * @param int $cartId
     * @param int $customerId
     */
    public function makeQuoteActive($cartId, $customerId)
    {
        /** @var \Magento\Quote\Api\CartRepositoryInterface $cartRepository */
        $cartRepository = $this->cartRepositoryInterfaceFactory->create();
        try {
            // get Active Quote of Customer and make inactive
            $activeCartId = $cartRepository->getActiveForCustomer($customerId);
            $id = $activeCartId->getId();
            if (isset($id)) {
                $this->getCustomerSession()->setParrentCODCartId($activeCartId->getId());
            }

            $activeCartId->setData('is_active', 0);
            $cartRepository->save($activeCartId);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }

        try {
            /** @var \Magento\Quote\Api\Data\CartInterface $cartInfo */
            $cartInfo = $cartRepository->get($cartId);
            $cartInfo->setIsActive(1);
            $cartRepository->save($cartInfo);
        } catch (\Exception $e) {
            throw new LocalizedException(
                new Phrase(
                    $e->getMessage()
                )
            );
        }

        return true;
    }

    public function getCustomerSession()
    {
        return $this->customerSession;
    }
}

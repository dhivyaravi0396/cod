<?php
/**
 * @package Modulebazaar_PayForCOD
 */

namespace Modulebazaar\PayForCOD\Controller\PayForCOD;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\State;

use Modulebazaar\PayForCOD\Api\PayForCODInterfaceFactory;

/**
 * Class Index
 *
 * @package Modulebazaar\PayForCOD\Controller\PayForCOD
 */
class Index extends Action
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    public $resultFactory;

    /**
     * @var PayForCODInterfaceFactory
     */
    public $payForCODInterfaceFactory;

    /**
     * @var State
     */
    public $state;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ResultFactory $resultFactory,
        PayForCODInterfaceFactory $payForCODInterfaceFactory,
        State $state
    ) {
        $this->resultFactory = $resultFactory;
        $this->payForCODInterfaceFactory = $payForCODInterfaceFactory;
        $this->state = $state;
        parent::__construct($context);
    }

    public function execute()
    {
        $requestParams = $this->getRequest()->getParams();
        $orderId = $requestParams['order_id'];
        $payNowManagementInterface = $this->payForCODInterfaceFactory->create();
        try {
            $payNowStatus = $payNowManagementInterface->placeOrder($orderId);
            if (!empty($payNowStatus)) {
                $this->_redirect('checkout', array('_fragment' => 'payment'));
            } else {
                if ($payNowStatus == false) {
                    $this->messageManager->addError(__("Online Payment not available in Website"));
                } else {
                    $this->messageManager->addError(__("Pay For COD not Available Contact Support Team"));
                }

                $this->_redirect('sales/order/history');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $this->_redirect('sales/order/history');
        }
    }
}

<?php
/**
 * @package Modulebazaar_PayForCOD
 * @author  Sai Ram sairam@egrovesystems.com
 */

namespace Modulebazaar\PayForCOD\Setup;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

/**
 * Class UpgradeData
 * @package Modulebazaar\PayForCOD\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Pay COD Order-State code
     */
    const ORDER_STATE_CUSTOM_CODE = 'pay_cod_state';

    /**
     * Pay COD  Order-Status code
     */
    const ORDER_STATUS_CUSTOM_CODE = 'pay_cod';

    /**
     * Pay COD  Order-Status label
     */
    const ORDER_STATUS_CUSTOM_LABEL = 'Pay COD';

    /**
     * Status Factory
     *
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * Status Resource Factory
     *
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    /**
     * UpgradeData constructor.
     *
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
    
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     *
     * @throws Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addNewOrderStateAndStatus($setup);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('sales_order_status_state'),
                array('visible_on_front' => 0),
                array('state = ?' => 'pay_cod_state')
            );
        }
    }

    /**
     * Create new custom order status and assign it to the new custom order state
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addNewOrderStateAndStatus($setup)
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData(
            array(
            'status' => self::ORDER_STATUS_CUSTOM_CODE,
            'label' => self::ORDER_STATUS_CUSTOM_LABEL
            )
        );

        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }

        $status->assignState(self::ORDER_STATE_CUSTOM_CODE, true, true);
    }
}

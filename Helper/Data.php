<?php
/**
 * @package Modulebazaar_PayForCOD
 * @author  Sai Ram sairam@egrovesystems.com
 */

namespace Modulebazaar\PayForCOD\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Modulebazaar\PayForCOD\Helper
 */
class Data extends AbstractHelper
{
    /** @var string */
    const XML_PATH_PAY_NOW = 'payforcod/';

    /** @var string */
    const GENERAL_CONFIGURATION = 'general_configuration/';

    /** @var string */
    const MODULE_STATUS_FIELD = 'enable';

    /**
     * Data constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context
    ) {
    
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get Config Values
     *
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * GET General Config
     *
     * @param $code
     * @param null $storeId
     * @return string
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(
            self::XML_PATH_PAY_NOW
            . self::GENERAL_CONFIGURATION . $code, $storeId
        );
    }

    /**
     * Get Module Status
     *
     * @paramgetModuleStatus null $storeId
     * @return bool
     */
    public function getModuleStatus($storeId = null)
    {
        if ($this->getConfigValue(
            self::XML_PATH_PAY_NOW
            . self::GENERAL_CONFIGURATION . self::MODULE_STATUS_FIELD, $storeId
        )) {
            return true;
        } else {
            return false;
        }
    }
}

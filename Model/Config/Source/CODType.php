<?php

namespace Modulebazaar\PayForCOD\Model\Config\Source;

class CODType implements \Magento\Framework\Option\ArrayInterface
{

    public function __construct(
        \Magento\Sales\Model\Config\Source\Order\Status $status
    ) {
    
        $this->status = $status;
    }

    public function toOptionArray()
    {
        $statusArray=$this->status->toOptionArray();

        $arrayList = array(
            array('value' => 'pending', 'label' => 'Pending'),
            array('value' => 'processing', 'label' => 'Processing')
        );
        return $arrayList;
    }
}

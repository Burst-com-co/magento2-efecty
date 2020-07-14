<?php

namespace Burst\Efecty\Model;

class Efecty extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = 'burst_efecty';
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Burst\Efecty\Model\ResourceModel\Efecty');
    }
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
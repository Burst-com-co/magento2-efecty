<?php
namespace Burst\Efecty\Model\ResourceModel\Efecty;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Burst\Efecty\Model\Efecty',
            'Burst\Efecty\Model\ResourceModel\Efecty'
        );
    }
}
<?php

namespace Burst\Efecty\Model\Payment;
/**
 * Pay In Store payment method model
 */
class Efecty extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'efecty';
    
    /**
     * Method is avalaible
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return boolean
     */
    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $conf = $objectManager
                ->get('Magento\Framework\App\Config\ScopeConfigInterface')
                ->getValue('payment/burst_efecty/active');
        $active = (int)$conf;
        if ($active==1) {
            return true;
        } else {
            return false;
        }
    }   
}
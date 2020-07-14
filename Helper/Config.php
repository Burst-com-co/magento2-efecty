<?php
namespace Burst\Efecty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper{
    public function __construct( 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager)
	{
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }
    /**
     * Get store ID
     *
     * @return string
     */
    public function getStoreID(){
        return $this->_storeManager->getStore()->getId();
        
    }
    /**
     * Get store name
     *
     * @return string
     */
    public function getStorename(){
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );   
    }
    /**
     * Get default store email
     *
     * @return string
     */
    public function getStoreEmail(){
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get expiration time in days
     *  
     * @return int
     */
    public function getExpirationTime()
    {
        return ceil($this->scopeConfig->getValue('payment/burst_efecty/expiration', ScopeInterface::SCOPE_STORE));
    }
    /**
     * Get activate status from modue 
     * 
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->scopeConfig->getValue('payment/burst_efecty/active', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get module title
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->scopeConfig->getValue('payment/burst_efecty/title', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get description from module
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->scopeConfig->getValue('payment/burst_efecty/description', ScopeInterface::SCOPE_STORE);
    }
    /**
     * Get Efecty account ID
     * 
     * @return string|null
     */
    public function getAccountID()
    {
        return $this->scopeConfig->getValue('payment/burst_efecty/account_id', ScopeInterface::SCOPE_STORE);
    }
    /**
     * Get Efecty trankey
     * 
     * @return string|null
     */
    public function getKey()
    {
        return $this->scopeConfig->getValue('payment/burst_efecty/key', ScopeInterface::SCOPE_STORE);
    }
    /**
     * Get Efecty login
     * 
     * @return string|null
     */
    public function getLogin()
    {
        return $this->scopeConfig->getValue('payment/burst_efecty/login', ScopeInterface::SCOPE_STORE);
    }
     /**
     * Get default Efecty soap endpoint
     * 
     * @return string
     */
    public static function getDefaultEndpoint()
    {
        return 'https://api.payulatam.com/';
    }
    /**
     * Get default store URL
     *
     * @param boolean $fromStore
     * @return string
     */
    public function getDefaultStoreUrl($fromStore = true)
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
    /**
     * Get Copy email address
     *
     * @return string
     */
    public function getCopyAddressEmail()
    {
        return $this->scopeConfig->getValue('payment/burst_efecty/copy_to', ScopeInterface::SCOPE_STORE);
    }
    /**
     * Get email subject
     *
     * @return string
     */
    public function getEmailSubject()
    {
        $subject=$this->scopeConfig->getValue('payment/burst_efecty/subject', ScopeInterface::SCOPE_STORE);
        if (is_null($subject) || $subject=='') {
            return 'Payment Efecty - '. $this->getStorename();
        } else {
            return $subject;
        }
        
        
    }
}
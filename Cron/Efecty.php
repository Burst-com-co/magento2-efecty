<?php
namespace Burst\Efecty\Cron;

class Efecty
{
    protected $logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Burst\Efecty\Model\EfectyFactory $paymentLinkFactory,
        \Burst\Efecty\Helper\Payu $Payu) {
        $this->logger = $logger;
        $this->Payu=$Payu;
        $this->paymentLinkFactory=$paymentLinkFactory;
    }

    public function execute(){
        $model = $this->paymentLinkFactory->create();
        $collection = $model->getCollection()
            ->addFieldToFilter('valid_until', ['gteq' => date('Y-m-d H:i:s')])
            ->addFieldToFilter('status', ['eq' => 'CREATED']);
        foreach($collection as $item){
            $data=$item->getData();
            $payment_status=$this->Payu->getStatus($data['order_id']);
            if (!is_null($payment_status) && $data["id"]!=$payment_status) {
                $update = $model->load($data["id"]);
                $update->setStatus($payment_status);
                $update->save();
            }
        }
    }

}

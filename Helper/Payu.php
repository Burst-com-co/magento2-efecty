<?php
namespace Burst\Efecty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Payu extends AbstractHelper{
    //Helper vars
    protected $scopeConfig, $logger;
    //Payu var
    private $_description, $_serviceCode, $_serviceName, $_creationDate, $_dueDate, $_dueType, $_cutDate, $_currency, $_dueRate, $_devolutionBase;
    //CURL Vars
    private $curl, $base_url, $headers;
    /**
     * Construct
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,  
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Burst\Efecty\Model\EfectyFactory $paymentLinkFactory,
        \Burst\Efecty\Helper\Config $config)
	{
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->paymentLinkFactory = $paymentLinkFactory;
        $this->config = $config;
        $this->logger = $logger;
        $this->_description=$this->config->getDescription();
        $this->_serviceCode=1;
        $this->_serviceName=$this->config->getTitle();
        $this->_creationDate=  $this->sum_or_res_days_or_hours("+5 hours", date("Y-m-d h:i"));
        $this->_dueDate=  $this->sum_or_res_days_or_hours("+".$this->config->getExpirationTime()." hours", $this->_creationDate);
        $this->_dueType=0;
        $this->_cutDate=  $this->_dueDate;
        $this->_currency="COP";
        $this->_dueRate=0;
        $this->_devolutionBase=16;
        /**
         * CURL
         */
        $this->curl= curl_init();
        $this->base_url=$this->config->getDefaultEndpoint();
        // $this->base_url="https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi";
        $this->headers = array();
        $this->headers[] ="Content-Type: application/json";
        $this->headers[]="Accept: application/json";
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers); 
    }
    public function createLink($increment, $email, $value, $name, $tipo_pago)
    {
        $this->format_time($this->_dueDate);
        $this->get_signature($increment, $value);
        $this->setJson($increment, $value, $email, $name, $tipo_pago);
        $this->sendLink();
        $this->_reference=$increment;
        $respuesta_servicio=$this->respuestaCurl;
        $this->logger->addInfo('Efecty', ["Payu"=>\json_encode($respuesta_servicio)]);
        if($respuesta_servicio['code']==="SUCCESS" ||$respuesta_servicio['error']=== null){
            $order_id=$respuesta_servicio['transactionResponse']['orderId'];
            $transaction_id=$respuesta_servicio['transactionResponse']['transactionId'];
            $pdf_url=$respuesta_servicio['transactionResponse']['extraParameters']['URL_PAYMENT_RECEIPT_PDF'];
            $this->safeLinkData($this->_reference, $order_id, $transaction_id, $pdf_url, $email, $name, $value);
            $this->sendPayuEmail($increment, $email, $name, $pdf_url);

        }else{
            $this->logger->addInfo('Efecty', ["Payu"=>'Se produjo un error en la creacion del link, intente de nuevo']);
        }
    }
    private function format_time($date){
        $this->date_return=$date."T23:59:59";
    }
    private function get_signature($increment, $value){
        $this->signature=md5($this->config->getKey()."~"."513081"."~$increment~$value~COP");
    }
    private function setJson($increment, $value, $email,$name, $metodo){
        $this->array_json=[];
        $this->array_json['language']="es";
        $this->array_json['command']="SUBMIT_TRANSACTION";
        $this->array_json['merchant']=array(
            "apiLogin"=> $this->config->getLogin(), 
            "apiKey"=> $this->config->getKey());
        $this->array_json['transaction']=array(
            "order"=>array(
                "accountId"=>$this->config->getAccountID(),
                "referenceCode"=>"$increment", 
                "description"=>"Pago de pedido $increment", 
                "language"=>"es",
                "signature"=> $this->signature, 
                "additionalValues"=>array(
                    "TX_VALUE"=>array(
                        "value"=>$value, 
                        "currency"=>"COP"
                        )
                    ),
                "buyer"=>array(
                    "emailAddress"=>$email,
                    "fullName"=>$name
                )
                ),
            "type"=>"AUTHORIZATION_AND_CAPTURE",
            "paymentMethod"=> "$metodo",
            "expirationDate"=>$this->date_return,
            "ipAddress"=> $_SERVER['REMOTE_ADDR']
            );
        $this->array_json['test']=false;
        $this->json_link= json_encode($this->array_json);
    }
    private function sendLink() {
        curl_setopt($this->curl, CURLOPT_URL,$this->base_url."payments-api/4.0/service.cgi");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->json_link);  //Post Fields
        $this->respuestaCurl = json_decode(curl_exec ($this->curl),true);
        $this->json_link= json_encode($this->respuestaCurl);
    }
    private function safeLinkData($increment, $order_id, $transaction_id, $url_payment, $email, $name, $value){
        $data=[
            'increment_id'=>$increment,
            'order_id'=>$order_id,
            'amount'=>$value,
            'customer_email'=>$email,
            'customer_firstname'=>$name,
            'status'=>'CREATED',
            'requestId'=>$transaction_id,
            'payment_url'=>$url_payment,
            'valid_until'=>$this->_dueDate
        ];
        $model = $this->paymentLinkFactory->create();
		$model->addData($data);
        $saveData = $model->save();
    }
    private function sendPayuEmail($increment_id, $email, $name, $url)
    {
        try {
            $sentToEmail = $email;
            $sentToName = $name;
            $sender = [
                'name' => $this->config->getStorename(),
                'email' => $this->config->getStoreEmail()
            ];
            $this->mail($sender, $sentToEmail, $sentToName, $increment_id, $url);
            if (!\is_null($this->config->getCopyAddressEmail())) {
                $this->mail($sender, $this->config->getCopyAddressEmail(),'Seller',$increment_id, $url);
            }
        } catch (Exception $e) {
            $this->logger->addInfo('Efecty', ["Error"=>json_encode($e->getMessage())]);
        }
    }
    public function mail($sender, $sentToEmail, $sentToName, $increment, $url)
    {
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier('burst_efecty_custom_email_template')
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, /* here you can defile area and store of template for which you prepare it */
                    'store' => $this->config->getStoreID(),
                ]
            )
            //Email template variables
            ->setTemplateVars(
                [
                    'name'=> $sentToName,
                    'store_name'=>$this->config->getStorename(),
                    'reference'=>$increment,
                    'process_url'=>$url,
                    'email_subject'=>$this->config->getEmailSubject(),
                ]
            )
            ->setFrom($sender)
            ->addTo($sentToEmail, $sentToName)
            ->getTransport();
        $transport->sendMessage();
    }
    private function sum_or_res_days_or_hours($sum_or_rest, $date) {
        $date_transform=date('Y-m-d', strtotime($sum_or_rest, strtotime($date)));
        return $date_transform;
    }
    /**
      * Get payment status from Pay U
      * @param type $order_id 
      * @return type
      */
    public function getStatus($order_id) {
        $this->order_id=(int)$order_id;
        $json=$this->setStatusJson();
        $respuestaCurl=$this->sendOrderForData($json);
        $numero_arreglos=count($respuestaCurl["result"]["payload"]["transactions"]);
        return $respuestaCurl["result"]["payload"]["transactions"][$numero_arreglos-1]["transactionResponse"]["state"];
    }
    /**
     * Set Pay U json structure
     * @return type
     */
    private function setStatusJson(){
        $array_json=[];
        $array_json['test']=false;
        $array_json['language']="es";
        $array_json['command']="ORDER_DETAIL";
        $array_json['merchant']=array(
            "apiLogin"=> $this->config->getLogin(), 
            "apiKey"=> $this->config->getKey());
        $array_json['details']=array(
            "orderId"=> $this->order_id
        );
        return json_encode($array_json);
    } 
    /**
     * Send info to Pay to search status.
     * @return type
     */
    private function sendOrderForData($array_json) {
        try {
            $this->API_Key=$this->config->getKey();
            $this->API_Login=$this->config->getLogin();
            $this->base_url=$this->config->getDefaultEndpoint()."reports-api/4.0/service.cgi";
            $this->headers = array();
            $this->headers[] ="Content-Type: application/json";
            $this->headers[]="Accept: application/json";
            $this->curl= curl_init();
            curl_setopt($this->curl, CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
            curl_setopt($this->curl, CURLOPT_URL,$this->base_url);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $array_json);  //Post Fields
            return json_decode(curl_exec($this->curl),true);
        } catch (Exception $e) {
            return \json_encode( $e->getMessage());
        }
        
    }
}
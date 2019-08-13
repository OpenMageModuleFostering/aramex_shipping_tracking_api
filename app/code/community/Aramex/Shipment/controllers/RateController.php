<?php
class Aramex_Shipment_RateController extends Mage_Adminhtml_Controller_Action {
	
	 public function postAction(){
		$account=Mage::getStoreConfig('aramexsettings/settings/account_number');		
		$country_code=Mage::getStoreConfig('aramexsettings/settings/account_country_code');
		$post = $this->getRequest()->getPost();
		$country = Mage::getModel('directory/country')->loadByCode($country_code);		
		$response=array();
				
		$clientInfo = Mage::helper('aramexshipment')->getClientInfo();		
		try {
				if (empty($post)) {					
					$response['type']='error';
					$response['error']=$this->__('Invalid form data.');
					print json_encode($response);		
					die();
				}
		$params = array(
		'ClientInfo'  			=> $clientInfo,
								
		'Transaction' 			=> array(
									'Reference1'			=> $post['reference'] 
								),
								
		'OriginAddress' 	 	=> array(
									'StateOrProvinceCode'	=>html_entity_decode($post['origin_state']),
									'City'					=> html_entity_decode($post['origin_city']),
									'PostCode'				=>$post['origin_zipcode'],
									'CountryCode'				=> $post['origin_country']
								),
								
		'DestinationAddress' 	=> array(
									'StateOrProvinceCode'	=>html_entity_decode($post['destination_state']),
									'City'					=> html_entity_decode($post['destination_city']),
									'PostCode'				=>$post['destination_zipcode'],
									'CountryCode'			=> $post['destination_country'],
								),
		'ShipmentDetails'		=> array(
									'PaymentType'			 => $post['payment_type'],
									'ProductGroup'			 => $post['product_group'],
									'ProductType'			 => $post['service_type'],
									'ActualWeight' 			 => array('Value' => $post['text_weight'], 'Unit' => $post['weight_unit']),
									'ChargeableWeight' 	     => array('Value' => $post['text_weight'], 'Unit' => $post['weight_unit']),
									'NumberOfPieces'		 => $post['total_count']
								)
	);
	
	$baseUrl = Mage::helper('aramexshipment')->getWsdlPath();
	$soapClient = new SoapClient($baseUrl.'aramex-rates-calculator-wsdl.wsdl', array('trace' => 1));
	
	try{
	$results = $soapClient->CalculateRate($params);	
	if($results->HasErrors){
		if(count($results->Notifications->Notification) > 1){
			$error="";
			foreach($results->Notifications->Notification as $notify_error){
				$error.=$this->__('Aramex: ' . $notify_error->Code .' - '. $notify_error->Message)."<br>";				
			}
			$response['error']=$error;
		}else{
			$response['error']=$this->__('Aramex: ' . $results->Notifications->Notification->Code . ' - '. $results->Notifications->Notification->Message);
		}
		$response['type']='error';
	}else{
		$response['type']='success';
		$amount="<p class='amount'>".$results->TotalAmount->Value." ".$results->TotalAmount->CurrencyCode."</p>";
		$text="Local taxes - if any - are not included. Rate is based on account number $account in ".$country->getName();
		$response['html']=$amount.$text;		
		
	}
	} catch (Exception $e) {
			$response['type']='error';
			$response['error']=$e->getMessage();			
	}
	}
	catch (Exception $e) {
			$response['type']='error';
			$response['error']=$e->getMessage();			
	}
	print json_encode($response);		
	die();
	 }
	
}
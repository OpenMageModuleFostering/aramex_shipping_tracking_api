<?php
class Aramex_Shipment_SchedulepickupController extends Mage_Adminhtml_Controller_Action {
	
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
		$post=$post['pickup'];	
		$_order = Mage::getModel('sales/order')->load($post['order_id']);	
		
		$pickupDate=strtotime($post['date']);		
		$readyTimeH=$post['ready_hour'];
		$readyTimeM=$post['ready_minute'];			
		$readyTime=mktime(($readyTimeH-2),$readyTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));	
		
		$closingTimeH=$post['latest_hour'];
		$closingTimeM=$post['latest_minute'];
		$closingTime=mktime(($closingTimeH-2),$closingTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
		$params = array(
		'ClientInfo'  	=> $clientInfo,
								
		'Transaction' 	=> array(
								'Reference1'			=> $post['reference'] 
								),
								
		'Pickup'		=>array(
								'PickupContact'			=>array(
									'PersonName'		=>html_entity_decode($post['contact']),
									'CompanyName'		=>html_entity_decode($post['company']),
									'PhoneNumber1'		=>html_entity_decode($post['phone']),
									'PhoneNumber1Ext'	=>html_entity_decode($post['ext']),
									'CellPhone'			=>html_entity_decode($post['mobile']),
									'EmailAddress'		=>html_entity_decode($post['email'])
								),
								'PickupAddress'			=>array(
									'Line1'				=>html_entity_decode($post['address']),
									'City'				=>html_entity_decode($post['city']),
									'StateOrProvinceCode'=>html_entity_decode($post['state']),
									'PostCode'			=>html_entity_decode($post['zip']),
									'CountryCode'		=>$post['country']
								),
								
								'PickupLocation'		=>html_entity_decode($post['location']),
								'PickupDate'			=>$readyTime,
								'ReadyTime'				=>$readyTime,
								'LastPickupTime'		=>$closingTime,
								'ClosingTime'			=>$closingTime,
								'Comments'				=>html_entity_decode($post['comments']),
								'Reference1'			=>html_entity_decode($post['reference']),
								'Reference2'			=>'',
								'Vehicle'				=>$post['vehicle'],
								'Shipments'				=>array(
									'Shipment'					=>array()
								),
								'PickupItems'			=>array(
									'PickupItemDetail'=>array(
										'ProductGroup'	=>$post['product_group'],
										'ProductType'	=>$post['product_type'],
										'Payment'		=>$post['payment_type'],										
										'NumberOfShipments'=>$post['no_shipments'],
										'NumberOfPieces'=>$post['no_pieces'],										
										'ShipmentWeight'=>array('Value'=>$post['text_weight'],'Unit'=>$post['weight_unit']),
										
									),
								),
								'Status'				=>$post['status']
							)
	);
	
	$baseUrl = Mage::helper('aramexshipment')->getWsdlPath();
	$soapClient = new SoapClient($baseUrl . 'shipping.wsdl');
	
	try{
	$results = $soapClient->CreatePickup($params);		
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
		$notify = false;
        $visible = false;
		$comment="Pickup reference number ( <strong>".$results->ProcessedPickup->ID."</strong> ).";
		$_order->addStatusHistoryComment($comment, $_order->getStatus())
		->setIsVisibleOnFront($visible)
		->setIsCustomerNotified($notify);
		$_order->save();	
		$shipmentId=null;
		$shipment = Mage::getModel('sales/order_shipment')->getCollection()
		->addFieldToFilter("order_id",$_order->getId())->load();
		if($shipment->count()>0){
			foreach($shipment as $_shipment){
				$shipmentId=$_shipment->getId();
				break;
			}
		}
		if($shipmentId!=null){
			$shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
			$shipment->addComment(
                $comment,
                false,
                false
            );
			$shipment->save();
		}
		$response['type']='success';
		$amount="<p class='amount'>Pickup reference number ( <strong>".$results->ProcessedPickup->ID."</strong> ).</p>";		
		$response['html']=$amount;
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
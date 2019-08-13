<?php
	class Aramex_Shipment_Block_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
	{
		function __construct()
		{
			$itemscount 	= 0;
			$totalWeight 	= 0;
			$_order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));				
			$itemsv = $_order->getAllVisibleItems();
			foreach($itemsv as $itemvv){
				if($itemvv->getQtyOrdered() > $itemvv->getQtyShipped()){
					$itemscount += $itemvv->getQtyOrdered() - $itemvv->getQtyShipped();
				}
				if($itemvv->getWeight() != 0){
					$weight =  $itemvv->getWeight()*$itemvv->getQtyOrdered(); 
				} else {
					$weight =  0.5*$itemvv->getQtyOrdered();
				}
				$totalWeight 	+= $weight;
			 }
			 
			 $shipments = Mage::getResourceModel('sales/order_shipment_collection')
				->addAttributeToSelect('*')	
				->addFieldToFilter("order_id",$_order->getId())->join("sales/shipment_comment",'main_table.entity_id=parent_id','comment')->addFieldToFilter('comment', array('like'=>"%{$_order->getIncrementId()}%"))->load();
				
				$aramex_return_button = false;
								
				if($shipments->count()){
					foreach($shipments as $key=>$comment){
						if (version_compare(PHP_VERSION, '5.3.0') <= 0) {
							$awbno=substr($comment->getComment(),0, strpos($comment->getComment(),"- Order No")); 
						}
						else{				
							$awbno=strstr($comment->getComment(),"- Order No",true);
						}
						$awbno=trim($awbno,"AWB No.");					
						break;
					}
					if((int) $awbno)
						$aramex_return_button = true;
				}
			 
			 
			 
			 
			if($_order->canShip()){
			 $this->_addButton('create_aramex_shipment', array(
							'label'     => Mage::helper('Sales')->__('Prepare Aramex Shipment'),
							'onclick'   => 'aramexpop('.$itemscount.')',
							'class'     => 'go'
						), 0, 100, 'header', 'header');
			}
			elseif(!$_order->canShip() && $aramex_return_button){
				 $this->_addButton('create_aramex_shipment', array(
							'label'     => Mage::helper('Sales')->__('Return Aramex Shipment'),
							'onclick'   => 'aramexreturnpop('.$itemscount.')',
							'class'     => 'go'
				  ), 0, 100, 'header', 'header');
			}
						
			
				//barry code
				if($itemscount==0){				
					$this->_addButton('print_aramex_label', array(
							'label'     => Mage::helper('Sales')->__('Aramex Print Label'),
							'onclick'   => "myObj.printLabel()",
							'class'     => 'go'
						), 0, 200, 'header', 'header');
				}
				//barry code end here
				
				parent::__construct();
		}
	}
?>
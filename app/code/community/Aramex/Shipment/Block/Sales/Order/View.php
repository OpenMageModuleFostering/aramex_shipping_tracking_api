<?php
	class Aramex_Shipment_Block_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
	{
		function __construct()
		{						$itemscount 	= 0;			$totalWeight 	= 0;						$_order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));			$itemsv = $_order->getAllVisibleItems();			foreach($itemsv as $itemvv){				if($itemvv->getQtyOrdered() > $itemvv->getQtyShipped()){					$itemscount += $itemvv->getQtyOrdered() - $itemvv->getQtyShipped();				}								if($itemvv->getWeight() != 0){						$weight =  $itemvv->getWeight()*$itemvv->getQtyOrdered();				} else {					$weight =  0.5*$itemvv->getQtyOrdered();				}								$totalWeight 	+= $weight;									}			
			$this->_addButton('create_aramex_shipment', array(
						'label'     => Mage::helper('Sales')->__('Prepare Aramex Shipment'),
						'onclick'   => 'aramexpop('.$itemscount.')',
						'class'     => 'go'
					), 0, 100, 'header', 'header');
			
			parent::__construct();
		}
	}
?>
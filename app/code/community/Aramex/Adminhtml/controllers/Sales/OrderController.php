<?php
require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';
class Aramex_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
	public function commentsHistoryAction()
    {		
        $this->_initOrder();		
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_history')->setTemplate("aramex/sales/order/view/tab/history.phtml")->toHtml()
        );
    }
}
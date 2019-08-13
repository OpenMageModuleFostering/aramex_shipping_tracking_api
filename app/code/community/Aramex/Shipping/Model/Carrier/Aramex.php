<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Usa
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Aramex_Shipping_Model_Carrier_Aramex extends Mage_Usa_Model_Shipping_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code 				= 'aramex';
    protected $_request 			= null;
    protected $_result 				= null;
    protected $_defaultGatewayUrl 	= null;
	
	function __construct()
	{  
		$this->_defaultGatewayUrl = Mage::helper('aramexshipment')->getWsdlPath().'Tracking.wsdl';
	}
	
	protected function _doShipmentRequest(Varien_Object $request)
	{
		return $result;
	}
	
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
	  
        if (!$this->getConfigFlag($this->_activeFlag)) {
            return false;
        }
		

       $this->setRequest($request);
        $this->_result = $this->_getQuotes();				
        $this->_updateFreeMethodQuote($request);		
        return $this->getResult();
    }
	
	
	
    public function setRequest(Mage_Shipping_Model_Rate_Request $request) {
        $this->_request = $request;

        $r = new Varien_Object();
		

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        } else {
            $r->setService('ALL');
        }

        if ($request->getAramexUserid()) {
            $userId = $request->getAramexUserid();
        } else {
            $userId = $this->getConfigData('userid');
        }
        $r->setUserId($userId);

       /* if ($request->getAramexContainer()) {
            $container = $request->getAramexContainer();
        } else {
            $container = $this->getConfigData('container');
        }
        $r->setContainer($container); */

        if ($request->getAramexSize()) {
            $size = $request->getAramexSize();
        } else {
            $size = $this->getConfigData('size');
        }
        $r->setSize($size);

        if ($request->getAramexMachinable()) {
            $machinable = $request->getAramexMachinable();
        } else {
            $machinable = $this->getConfigData('machinable');
        }
        $r->setMachinable($machinable);

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig('shipping/origin/postcode'));
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        $r->setDestCountryId($destCountry);


        $countries = Mage::getResourceModel('directory/country_collection')
                        ->addCountryIdFilter($destCountry)
                        ->load()
                        ->getItems();
        $country = array_shift($countries);
        $countryName = $country->getName();

        $r->setDestCountryName($countryName);

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
        $r->setWeightPounds(floor($weight));
		$r->setPackageQty($request->getPackageQty());
		
        $r->setWeightOunces(round(($weight - floor($weight)) * 16, 1));
        if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }
		$r->setDestState($request->getDestRegionCode());

        $r->setValue($request->getPackageValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

        $this->_rawRequest = $r;

        return $this;
    }
	
    public function getCode($type, $code='') {
        return false;
    }
	
    protected function _getQuotes() {
		 return $this->_getAramexQuotes();
        /*return false; */
    }
	
    public function getResult() {
        return $this->_result;
    }
	
    protected function _setFreeMethodRequest($freeMethod) {
        $r = $this->_rawRequest;

        $weight = $this->getTotalNumOfBoxes($r->getFreeMethodWeight());
        $r->setWeightPounds(floor($weight));
        $r->setWeightOunces(round(($weight - floor($weight)) * 16, 1));
        $r->setService($freeMethod);
    }
	
    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
       /*$allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }
		Mage::log($arr);*/
		$arr['SFC'] = 'Surface  Cargo (India)';
		$arr['OND'] = 'Overnight (Document)';
		$arr['ONP'] = 'Overnight (Parcel)';
        return $arr;
    }
	
    /**
     * Return array of authenticated information
     *
     * @return array
     */
    protected function _getAuthDetails()
    {
		return array(
						'ClientInfo'  			=> array(
												'AccountCountryCode'	=> Mage::getStoreConfig('aramexsettings/settings/account_country_code'),
												'AccountEntity'		 	=> Mage::getStoreConfig('aramexsettings/settings/account_entity'),
												'AccountNumber'		 	=> Mage::getStoreConfig('aramexsettings/settings/account_number'),
												'AccountPin'		 	=> Mage::getStoreConfig('aramexsettings/settings/account_pin'),
												'UserName'			 	=> Mage::getStoreConfig('aramexsettings/settings/user_name'),
												'Password'			 	=> Mage::getStoreConfig('aramexsettings/settings/password'),
												'Version'			 	=> 'v1.0'
												)
					);
    }

    public function getTracking($trackings) {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }

        $this->_getXmlTracking($trackings);

        return $this->_result;
    }

    protected function setTrackingReqeust() {
        $r = new Varien_Object();

        $userId = $this->getConfigData('userid');
        $r->setUserId($userId);

        $this->_rawTrackRequest = $r;
    }

    protected function _getXmlTracking($trackings) {
        $r = $this->_rawTrackRequest;

        foreach ($trackings as $tracking) {
            $this->_parseXmlTrackingResponse($tracking);
        }
    }

    protected function _parseXmlTrackingResponse($trackingvalue) {
        $resultArr = array();

        if (!$this->_result) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }
        $defaults = $this->getDefaults();

        //$url = 'http://localhost:8080/soap_test/wsdl/Tracking.wsdl';
        //if (!$url) {
        $url = $this->_defaultGatewayUrl;
		
        //}
		
        $clientAramex = new SoapClient($url);
		$aramexParams = $this->_getAuthDetails();

		$aramexParams['Transaction'] 	= array('Reference1' => '001' );
		$aramexParams['Shipments'] 		= array($trackingvalue);

        $_resAramex = $clientAramex->TrackShipments($aramexParams);
	
		if(is_object($_resAramex) && !$_resAramex->HasErrors){
				$tracking = Mage::getModel('shipping/tracking_result_status');
				$tracking->setCarrier('aramex');
				$tracking->setCarrierTitle($this->getConfigData('title'));
				$tracking->setTracking($trackingvalue);
				if(!empty($_resAramex->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult)){
					$tracking->setTrackSummary($this->getTrackingInfoTable($_resAramex->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult));
				} else {
					$tracking->setTrackSummary('Unable to retrieve quotes, please check if the Tracking Number is valid or contact your administrator.');
				}
				$this->_result->append($tracking);
		} else {
				$errorMessage = '';
				foreach($_resAramex->Notifications as $notification){
					$errorMessage .= '<b>' . $notification->Code . '</b>' . $notification->Message;
				}
				$error = Mage::getModel('shipping/tracking_result_error');
				$error->setCarrier('aramex');
				$error->setCarrierTitle($this->getConfigData('title'));
				$error->setTracking($trackingvalue);
				$error->setErrorMessage($errorMessage);
				$this->_result->append($error);			
		}
    }

    public function getResponse() {
        $statuses = '';
        if ($this->_result instanceof Mage_Shipping_Model_Tracking_Result) {
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking) {
                    if ($data = $tracking->getAllData()) {
                        if (!empty($data['track_summary'])) {
                            $statuses .= Mage::helper('usa')->__($data['track_summary']);
                        } else {
                            $statuses .= Mage::helper('usa')->__('Empty response');
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = Mage::helper('usa')->__('Empty response');
        }
        return $statuses;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getTrackingInfoTable($HAWBHistory) {

        $_resultTable = '<table summary="Item Tracking"  class="data-table">';
        $_resultTable .= '<col width="1">
                          <col width="1">
                          <col width="1">
                          <col width="1">
                          <thead>
                          <tr class="first last">
                          <th>Location</th>
                          <th>Action Date/Time</th>
                          <th class="a-right">Tracking Description</th>
                          <th class="a-center">Comments</th>
                          </tr>
                          </thead><tbody>';

        foreach ($HAWBHistory as $HAWBUpdate) {

            $_resultTable .= '<tr>
                <td>' . $HAWBUpdate->UpdateLocation . '</td>
                <td>' . $HAWBUpdate->UpdateDateTime . '</td>
                <td>' . $HAWBUpdate->UpdateDescription . '</td>
                <td>' . $HAWBUpdate->Comments . '</td>
                </tr>';
        }
        $_resultTable .= '</tbody></table>';

        return $_resultTable;
    }
	
	public function _getAramexQuotes(){	
		$r = $this->_rawRequest;
		$pkgWeight = $r->getWeightPounds();
        $pkgQty =  $r->getPackageQty();
		
		
		$product_group = 'EXP';
		$allowed_methods_key = 'allowed_international_methods';
		$allowed_methods = Mage::getSingleton('aramex/carrier_aramex_source_internationalmethods')->toKeyArray();	
		if(Mage::getStoreConfig('aramexsettings/shipperdetail/country') == $r->getDestCountryId()){
			$product_group = 'DOM';
			$allowed_methods = Mage::getSingleton('aramex/carrier_aramex_source_domesticmethods')->toKeyArray();
			$allowed_methods_key = 'allowed_domestic_methods';
		}
		
		$admin_allowed_methods = explode(',',$this->getConfigData($allowed_methods_key));
		$admin_allowed_methods = array_flip($admin_allowed_methods);		
		$allowed_methods = array_intersect_key($allowed_methods,$admin_allowed_methods);	
		
		$baseUrl = Mage::helper('aramexshipment')->getWsdlPath();
		$clientInfo = Mage::helper('aramexshipment')->getClientInfo();
		$OriginAddress = array(
								'StateOrProvinceCode'	=> Mage::getStoreConfig('aramexsettings/shipperdetail/state'),
								'City'					=> Mage::getStoreConfig('aramexsettings/shipperdetail/city'),
								'PostCode'				=> Mage::getStoreConfig('aramexsettings/shipperdetail/postalcode'),
								'CountryCode'				=> Mage::getStoreConfig('aramexsettings/shipperdetail/country'),
								);
		$DestinationAddress = array(
								'StateOrProvinceCode'	=>$r->getDestState(),
								'City'					=> '',
								'PostCode'				=>  Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID == $r->getDestCountryId() ? substr($r->getDestPostal(), 0, 5) : $r->getDestPostal(),
								'CountryCode'			=> $r->getDestCountryId(),
							);
	   $ShipmentDetails	= array(
								'PaymentType'			 => 'P',
								'ProductGroup'			 => $product_group,
								'ProductType'			 => '',
								'ActualWeight' 			 => array('Value' => $pkgWeight, 'Unit' => 'LB'),
								'ChargeableWeight' 	     => array('Value' => $pkgWeight, 'Unit' => 'LB'),
								'NumberOfPieces'		 => $pkgQty
							);
						
        $params = array('ClientInfo' => $clientInfo, 'OriginAddress' => $OriginAddress, 'DestinationAddress' => $DestinationAddress, 'ShipmentDetails' => $ShipmentDetails);
			
		
	  //SOAP object		 
	   $soapClient = new SoapClient($baseUrl . 'aramex-rates-calculator-wsdl.wsdl');
	   $priceArr  = array();
	   foreach($allowed_methods as $m_value =>$m_title){	      
			$params['ShipmentDetails']['ProductType'] = $m_value;
			
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
					$priceArr[$m_value] = array('label' => $m_title, 'amount'=> $results->TotalAmount->Value);		
				
			}
		} catch (Exception $e) {
				$response['type']='error';
				$response['error']=$e->getMessage();			
		}
	}	
	  $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$values) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);               
                $rate->setMethodTitle($values['label']);
                $rate->setCost($values['amount']);
                $rate->setPrice($values['amount']);
                $result->append($rate);
            }
        }

        return $result;	
	}
	
	
	
}

?>
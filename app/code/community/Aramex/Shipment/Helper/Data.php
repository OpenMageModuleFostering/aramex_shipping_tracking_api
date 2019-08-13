<?php
class Aramex_Shipment_Helper_Data extends  Mage_Core_Helper_Abstract
{
      public function getClientInfo()
      {
		$account=Mage::getStoreConfig('aramexsettings/settings/account_number');
		$username=Mage::getStoreConfig('aramexsettings/settings/user_name');
		$password=Mage::getStoreConfig('aramexsettings/settings/password');
		$pin=Mage::getStoreConfig('aramexsettings/settings/account_pin');
		$entity=Mage::getStoreConfig('aramexsettings/settings/account_entity');
		$country_code=Mage::getStoreConfig('aramexsettings/settings/account_country_code');
		return array(
			'AccountCountryCode'	=> $country_code,
			'AccountEntity'		 	=> $entity,
			'AccountNumber'		 	=> $account,
			'AccountPin'		 	=> $pin,
			'UserName'			 	=> $username,
			'Password'			 	=> $password,
			'Version'			 	=> 'v1.0',
			'Source'				=> 31
		);
      }
	  public function getWsdlPath(){
		$wsdlBasePath = Mage::getModuleDir('etc', 'Aramex_Shipment')  . DS . 'wsdl' . DS . 'Aramex' . DS;
		if(Mage::getStoreConfig('aramexsettings/config/sandbox_flag')==1){
			$wsdlBasePath .='TestMode'.DS;
		}
		return $wsdlBasePath;
	  }
	 
}
<?php
/**
 *    Copyright (C) 2018 Damien Vargas
 *    Copyright (C) 2017 Frank Wall
 *    Copyright (C) 2015 Deciso B.V.
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */
namespace OPNsense\Dante;

use \OPNsense\Base\ApiMutableModelControllerBase;
use \OPNsense\Base\UIModelGrid;
use \OPNsense\Core\Config;


/**
 * Class ClientslistController
 * @package OPNsense\Dante
 */
abstract class GlobalController extends ApiMutableModelControllerBase
{
 	/********************** Gestion Config ************************************/
    /**
     * Validate and save model after update or insertion.
     * Use the reference node and tag to rename validation output for a specific
     * node to a new offset, which makes it easier to reference specific uuids
     * without having to use them in the frontend descriptions.
     * @param BaseModel $mdl model reference
     * @param $node reference node, to use as relative offset
     * @param $reference reference for validation output, used to rename the validation output keys
     * @return array result / validation output
     */
    protected function GlobalSave($mdl, $node = null, $reference = null, $message = "saved")
    {
    	$result = array("result"=>"failed","validations" => array());
    	// perform validation
    	$valMsgs = $mdl->performValidation();
    	foreach ($valMsgs as $field => $msg) {
    		// replace absolute path to attribute for relative one at uuid.
    		if ($node != null) {
    			$fieldnm = str_replace($node->__reference, $reference, $msg->getField());
    			$result["validations"][$fieldnm] = $msg->getMessage();
    		} else {
    			$result["validations"][$msg->getField()] = $msg->getMessage();
    		}
    	}
    	
    	// serialize model to config and save when there are no validation errors
    	if (count($result['validations']) == 0) {
    		// save config if validated correctly
    		$mdl->serializeToConfig();
    		
    		Config::getInstance()->save();
    		$mdl->configDirty();
    		$result = array("result" => $message);
    	}
    	
    	return $result;
    }
    
    /**
     * Create reference array with UUID and position in list
     * @param string $path Way define in xml's model
     * @param BaseModel $mdl
     * @return array
     */
    private function prepareTableau($path,$mdl){
    	$liste_uuid=array();
    	$nodes=$this->retrieveModel($path);
    	foreach ($nodes->getChildren() as $alias) {
    		$uuid=$alias->getAttributes()["uuid"];
    		$node=$mdl->getNodeByReference($path.".".$uuid);
    		if(!is_null($node)){
    			$liste_uuid[$uuid]=(string)$node->rulePosition;
    		}
    	}
    	return $liste_uuid;
    }
    
    /**
     * Add new rule and reorder rules 
     * @param string $path Way define in xml's model
     * @param BaseModel $mdl
     * @param string $uuid_ref
     * @return boolean
     */
    protected function ReorderRules($path,$mdl,$uuid_ref = null){
    	$liste_uuid=$this->prepareTableau($path,$mdl);
    	$pos_ref=$this->retrieveRulePositionValue($path,$mdl,$uuid_ref);
    	asort($liste_uuid);
    	$position=1;
    	if(isset($liste_uuid[$uuid_ref])){
    		unset($liste_uuid[$uuid_ref]);
    	}
    	$changed=false;
    	foreach($liste_uuid as $uuid=>$pos){
    		if($pos_ref==$position){
    			$this->ResetPosition($path,$mdl,$uuid_ref,$position);
    			$changed=true;
    			$position++;
    		}
    		$this->ResetPosition($path,$mdl,$uuid,$position);
    		$position++;
    	}
    	if(!$changed){
    		//ref position to high
    		$this->ResetPosition($path,$mdl,$uuid_ref,$position);
    	}
    	return false;
    }
    
    /**
     * Reset rulePosition of nodes
     * @param string $path Way define in xml's model
     * @param BaseModel $mdl
     * @param string $uuid
     * @param int $position
     * @return \OPNsense\Dante\Api\ClientslistController
     */
    private function ResetPosition($path,$mdl,$uuid,$position){
    	$node=$mdl->getNodeByReference($path.".".$uuid);
    	if ($node != null) {
    		$node->rulePosition=$position;
    	}
    	return $this;
    }
    
    /**
     * Retreive rulePosition of a node
     * @param string $path Way define in xml's model
     * @param BaseModel $mdl
     * @param string $uuid
     * @return string
     */
    private function retrieveRulePositionValue($path,$mdl,$uuid){
    	if(!is_null($uuid)){
    		$node=$mdl->getNodeByReference($path.".".$uuid);
    		if ($node != null) {
    			return (string)$node->rulePosition;
    		}
    	}
    	return 10000;
    }
    
    /**
     * Retreive 
     * @param string $path
     * @return BaseModel
     */
    protected function &retrieveModel($path){
    	$mdl = $this->getModel();
    	$tmp = $mdl;
    	foreach (explode('.', $path) as $step) {
    		$tmp = $tmp->{$step};
    	}
    	return $tmp;
    }
    
    protected function validIpFqdn($ip,$CheckCidr=true){
    	return $this->valide_ip_cidr_fqdn($ip,$CheckCidr);
    }
    
    /********************** End Gestion Config ************************************/
    
    /********************** Gestion Format ************************************/
    /**
     * Valid if $domain_name is a valid domain for Dante
     * @param string $domain_name
     * @return boolean
     */
    private function is_valid_domain_name($domain_name)
    {
    	$options = array (
    			'flags' => FILTER_FLAG_HOSTNAME
    	);
    	if(strpos ( $domain_name, '.' ) === 0 ){
    		$domain_name='www'.$domain_name;
    	}
    	if(filter_var ( $domain_name, FILTER_VALIDATE_DOMAIN, $options ) != false){
    		return true;
    	}
    	return false;
//     	return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
//     			&& preg_match("/^.{1,253}$/", $domain_name) //overall length check
//     			&& preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
    }
    
    
    /**
     * Validate IP/CIDR or FQDN
     * @return boolean
     */
    private function valide_ip_cidr_fqdn($ip,$CheckCidr) {
    	if(preg_match("/^(?<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}).*$/", $ip,$filtre)!=false) {
    		if ($filtre['ip'] == "0.0.0.0" || (filter_var ( $filtre['ip'], FILTER_VALIDATE_IP ) != false)) {
    			if($CheckCidr){
    				if(preg_match("/^(?<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\/(?<cidr>\d{1,2})$/", $ip,$filtre)!=false){
    					if (is_numeric ( $filtre['cidr'] ) && $filtre['cidr']  <= 32) {
    						return true;
    					}
    				}
    			} else {
    				return true;
    			}
    		}
    		return false;
    	} 
    	return $this->is_valid_domain_name($ip);
    }
    
    /********************** End Gestion Format ************************************/
}

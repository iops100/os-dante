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
namespace OPNsense\Dante\Api;

use \OPNsense\Base\ApiMutableModelControllerBase;
use \OPNsense\Base\UIModelGrid;
use \OPNsense\Core\Config;
use \OPNsense\Dante\ClientsList;

/**
 * Class ClientslistController
 * @package OPNsense\Dante
 */
class ClientslistController extends \OPNsense\Dante\GlobalController
{
	static protected $internalModelName = 'clientslist';
	static protected $internalModelClass = '\OPNsense\Dante\ClientsList';
	
	private function validClientIps($node){
		if($this->validIpFqdn($node->getNodes()["fromIp"])===false){
			return array("result"=>"failed","validations" => array("client.fromIp"=>"Not a valid format"));
		}
		if(!empty($node->getNodes()["interfaceIp"])){
			if($this->validIpFqdn($node->getNodes()["interfaceIp"])===false){
				return array("result"=>"failed","validations" => array("client.interfaceIp"=>"Not a valid format"));
			}
		}
		
		return array();
	}
	
	public function searchClientAction()
    {
    	return $this->searchBase('clients.client', array("rulePosition", "RuleType", "fromIp", "fromPort", "interface", "interfaceIp", "LogsType"),"rulePosition");
    }
    
    public function getClientAction($uuid = NULL)
    {
    	return $this->getBase('client', 'clients.client', $uuid);
    }
    
    public function addClientAction()
    {
    	$result = array("result"=>"failed");
    	if ($this->request->isPost() && $this->request->hasPost("client")) {
    		$mdl = new ClientsList();
    		$node = $mdl->clients->client->Add();
    		if ($node != null) {
    			$node->setNodes($this->request->getPost("client"));
    			$invalidIps=$this->validClientIps($node);
    			if(!empty($invalidIps)){
    				return $invalidIps;
    			}
    			$this->ReorderRules('clients.client',$mdl,$node->getAttributes()["uuid"]);
    			return $this->GlobalSave($mdl, $node, "client");
    		}
    	}
    	return $result;
    }
    
    public function delClientAction($uuid)
    {
    	$result = array("result" => "failed");
    	
    	if ($this->request->isPost()) {
    		$mdl = $this->getModel();
    		if ($uuid != null) {
    			$mdl = new ClientsList();
    			if ($mdl->clients->client->del($uuid)) {
    				$this->ReorderRules('clients.client',$mdl,NULL);
    				return $this->GlobalSave($mdl, NULL, "client", "deleted");
    			} else {
    				$result['result'] = 'not found';
    			}
    		}
    	}
    	return $result;
    }
    
    public function setClientAction($uuid)
    {
    	if ($this->request->isPost() && $this->request->hasPost("client")) {
    		$mdl = new ClientsList();
    		if ($uuid != null) {
    			$node = $mdl->getNodeByReference('clients.client.' . $uuid);
    			if ($node != null) {
    				$node->setNodes($this->request->getPost("client"));
    				$invalidIps=$this->validClientIps($node);
    				if(!empty($invalidIps)){
    					return $invalidIps;
    				}
    				$this->ReorderRules('clients.client',$mdl,NULL);
    				return $this->GlobalSave($mdl, NULL, "client", "saved");
    			}
    		}
    	}
    	return array("result" => "failed");
    }
    
    public function toggleClientAction($uuid)
    {
    	return $this->toggleBase('clients.client', $uuid);
    }
}

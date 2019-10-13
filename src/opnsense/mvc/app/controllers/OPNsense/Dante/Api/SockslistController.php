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
use \OPNsense\Dante\SocksList;

/**
 * Class SockslistController
 * @package OPNsense\Dante
 */
class SockslistController extends \OPNsense\Dante\GlobalController
{
	static protected $internalModelName = 'sockslist';
	static protected $internalModelClass = '\OPNsense\Dante\SocksList';
	
	private function validSockIps($node){
		if($this->validIpFqdn($node->getNodes()["fromIp"])===false){
			return array("result"=>"failed","validations" => array("sock.fromIp"=>"Not a valid format"));
		}
		if($this->validIpFqdn($node->getNodes()["toIp"])===false){
			return array("result"=>"failed","validations" => array("sock.toIp"=>"Not a valid format"));
		}
		return array();
	}
	
	public function searchSockAction()
    {
    	return $this->searchBase('socks.sock', array("rulePosition", "RuleType", "fromIp", "toIp", "toPort", "LogsType", "Protocol","clientMethod"),"rulePosition");
    }
    
    public function getSockAction($uuid = NULL)
    {
    	return $this->getBase('sock', 'socks.sock', $uuid);
    }
    
    public function addSockAction()
    {
    	$result = array("result"=>"failed");
    	if ($this->request->isPost() && $this->request->hasPost("sock")) {
    		$mdl = new SocksList();
    		$node = $mdl->socks->sock->Add();
    		if ($node != null) {
    			$node->setNodes($this->request->getPost("sock"));
    			$invalidIps=$this->validSockIps($node);
    			if(!empty($invalidIps)){
    				return $invalidIps;
    			}
    			$this->ReorderRules('socks.sock',$mdl,$node->getAttributes()["uuid"]);
    			return $this->GlobalSave($mdl, $node, "sock");
    		}
    	}
    	return $result;
    }
    
    public function delSockAction($uuid)
    {
    	$result = array("result" => "failed");
    	
    	if ($this->request->isPost()) {
    		$mdl = $this->getModel();
    		if ($uuid != null) {
    			$mdl = new SocksList();
    			if ($mdl->socks->sock->del($uuid)) {
    				$this->ReorderRules('socks.sock',$mdl,NULL);
    				return $this->GlobalSave($mdl, NULL, "sock", "deleted");
    			} else {
    				$result['result'] = 'not found';
    			}
    		}
    	}
    	return $result;
    }
    
    public function setSockAction($uuid)
    {
    	if ($this->request->isPost() && $this->request->hasPost("sock")) {
    		$mdl = new SocksList();
    		if ($uuid != null) {
    			$node = $mdl->getNodeByReference('socks.sock.' . $uuid);
    			if ($node != null) {
    				$node->setNodes($this->request->getPost("sock"));
    				$invalidIps=$this->validSockIps($node);
    				if(!empty($invalidIps)){
    					return $invalidIps;
    				}
    				$this->ReorderRules('socks.sock',$mdl,NULL);
    				return $this->GlobalSave($mdl, NULL, "sock", "saved");
    			}
    		}
    	}
    	return array("result" => "failed");
    }
    
    public function toggleSockAction($uuid)
    {
    	return $this->toggleBase('socks.sock', $uuid);
    }
}

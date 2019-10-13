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
use \OPNsense\Dante\RoutesList;

/**
 * Class RouteslistController
 * @package OPNsense\Dante
 */
class RouteslistController extends \OPNsense\Dante\GlobalController
{
	static protected $internalModelName = 'routeslist';
	static protected $internalModelClass = '\OPNsense\Dante\RoutesList';
	
	private function validRouteIps($node){
		if($this->validIpFqdn($node->getNodes()["fromIp"])===false){
			return array("result"=>"failed","validations" => array("route.fromIp"=>"Not a valid format"));
		}
		if($this->validIpFqdn($node->getNodes()["toIp"])===false){
			return array("result"=>"failed","validations" => array("route.toIp"=>"Not a valid format"));
		}
		if($this->validIpFqdn($node->getNodes()["viaIp"],false)===false){
			return array("result"=>"failed","validations" => array("route.viaIp"=>"Not a valid format"));
		}
		return array();
	}
    
    public function searchRouteAction()
    {
    	return $this->searchBase('routes.route', array("rulePosition", "fromIp", "toIp", "toPort", "viaIp", "viaPort", "proxyProtocol","protocol","command"),"rulePosition");
    }
    
    public function getRouteAction($uuid = NULL)
    {
    	return $this->getBase('route', 'routes.route', $uuid);
    }
    
    public function addRouteAction()
    {
    	$result = array("result"=>"failed");
    	if ($this->request->isPost() && $this->request->hasPost("route")) {
    		$mdl = new RoutesList();
    		$node = $mdl->routes->route->Add();
    		if ($node != null) {
    			$node->setNodes($this->request->getPost("route"));
    			$this->ReorderRules('routes.route',$mdl,$node->getAttributes()["uuid"]);
    			$invalidIps=$this->validRouteIps($node);
    			if(!empty($invalidIps)){
    				return $invalidIps;
    			}
    			return $this->GlobalSave($mdl, $node, "route");
    		}
    	}
    	return $result;
    }
    
    public function delRouteAction($uuid)
    {
    	$result = array("result" => "failed");
    	
    	if ($this->request->isPost()) {
    		$mdl = $this->getModel();
    		if ($uuid != null) {
    			$mdl = new RoutesList();
    			if ($mdl->routes->route->del($uuid)) {
    				$this->ReorderRules('routes.route',$mdl,NULL);
    				return $this->GlobalSave($mdl, NULL, "route", "deleted");
    			} else {
    				$result['result'] = 'not found';
    			}
    		}
    	}
    	return $result;
    }
    
    public function setRouteAction($uuid)
    {
    	if ($this->request->isPost() && $this->request->hasPost("route")) {
    		$mdl = new RoutesList();
    		if ($uuid != null) {
    			$node = $mdl->getNodeByReference('routes.route.' . $uuid);
    			if ($node != null) {
    				$node->setNodes($this->request->getPost("route"));
    				$this->ReorderRules('routes.route',$mdl,NULL);
    				$invalidIps=$this->validRouteIps($node);
    				if(!empty($invalidIps)){
    					return $invalidIps;
    				}
    				return $this->GlobalSave($mdl, NULL, "route", "saved");
    			}
    		}
    	}
    	return array("result" => "failed");
    }
    
    public function toggleRouteAction($uuid)
    {
    	return $this->toggleBase('routes.route', $uuid);
    }
}

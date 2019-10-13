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

use OPNsense\Base\BaseModel;
use OPNsense\Core\Backend;

/**
 * Class Dante
 * @package OPNsense\Dante
 */
class RoutesList extends GlobalModel
{
	
	public function createRoutesRules() {
		$listeRoutes=$this->prepareTableauRulePosition($this->routes->route);
		$routesrules = "\n#Routes Rules\n";
		foreach($listeRoutes as $uuid=>$position){
			$node=$this->getNodeByReference("routes.route.".$uuid);
			if(!$node){
				throw new \Exception("Route node could not be found");
			}
			$routesrules .= "route ";
			$routesrules .= " {\n from: " . (string)$node->fromIp ;
			$routesrules .=  " to: " . (string)$node->toIp . $this->getPortLine ( (string)$node->toPort,'=' ) ;
			$routesrules .=  " via: " . (string)$node->viaIp . $this->getPortLine ( (string)$node->viaPort,'=' ) ."\n";
			$routesrules .= $this->getProxyProtocol ( $node ) ;
			$routesrules .= $this->getProtocol ( $node ) ;
			$routesrules .= $this->getCommand ( $node ) ;
			$routesrules .= "}\n";
			/* sock pass { from: 192.168.200.103/32  to: em1 port= 80 log: error connect disconnect } */
		}
		return $routesrules;
	}
}

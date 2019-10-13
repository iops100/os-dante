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
class ClientsList extends GlobalModel
{
	private function findInternalInterface($node){
		if(!empty((string)$node->interfaceIp)){
			return (string)$node->interfaceIp;
		}
		return $this->getInterfaceName(array(array('value'=>(string)$node->interface,'selected'=>1)));
	}
	
	public function createClientsRules() {
		$listeClients=$this->prepareTableauRulePosition($this->clients->client);
		$clientrules = "\n#Client Rules\n";
		foreach($listeClients as $uuid=>$position){
			$node=$this->getNodeByReference("clients.client.".$uuid);
			if(!$node){
				throw new \Exception("Client node could not be found");
			}
			$clientrules .= "client ".(string)$node->RuleType;
			$clientrules .= " {\n from: " . (string)$node->fromIp . $this->getPortLine ( (string)$node->fromPort,'' );
			$clientrules .=  " to: " . $this->findInternalInterface($node) . "\n";
			$clientrules .= $this->getLogstype ( $node ) ;
			$clientrules .= "}\n";
			/* client pass { from: 192.168.200.103/32 port 1-65535 to: em1 log: error connect disconnect } */
		}
		return $clientrules;
	}
}

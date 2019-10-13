<?php
require_once("guiconfig.inc");

function build_logfile_list() {
	$config = &config_read_array('OPNsense','dante','sockdglobal');
	
	if (is_array($config) && isset($config['global']['logOutput'])) {
		return $config['global']['logOutput'];
	}
	return 'sockd.log';
}

$logfile = '/var/log/Dante_opnsense/'.build_logfile_list();
$logclog = false;

$service_hook = 'dante';

//Date and type fields number
$logsplit = 3;

require_once 'diag_logs_template.inc';

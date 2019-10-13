<?php

/**
 *    Copyright (C) 2018 Damien Vargas
 *    Copyright (C) 2017 Frank Wall
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
 */

function dante_enabled()
{
    global $config;

    return isset($config['OPNsense']['dante']['sockdglobal']['global']['enabled']) &&
    $config['OPNsense']['dante']['sockdglobal']['global']['enabled'] == 1;
}

function dante_firewall($fw)
{
    if (!dante_enabled()) {
        return;
    }

    //$fw->registerAnchor('dante/*', 'nat');
    //$fw->registerAnchor('dante/*', 'rdr');
    //$fw->registerAnchor('dante/*', 'fw');
}

/**
 *  register legacy service
 * @return array
 */
function dante_services()
{
    $services = array();

    if (!dante_enabled()) {
        return $services;
    }

    $services[] = array(
        'description' => gettext('Dante Server'),
        'pidfile' => '/var/run/Dante_opnsense/sockd.pid',
        'configd' => array(
            'restart' => array('dante restart'),
            'start' => array('dante start'),
            'stop' => array('dante stop'),
        ),
        'name' => 'dante',
    );

    return $services;
}

/**
 *  sync configuration via xmlrpc
 * @return array
 */

function dante_xmlrpc_sync()
{
    $result = array();
    $result['id'] = 'dante';
    $result['section'] = 'OPNsense.dante';
    $result['description'] = gettext('Opnsense Package to manage Dante Server');
    return array($result);
}

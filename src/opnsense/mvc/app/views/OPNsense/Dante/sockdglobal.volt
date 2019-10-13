{#
 # Copyright (C) 2018 Damien Vargas
 # Copyright (C) 2017 Frank Wall
 # Copyright (C) 2014-2015 Deciso B.V.
 # All rights reserved.
 #
 # Redistribution and use in source and binary forms, with or without modification,
 # are permitted provided that the following conditions are met:
 #
 # 1.  Redistributions of source code must retain the above copyright notice,
 #     this list of conditions and the following disclaimer.
 #
 # 2.  Redistributions in binary form must reproduce the above copyright notice,
 #     this list of conditions and the following disclaimer in the documentation
 #     and/or other materials provided with the distribution.
 #
 # THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 # INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 # AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 # AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 # OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 # SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 # INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 # CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 # ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 # POSSIBILITY OF SUCH DAMAGE.
 #}

<script>
    $( document ).ready(function () {
        var data_get_map = {'frm_sockdglobal_settings':"/api/dante/sockdglobal/get", 'frm_clientslist_settings':"/api/dante/clientslist/get", 'frm_sockslist_settings':"/api/dante/sockslist/get", 'frm_routeslist_settings':"/api/dante/routeslist/get"};
        mapDataToFormUI(data_get_map).done(function (data) {
            formatTokenizersUI();
            $('.selectpicker').selectpicker('refresh');
            updateServiceControlUI('dante');
            isSubsystemDirty();
        });

        ajaxCall(url="/api/dante/service/status", sendData={}, callback=function (data, status) {
            updateServiceStatusUI(data['status']);
        });

        // link save button to API set action
        $("#saveAct").click(function () {
            saveFormToEndpoint(url="/api/dante/sockdglobal/set", formid='frm_sockdglobal_settings',callback_ok=function () {
            });
            isSubsystemDirty();
        });

      /**
       * get the isSubsystemDirty value and print a notice
       */
      function isSubsystemDirty() {
         ajaxGet(url="/api/dante/service/dirty", sendData={}, callback=function(data,status) {
            if (status == "success") {
               if (data.dante.dirty === true) {
                  $("#configChangedMsg").removeClass("hidden");
               } else {
                  $("#configChangedMsg").addClass("hidden");
               }
            }
         });
      }
      
      /**
       * chain std_bootgrid_reload from opnsense_bootgrid_plugin.js
       * to get the isSubsystemDirty state on "UIBootgrid" changes
       */
      var opn_std_bootgrid_reload = std_bootgrid_reload;
      std_bootgrid_reload = function(gridId) {
         opn_std_bootgrid_reload(gridId);
         isSubsystemDirty();
      };

        /*************************************************************************************************************
         * link grid actions
         *************************************************************************************************************/

        $("#grid-clientslists").UIBootgrid(
            {   'search':'/api/dante/clientslist/searchClient',
                'get':'/api/dante/clientslist/getClient/',
                'set':'/api/dante/clientslist/setClient/',
                'add':'/api/dante/clientslist/addClient/',
                'del':'/api/dante/clientslist/delClient/',
                'toggle':'/api/dante/clientslist/toggleClient/'
            }
        );
        
        $("#grid-sockslists").UIBootgrid(
            {   'search':'/api/dante/sockslist/searchSock',
                'get':'/api/dante/sockslist/getSock/',
                'set':'/api/dante/sockslist/setSock/',
                'add':'/api/dante/sockslist/addSock/',
                'del':'/api/dante/sockslist/delSock/',
                'toggle':'/api/dante/sockslist/toggleSock/'
            }
        );
        
        $("#grid-routeslists").UIBootgrid(
            {   'search':'/api/dante/routeslist/searchRoute',
                'get':'/api/dante/routeslist/getRoute/',
                'set':'/api/dante/routeslist/setRoute/',
                'add':'/api/dante/routeslist/addRoute/',
                'del':'/api/dante/routeslist/delRoute/',
                'toggle':'/api/dante/routeslist/toggleRoute/'
            }
        );

        /*************************************************************************************************************
         * Commands
         *************************************************************************************************************/

        /**
         * Reconfigure
         */
        $("#btnApplyConfig").unbind('click').click(function(){
            $("#btnApplyConfigProgress").addClass("fa fa-spinner fa-pulse");
            ajaxCall(url="/api/dante/service/reconfigure", sendData={}, callback=function(data,status) {
                if (status != "success" || data['status'] != 'ok') {
                    BootstrapDialog.show({
                        type: BootstrapDialog.TYPE_WARNING,
                        title: "{{ lang._('Error reconfiguring Dante') }}",
                        message: data['status'],
                        draggable: true
                    });
                } else {
                    ajaxCall(url="/api/dante/service/reconfigure", sendData={});
                }
                $("#btnApplyConfigProgress").removeClass("fa fa-spinner fa-pulse");
                $('#btnApplyConfig').blur();
                isSubsystemDirty();
            });
        });
        
        // update history on tab state and implement navigation
    	if(window.location.hash != "") {
        	$('a[href="' + window.location.hash + '"]').click()
    	}
    	$('.nav-tabs a').on('shown.bs.tab', function (e) {
        	history.pushState(null, null, e.target.hash);
    	});
    });
</script>

<div class="alert alert-info hidden" role="alert" id="configChangedMsg">
   <button class="btn btn-primary pull-right" id="btnApplyConfig" type="button"><b>{{ lang._('Apply changes') }}</b> <i id="btnApplyConfigProgress"></i></button>
   {{ lang._('The Dante configuration has been changed') }} <br /> {{ lang._('You must apply the changes in order for them to take effect.')}}
</div>

<ul class="nav nav-tabs" data-tabs="tabs" id="maintabs">
    <li class="active"><a data-toggle="tab" href="#sockdglobal">{{ lang._('Main Settings') }}</a></li>
    <li><a data-toggle="tab" href="#clientslist">{{ lang._('Clients Access') }}</a></li>
    <li><a data-toggle="tab" href="#sockslist">{{ lang._('Socks Rules') }}</a></li>
    <li><a data-toggle="tab" href="#routeslist">{{ lang._('Routes Rules') }}</a></li>
</ul>
<div class="tab-content content-box tab-content">
    <div id="sockdglobal" class="tab-pane fade in active">
        <div class="content-box" style="padding-bottom: 1.5em;">
            {{ partial("layout_partials/base_form",['fields':sockdglobalForm,'id':'frm_sockdglobal_settings'])}}
            <div class="col-md-12">
                <hr />
                <button class="btn btn-primary" id="saveAct" type="button"><b>{{ lang._('Save') }}</b> <i id="saveAct_progress"></i></button>
            </div>
        </div>
    </div>
    <div id="clientslist" class="tab-pane fade in">
    	<div class="content-box" style="padding-bottom: 1.5em;">
    	<!-- tab page "clientslists" -->
        <table id="grid-clientslists" class="table table-condensed table-hover table-striped table-responsive" data-editDialog="dialogEditDantePassClient">
            <thead>
            <tr>
                <th data-column-id="rulePosition" data-type="string" data-visible="true" data-sortable="true">{{ lang._('Rule Order') }}</th>
                <th data-column-id="RuleType" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Rule Type') }}</th>
                <th data-column-id="fromIp" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Source') }}</th>
                <th data-column-id="fromPort" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Port') }}</th>
                <th data-column-id="interface" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Interface') }}</th>
                <th data-column-id="interfaceIp" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Connected IP') }}</th>
                <th data-column-id="LogsType" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Logs Type') }}</th>
                <th data-column-id="uuid" data-type="string" data-identifier="true" data-visible="false" data-sortable="false">{{ lang._('ID') }}</th>
                <th data-column-id="commands" data-formatter="commands" data-sortable="false">{{ lang._('Commands') }}</th>            
            </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            <tr>
                <td></td>
                <td>
                    <button data-action="add" type="button" class="btn btn-xs btn-default"><span class="fa fa-plus"></span></button>
                </td>
            </tr>
            </tfoot>
        </table>
        </div>
        {{ partial("layout_partials/base_dialog",['fields':formDialogEditDantePassClient,'id':'dialogEditDantePassClient','label':lang._('Add Client Rule')])}}
    </div>
    <div id="sockslist" class="tab-pane fade in">
    	<div class="content-box" style="padding-bottom: 1.5em;">
    	<!-- tab page "sockslists" -->
        <table id="grid-sockslists" class="table table-condensed table-hover table-striped table-responsive" data-editDialog="dialogEditDantePassSock">
            <thead>
            <tr>
                <th data-column-id="rulePosition" data-type="string" data-visible="true" data-sortable="true">{{ lang._('Rule Order') }}</th>
                <th data-column-id="RuleType" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Rule Type') }}</th>
                <th data-column-id="fromIp" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Source') }}</th>
                <th data-column-id="toIp" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Target') }}</th>
                <th data-column-id="toPort" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Port') }}</th>
                <th data-column-id="LogsType" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Logs Type') }}</th>
                <th data-column-id="protocol" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Protocol') }}</th>
                <th data-column-id="clientMethod" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Clientmethod') }}</th>
                <th data-column-id="uuid" data-type="string" data-identifier="true" data-visible="false" data-sortable="false">{{ lang._('ID') }}</th>
                <th data-column-id="commands" data-formatter="commands" data-sortable="false">{{ lang._('Commands') }}</th>            
            </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            <tr>
                <td></td>
                <td>
                    <button data-action="add" type="button" class="btn btn-xs btn-default"><span class="fa fa-plus"></span></button>
                </td>
            </tr>
            </tfoot>
        </table>
        </div>
        {{ partial("layout_partials/base_dialog",['fields':formDialogEditDantePassSock,'id':'dialogEditDantePassSock','label':lang._('Add Sock Rule')])}}
    </div>
    <div id="routeslist" class="tab-pane fade in">
    	<div class="content-box" style="padding-bottom: 1.5em;">
    	<!-- tab page "routeslists" -->
        <table id="grid-routeslists" class="table table-condensed table-hover table-striped table-responsive" data-editDialog="dialogEditDantePassRoute">
            <thead>
            <tr>
                <th data-column-id="rulePosition" data-type="string" data-visible="true" data-sortable="true">{{ lang._('Rule Order') }}</th>
                <th data-column-id="fromIp" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Source') }}</th>
                <th data-column-id="toIp" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Target') }}</th>
                <th data-column-id="toPort" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Target Port') }}</th>
                <th data-column-id="viaIp" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Via') }}</th>
                <th data-column-id="viaPort" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Via Port') }}</th>
                <th data-column-id="proxyProtocol" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Proxy Protocol') }}</th>
                <th data-column-id="protocol" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Protocol') }}</th>
                <th data-column-id="command" data-type="string" data-visible="true" data-sortable="false">{{ lang._('Command') }}</th>
                <th data-column-id="uuid" data-type="string" data-identifier="true" data-visible="false" data-sortable="false">{{ lang._('ID') }}</th>
                <th data-column-id="commands" data-formatter="commands" data-sortable="false">{{ lang._('Commands') }}</th>            
            </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            <tr>
                <td></td>
                <td>
                    <button data-action="add" type="button" class="btn btn-xs btn-default"><span class="fa fa-plus"></span></button>
                </td>
            </tr>
            </tfoot>
        </table>
        </div>
        {{ partial("layout_partials/base_dialog",['fields':formDialogEditDantePassRoute,'id':'dialogEditDantePassRoute','label':lang._('Add Route')])}}
    </div>
</div>


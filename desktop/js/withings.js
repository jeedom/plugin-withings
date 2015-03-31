
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

 $("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

 $('#bt_linkToUser').on('click', function () {
    $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/withings/core/ajax/withings.ajax.php", // url du fichier php
        data: {
            action: "linkToUser",
            id: $('.eqLogic .eqLogicAttr[data-l1key=id]').value()
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { // si l'appel a bien fonctionné
        if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
        }
        if (isset(data.result.redirect)) {
            window.location.href = data.result.redirect;
        } else {
            $('#div_alert').showAlert({message: 'Synchronisation réussie', level: 'success'});
        }
    }
});
});

 $('#bt_registerNotification').on('click', function () {
    $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/withings/core/ajax/withings.ajax.php", // url du fichier php
        data: {
            action: "registerNotification",
            id: $('.eqLogic .eqLogicAttr[data-l1key=id]').value()
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { // si l'appel a bien fonctionné
        if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
        }

        $('#div_alert').showAlert({message: 'Mode push actif', level: 'success'});
        printEqLogic({id : $('.eqLogic .eqLogicAttr[data-l1key=id]').value()});
    }
});
});

 $('#bt_revokeNotification').on('click', function () {
    var el = $(this);
    $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/withings/core/ajax/withings.ajax.php", // url du fichier php
        data: {
            action: "revokeNotification",
            id: $('.eqLogic .eqLogicAttr[data-l1key=id]').value(),
            callback: el.attr('data-callbackurl')
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { // si l'appel a bien fonctionné
        if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
        }
        $('#div_alert').showAlert({message: 'Mode push inactif', level: 'success'});
        printEqLogic({id : $('.eqLogic .eqLogicAttr[data-l1key=id]').value()});
    }
});
});

 function printEqLogic(_data){
 $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/withings/core/ajax/withings.ajax.php", // url du fichier php
        data: {
            action: "listNotification",
            id:_data.id
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { // si l'appel a bien fonctionné
        if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
        }
        $('#bt_registerNotification').hide();
        $('#bt_revokeNotification').hide();
        var found = false;
        if(isset(data.result.body) && isset(data.result.body.profiles)){
            var profiles = data.result.body.profiles;
            for(var i in profiles){
                if(profiles[i].comment == 'Jeedom'){
                    found = true;
                    $('#bt_revokeNotification').attr('data-callbackurl',profiles[i].callbackurl)
                }
            }
            if(found){
                $('#bt_registerNotification').hide();
                $('#bt_revokeNotification').show();
            }else{
             $('#bt_registerNotification').show();
             $('#bt_revokeNotification').hide();
         }
     }
 }
});
}


function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name"></td>';
    tr += '<td>';
    tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" /> {{Historiser}}<br/></span>';
    tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/> {{Afficher}}<br/></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" style="display : none;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="subType" style="display : none;">';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
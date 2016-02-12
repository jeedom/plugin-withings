<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'withings');
$eqLogics = eqLogic::byType('withings');
?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter une personne}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
	echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '" style="' . $opacity . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
}
?>
           </ul>
       </div>
   </div>

   <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend>{{Mes équipements Withings}}
    </legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
         <center>
            <i class="fa fa-plus-circle" style="font-size : 7em;color:#94ca02;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#94ca02"><center>Ajouter</center></span>
    </div>
    <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
	echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
	echo "<center>";
	echo '<img src="plugins/withings/doc/images/withings_icon.png" height="105" width="95" />';
	echo "</center>";
	echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
	echo '</div>';
}
?>
</div>
</div>

<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
    <form class="form-horizontal">
        <fieldset>
            <legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}  <i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom de la personne}}</label>
                <div class="col-sm-3">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de la personne}}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                <div class="col-sm-3">
                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                        <option value="">{{Aucun}}</option>
                        <?php
foreach (object::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
                   </select>
               </div>
           </div>
           <div class="form-group">
            <label class="col-sm-3 control-label" ></label>
            <div class="col-sm-9">
                <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-label-text="{{Activer}}" data-l1key="isEnable" checked/>
                <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-label-text="{{Visible}}" data-l1key="isVisible" checked/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{URL de retour}}</label>
            <div class="col-lg-4">
                <span><?php echo network::getNetworkAccess() . '/plugins/withings/core/php/callback.php';?></span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Lier}}</label>
            <div class="col-lg-2">
                <a class="btn btn-default" id="bt_linkToUser"><i class='fa fa-refresh'></i> {{Lier à un utilisateur}}</a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{Mode push}}</label>
            <div class="col-lg-2">
                <a class="btn btn-success" id="bt_registerNotification"><i class='fa fa-share'></i> {{Activer}}</a>
                <a class="btn btn-danger" id="bt_revokeNotification"><i class='fa fa-times'></i> {{Désactiver}}</a>
            </div>
            <div class="alert alert-info col-lg-7">
                {{Nécessite de pouvoir accéder à jeedom de l'exterieur et d'avoir bien configuré la partie reseaux dans jeedom}}
            </div>
        </div>
		 <legend><i class="fa fa-wrench"></i>  {{Masquer Blocs}}</legend>
             <div class="form-group">
               <div class="col-lg-2">
			   <label class="col-lg-6 control-label">{{Sommeil}} </label>
               <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-l1key="configuration" data-l2key="hidesleep" checked/>
               </div>
               <div class="col-lg-2">
			   <label class="col-lg-6 control-label">{{Activité }} </label>
               <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-l1key="configuration" data-l2key="hideactivity" checked/>
               </div>
               <div class="col-lg-2">
			   <label class="col-lg-6 control-label">{{Mesure }} </label>
               <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-l1key="configuration" data-l2key="hidemesure" checked/>
               </div>
			  </div>
    </fieldset>
</form>

<legend><i class="fa fa-list-alt"></i>  {{Tableau de commandes}}</legend>
<table id="table_cmd" class="table table-bordered table-condensed">
    <thead>
        <tr>
            <th>{{Nom}}</th><th>{{Options}}</th><th>{{Action}}</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<form class="form-horizontal">
    <fieldset>
        <div class="form-actions">
            <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
            <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
        </div>
    </fieldset>
</form>

</div>
</div>

<?php include_file('desktop', 'withings', 'js', 'withings');?>
<?php include_file('core', 'plugin.template', 'js');?>
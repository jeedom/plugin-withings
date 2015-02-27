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
	echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
}
?>
            </ul>
        </div>
    </div>

    <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
        <legend>{{Mes équipements Withings}}
        </legend>
        <?php
if (count($eqLogics) == 0) {
	echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n'avez encore aucune withings, cliquez à gauche sur le bouton ajouter un équipement withings pour commencer}}</span></center>";
} else {
	?>
            <div class="eqLogicThumbnailContainer">
                <?php
foreach ($eqLogics as $eqLogic) {
		echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
		echo "<center>";
		echo '<img src="plugins/withings/doc/images/withings_icon.png" height="105" width="95" />';
		echo "</center>";
		echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
		echo '</div>';
	}
	?>
            </div>
        <?php }?>
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
                    <label class="col-sm-3 control-label" >{{Activer}}</label>
                    <div class="col-sm-1">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" size="16" checked/>
                    </div>
                    <label class="col-sm-1 control-label" >{{Visible}}</label>
                    <div class="col-sm-1">
                        <input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">{{URL de retour}}</label>
                    <div class="col-lg-4">
                        <span><?php echo config::byKey('externalProtocol') . config::byKey('externalAddr') . ':' . config::byKey('externalPort') . config::byKey('externalComplement') . '/plugins/withings/core/php/callback.php';?></span>
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
            </fieldset>
        </form>

        <legend>{{Commandes}}</legend>
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
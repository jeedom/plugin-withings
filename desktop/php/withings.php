<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'withings');
$eqLogics = eqLogic::byType('withings');
?>

<div class="row row-overflow">
   <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
     <div class="eqLogicThumbnailContainer">
	 <div class="cursor eqLogicAction" data-action="add" >
            <i class="fa fa-plus-circle"></i>
        <br/>
        <span>Ajouter</span>
    </div>
      <div class="cursor eqLogicAction" data-action="gotoPluginConf">
          <i class="fa fa-wrench"></i>
      <br/>
      <span>{{Configuration}}</span>
  </div>
  <div class="cursor" id="bt_healthwithings">
      <i class="fa fa-medkit"></i>
 <br/>
  <span>{{Santé}}</span>
</div>
</div>
	<legend><i class="icon loisir-runner5"></i>  {{Mes Equipements Withings}}
    </legend>
    <div class="eqLogicThumbnailContainer">
	    <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
    <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
	echo '<img src="plugins/withings/doc/images/withings_icon.png" />';
	echo '<br/>';
	echo '<span>' . $eqLogic->getHumanName(true, true) . '</span>';
	echo '</div>';
}
?>
</div>
</div>

<div class="col-xs-12 eqLogic" style="display: none;">
    	<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>

    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
        <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="eqlogictab">
          <div class="row">
		  
            <div class="col-sm-12">
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
                    <label class="col-sm-3 control-label"></label>
                    <div class="col-sm-9">
                        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                    </div>
                </div>
		<div class="form-group">
          <label class="col-sm-3 control-label">{{Client ID}}</label>
          <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="client_id"/>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Secret key}}</label>
          <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="client_secret"/>
          </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label">{{URL de retour}}</label>
            <div class="col-lg-4">
                <span><?php echo network::getNetworkAccess('external') . '/plugins/withings/core/php/callback.php';?></span>
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
</div>
</div>
</div>
<div role="tabpanel" class="tab-pane" id="commandtab">
<table id="table_cmd" class="table table-bordered table-condensed">
    <thead>
        <tr>
            <th>{{Nom}}</th><th>{{Options}}</th><th>{{Action}}</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

</div>
</div>
</div>
</div>

<?php include_file('desktop', 'withings', 'js', 'withings');?>
<?php include_file('core', 'plugin.template', 'js');?>

<?php
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
if (config::byKey('api') != init('apikey')) {
	echo 'Clef API non valide, vous n\'etes pas autorisé à effectuer cette action (jeeApi). Demande venant de :' . getClientIp() . 'Clef API : ' . init('apikey') . init('api');
	exit();
}
require_once dirname(__FILE__) . '/withings.inc.php';
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
	echo 'Impossible de trouver l\'équipement correspondant à : ' . init('eqLogic_id');
	exit();
}

if ($eqLogic->getIsEnable() == 1) {
	$eqLogic->syncWithWithings();
}
?>
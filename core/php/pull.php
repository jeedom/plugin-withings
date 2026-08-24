<?php
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
include_file('core', 'authentification', 'php');

if (!jeedom::apiAccess(init('apikey'), 'withings')) {
	http_response_code(403);
	echo 'Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action';
	die();
}
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
	http_response_code(404);
	echo 'Impossible de trouver l\'équipement correspondant à : ' . init('eqLogic_id');
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
	http_response_code(200);
	exit();
}

if ($eqLogic->getIsEnable() == 1) {
	$eqLogic->syncWithWithings();
}

<?php

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
require_once dirname(__FILE__) . '/../../core/php/withings.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    $url = config::byKey('externalAddr');
    if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
        $url = 'http://' . $url;
    }
    if (config::byKey('externalPort') != '' && config::byKey('externalPort') != 80) {
        $url = $url . ':' . config::byKey('externalPort');
    }
    echo 'Vous ne pouvez appeller cette page sans être connecté. Veuillez vous connecter <a href=' . $url . '>ici</a> avant et refaire l\'opération de synchronisation';
    die();
}
require_once dirname(__FILE__) . '/withings.inc.php';
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
    echo 'Impossible de trouver l\'équipement correspondant à : ' . init('eqLogic_id');
    exit();
}
$withings = new WithingsPHP(config::byKey('client_key', 'withings'), config::byKey('secret_key', 'withings'));
$withings->initSession(withings::getCallbackUri() . '?eqLogic_id=' . $eqLogic->getId());
$eqLogic->setConfiguration('userid', $_GET['userid']);
$eqLogic->setConfiguration('token', $_SESSION['withings_Token']);
$eqLogic->setConfiguration('secret', $_SESSION['withings_Secret']);
$eqLogic->save();

$url = config::byKey('externalAddr');
if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
    $url = 'http://' . $url;
}
if (config::byKey('externalPort') != '' && config::byKey('externalPort') != 80) {
    redirect($url . ':' . config::byKey('externalPort') . '?v=d&p=withings&m=withings&id=' . $eqLogic->getId());
} else {
    redirect($url . '?v=d&p=withings&m=withings&id=' . $eqLogic->getId());
}

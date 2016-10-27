<?php
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
include_file('core', 'authentification', 'php');
if (!jeedom::apiAccess(init('apikey'), 'withings')) {
	echo 'Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action';
	die();
}
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
	echo 'Impossible de trouver l\'équipement correspondant à : ' . init('eqLogic_id');
	exit();
}
$userid = init('userid');
$oauthtoken = $eqLogic->getConfiguration('oauthtoken');
$oauthsecret = $eqLogic->getConfiguration('oauthsecret');
$eqLogic->setConfiguration('oauthtoken','');
$eqLogic->setConfiguration('oauthsecret','');
$eqLogic->save();
$nonce = hash('sha1', withings::makeRandomString());
$time = time();
$base_url = 'https://oauth.withings.com/account/access_token';
$consumer_key = $eqLogic->getConfiguration('client_id');
$consumer_secret = $eqLogic->getConfiguration('client_secret');
$url1 = 'oauth_consumer_key='.$consumer_key . '&oauth_nonce=' . $nonce;
$url2 = '&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$time.'&oauth_token='.$oauthtoken.'&oauth_version=1.0';
$basestring = 'GET&' . urlencode($base_url) . '&' . urlencode($url1.$url2);
$oauth_signature = withings::makeSignature($basestring,$consumer_secret.'&'.$oauthsecret);
$url = $base_url . '?' . $url1 . '&oauth_signature=' . $oauth_signature . $url2;
$cmd =  "curl --request GET '" . $url . "'";
$return = shell_exec($cmd);
$return = str_replace('oauth_token=','',$return);
$return = str_replace('_token_secret=','',$return);
$return = str_replace('&userid=','&oauth',$return);
$return = str_replace('&deviceid=','&oauth',$return);
$listResult = explode('&oauth',$return);
$oauth_token = $listResult[0];
$oauth_secret = $listResult[1];
$eqLogic->setConfiguration('oauthtoken',$oauth_token);
$eqLogic->setConfiguration('oauthsecret',$oauth_secret);
$eqLogic->setConfiguration('userid',$userid);
$eqLogic->save();
redirect(network::getNetworkAccess(external) . '/index.php?v=d&p=withings&m=withings&id=' . $eqLogic->getId());

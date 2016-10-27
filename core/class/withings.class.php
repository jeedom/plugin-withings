<?php

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

/* * ***************************Includes********************************* */

class withings extends eqLogic {
	/*     * *************************Attributs****************************** */

	private $_collectDate = '';
	public static $_widgetPossibility = array('custom' => true);

	/*     * ***********************Methode static*************************** */

	public static function cron30() {
		foreach (self::byType('withings') as $withings) {
			if ($withings->getIsEnable() == 1) {
				try {
					$withings->syncWithWithings();
					if ($withings->getConfiguration('withingNumberFailed', 0) > 0) {
						$withings->setConfiguration('withingNumberFailed', 0);
						$withings->save();
					}
				} catch (Exception $e) {
					if ($withings->getConfiguration('withingNumberFailed', 0) > 3) {
						log::add('withings', 'error', __('Erreur sur ', __FILE__) . $withings->getHumanName() . ' (' . $withings->getConfiguration('withingNumberFailed', 0) . ') : ' . $e->getMessage());
					} else {
						$withings->setConfiguration('withingNumberFailed', $withings->getConfiguration('withingNumberFailed', 0) + 1);
						$withings->save();
					}
				}
			}
		}
	}
	
	public static function authorizeWithings($_oauth_token,$_oauth_secret,$_consumer_key,$_consumer_secret){
		$nonce = hash('sha1', self::makeRandomString());
		$time = time();
		$base_url = 'https://oauth.withings.com/account/authorize';
		$url1 = 'oauth_consumer_key='.$_consumer_key . '&oauth_nonce=' . $nonce;
		$url2 = '&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$time.'&oauth_token='.$_oauth_token.'&oauth_version=1.0';
		$basestring = 'GET&' . urlencode($base_url) . '&' . urlencode($url1.$url2);
		$oauth_signature = self::makeSignature($basestring,$_consumer_secret.'&'.$_oauth_secret);
		$url = $base_url . '?' . $url1 . '&oauth_signature=' . $oauth_signature . $url2;
		return $url;
	}
	
	public static function makeRandomString($bits = 256) {
		$bytes = ceil($bits / 8);
		$return = '';
		for ($i = 0; $i < $bytes; $i++) {
			$return .= chr(mt_rand(0, 255));
		}
		return $return;
	}
	
	public static function makeSignature($_basestring,$_secret) {
		$signature = hash_hmac("sha1", $_basestring, $_secret, True);
		$based64 = base64_encode($signature);
		return urlencode($based64);
	}

	/*     * *********************Methode d'instance************************* */

	public function registerNotification() {
		$callback = urlencode(network::getNetworkAccess('external') . '/plugins/withings/core/php/pull.php?eqLogic_id=' . $this->getId() . '&apikey=' . jeedom::getApiKey('withings'));
		$type ='notify';
		$subtype ='action=subscribe';
		$nonce = hash('sha1', self::makeRandomString());
		$time = time();
		$base_url='https://wbsapi.withings.net/' . $type;
		$consumer_key = $this->getConfiguration('client_id');
		$consumer_secret = $this->getConfiguration('client_secret');
		$oauthtoken = $this->getConfiguration('oauthtoken');
		$oauthsecret = $this->getConfiguration('oauthsecret');
		$userid =  $this->getConfiguration('userid');
		$url1 = $subtype .'&callbackurl=' . $callback . '&comment=Jeedom&oauth_consumer_key='.$consumer_key . '&oauth_nonce=' . $nonce;
		$url2 = '&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$time.'&oauth_token='.$oauthtoken.'&oauth_version=1.0&userid='.$userid;
		$basestring = 'GET&' . urlencode($base_url) . '&' . urlencode($url1.$url2);
		$oauth_signature = self::makeSignature($basestring,$consumer_secret.'&'.$oauthsecret);
		$url = $base_url . '?' . $url1 . '&oauth_signature=' . $oauth_signature . $url2;
		$cmd =  "curl --request GET '" . $url . "'";
		$return = shell_exec($cmd);
		return json_decode($return,true);
	}

	public function listNotification() {
		$type ='notify';
		$subtype ='action=list';
		$result = $this->callWithings($type,$subtype);
		log::add('withings','debug',$result);
		return json_decode($result,true);
	}

	public function revokeNotification($_callback) {
		$type ='notify';
		$subtype ='action=revoke';
		$nonce = hash('sha1', self::makeRandomString());
		$withings = $this->getWithings();
		$time = time();
		$base_url='https://wbsapi.withings.net/' . $type;
		$consumer_key = $this->getConfiguration('client_id');
		$consumer_secret = $this->getConfiguration('client_secret');
		$oauthtoken = $this->getConfiguration('oauthtoken');
		$oauthsecret = $this->getConfiguration('oauthsecret');
		$userid =  $this->getConfiguration('userid');
		$url1 = $subtype .'&callbackurl=' . urlencode($_callback) . '&oauth_consumer_key='.$consumer_key . '&oauth_nonce=' . $nonce;
		$url2 = '&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$time.'&oauth_token='.$oauthtoken.'&oauth_version=1.0&userid='.$userid;
		$basestring = 'GET&' . urlencode($base_url) . '&' . urlencode($url1.$url2);
		$oauth_signature = self::makeSignature($basestring,$consumer_secret.'&'.$oauthsecret);
		$url = $base_url . '?' . $url1 . '&oauth_signature=' . $oauth_signature . $url2;
		$cmd =  "curl --request GET '" . $url . "'";
		$return = shell_exec($cmd);
		return json_decode($return,true);
	}
	
	public function linkToUser() {
		@session_start();
		$nonce = hash('sha1', self::makeRandomString());
		$time = time();
		$consumer_key = $this->getConfiguration('client_id');
		$callback = urlencode(network::getNetworkAccess('external') . '/plugins/withings/core/php/callback.php?apikey=' . jeedom::getApiKey('withings') . '&eqLogic_id=' . $this->getId());
		$base_url = 'https://oauth.withings.com/account/request_token';
		$url1 = 'oauth_callback=' . $callback . '&oauth_consumer_key='.$consumer_key . '&oauth_nonce=' . $nonce;
		$url2 = '&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$time.'&oauth_version=1.0';
		$basestring = 'GET&' . urlencode($base_url) . '&' . urlencode($url1.$url2);
		$oauth_signature = self::makeSignature($basestring,$this->getConfiguration('client_secret').'&');
		$url = $base_url . '?' . $url1 . '&oauth_signature=' . $oauth_signature . $url2;
		$cmd =  "curl --request GET '" . $url . "'";
		$return = shell_exec($cmd);
		$return = str_replace('oauth_token=','',$return);
		$return = str_replace('_token_secret=','',$return);
		$listResult = explode('&oauth',$return);
		$oauth_token = $listResult[0];
		$oauth_secret = $listResult[1];
		$this->setConfiguration('oauthtoken',$oauth_token);
		$this->setConfiguration('oauthsecret',$oauth_secret);
		$this->save();
		return self::authorizeWithings($oauth_token,$oauth_secret,$consumer_key,$this->getConfiguration('client_secret'));
	}
	
	public function callWithings($_type,$_subtype,$_date='',$_startdate ='',$_enddate =''){
		$base_url='https://wbsapi.withings.net/' . $_type;
		$nonce = hash('sha1', self::makeRandomString());
		$time = time();
		$consumer_key = $this->getConfiguration('client_id');
		$consumer_secret = $this->getConfiguration('client_secret');
		$oauthtoken = $this->getConfiguration('oauthtoken');
		$oauthsecret = $this->getConfiguration('oauthsecret');
		$userid =  $this->getConfiguration('userid');
		if ($_date != ''){
			$url1 = $_subtype .'&date='.$_date.'&oauth_consumer_key='.$consumer_key . '&oauth_nonce=' . $nonce;
		} else {
			$url1 = $_subtype .'&oauth_consumer_key='.$consumer_key . '&oauth_nonce=' . $nonce;
		}
		if ($_startdate != '' && $_subtype != 'action=getmeas'){
			$url2 = '&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$time.'&oauth_token='.$oauthtoken.'&oauth_version=1.0&startdateymd='.$_startdate.'&enddateymd='.$_enddate.'&userid='.$userid;
		} else if ($_startdate != '' && $_subtype == 'action=getmeas'){
			$url2 = '&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$time.'&oauth_token='.$oauthtoken.'&oauth_version=1.0&startdate='.$_startdate.'&userid='.$userid;
		} else {
			$url2 = '&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$time.'&oauth_token='.$oauthtoken.'&oauth_version=1.0&userid='.$userid;
		}
		$basestring = 'GET&' . urlencode($base_url) . '&' . urlencode($url1.$url2);
		$oauth_signature = self::makeSignature($basestring,$consumer_secret.'&'.$oauthsecret);
		$url = $base_url . '?' . $url1 . '&oauth_signature=' . $oauth_signature . $url2;
		$cmd =  "curl --request GET '" . $url . "'";
		log::add('withings','debug',$url);
		$return = shell_exec($cmd);
		return $return;
	}

	public function getActivity($_date) {
		$type ='v2/measure';
		$subtype ='action=getactivity';
		$result = $this->callWithings($type,$subtype,$_date);
		log::add('withings','debug',$result);
		return json_decode($result,true);
	}

	public function getBody($_date) {
		$type ='measure';
		$subtype ='action=getmeas';
		$result = $this->callWithings($type,$subtype,'',$_date);
		log::add('withings','debug',$result);
		return json_decode($result,true);
	}

	public function getSleepMesure($_startdate, $_enddate) {
		$type ='v2/sleep';
		$subtype ='action=get';
		$result = $this->callWithings($type,$subtype,'',$_startdate,$_enddate);
		log::add('withings','debug',$result);
		return json_decode($result,true);
	}

	public function getSleepSummary($_startdate, $_enddate) {
		$type ='v2/sleep';
		$subtype ='action=getsummary';
		$result = $this->callWithings($type,$subtype,'',$_startdate,$_enddate);
		log::add('withings','debug',$result);
		return json_decode($result,true);
	}

	public function postSave() {
		$step = $this->getCmd(null, 'step');
		if (!is_object($step)) {
			$step = new withingsCmd();
			$step->setLogicalId('step');
			$step->setIsVisible(1);
			$step->setName(__('Pas', __FILE__));
			$step->setTemplate('dashboard', 'line');
			$step->setTemplate('mobile', 'line');
		}
		$step->setType('info');
		$step->setSubType('numeric');
		$step->setEqLogic_id($this->getId());
		$step->save();

		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new withingsCmd();
			$refresh->setLogicalId('refresh');
			$refresh->setIsVisible(1);
			$refresh->setName(__('Rafraichir', __FILE__));
		}
		$refresh->setType('action');
		$refresh->setSubType('other');
		$refresh->setEqLogic_id($this->getId());
		$refresh->save();

		$distance = $this->getCmd(null, 'distance');
		if (!is_object($distance)) {
			$distance = new withingsCmd();
			$distance->setLogicalId('distance');
			$distance->setIsVisible(1);
			$distance->setName(__('Distance', __FILE__));
			$distance->setTemplate('dashboard', 'line');
			$distance->setTemplate('mobile', 'line');
		}
		$distance->setType('info');
		$distance->setSubType('numeric');
		$distance->setUnite('km');
		$distance->setEqLogic_id($this->getId());
		$distance->save();

		$calories = $this->getCmd(null, 'calories');
		if (!is_object($calories)) {
			$calories = new withingsCmd();
			$calories->setLogicalId('calories');
			$calories->setIsVisible(1);
			$calories->setName(__('Calories', __FILE__));
			$calories->setTemplate('dashboard', 'line');
			$calories->setTemplate('mobile', 'line');
		}
		$calories->setType('info');
		$calories->setSubType('numeric');
		$calories->setEqLogic_id($this->getId());
		$calories->save();

		$elevation = $this->getCmd(null, 'elevation');
		if (!is_object($elevation)) {
			$elevation = new withingsCmd();
			$elevation->setLogicalId('elevation');
			$elevation->setIsVisible(1);
			$elevation->setName(__('Elévation', __FILE__));
			$elevation->setTemplate('dashboard', 'line');
			$elevation->setTemplate('mobile', 'line');
		}
		$elevation->setType('info');
		$elevation->setSubType('numeric');
		$elevation->setEqLogic_id($this->getId());
		$elevation->save();

		$wakeupduration = $this->getCmd(null, 'wakeupduration');
		if (!is_object($wakeupduration)) {
			$wakeupduration = new withingsCmd();
			$wakeupduration->setLogicalId('wakeupduration');
			$wakeupduration->setIsVisible(1);
			$wakeupduration->setName(__('Durée du réveil', __FILE__));
			$wakeupduration->setTemplate('dashboard', 'line');
			$wakeupduration->setTemplate('mobile', 'line');
		}
		$wakeupduration->setType('info');
		$wakeupduration->setSubType('numeric');
		$wakeupduration->setUnite('min');
		$wakeupduration->setEqLogic_id($this->getId());
		$wakeupduration->save();

		$durationtosleep = $this->getCmd(null, 'durationtosleep');
		if (!is_object($durationtosleep)) {
			$durationtosleep = new withingsCmd();
			$durationtosleep->setLogicalId('durationtosleep');
			$durationtosleep->setIsVisible(1);
			$durationtosleep->setName(__('Temps pour dormir', __FILE__));
			$durationtosleep->setTemplate('dashboard', 'line');
			$durationtosleep->setTemplate('mobile', 'line');
		}
		$durationtosleep->setType('info');
		$durationtosleep->setSubType('numeric');
		$durationtosleep->setUnite('min');
		$durationtosleep->setEqLogic_id($this->getId());
		$durationtosleep->save();

		$deepsleepduration = $this->getCmd(null, 'deepsleepduration');
		if (!is_object($deepsleepduration)) {
			$deepsleepduration = new withingsCmd();
			$deepsleepduration->setLogicalId('deepsleepduration');
			$deepsleepduration->setIsVisible(1);
			$deepsleepduration->setName(__('Sommeils profond', __FILE__));
			$deepsleepduration->setTemplate('dashboard', 'line');
			$deepsleepduration->setTemplate('mobile', 'line');
		}
		$deepsleepduration->setType('info');
		$deepsleepduration->setSubType('numeric');
		$deepsleepduration->setUnite('min');
		$deepsleepduration->setEqLogic_id($this->getId());
		$deepsleepduration->save();

		$lightsleepduration = $this->getCmd(null, 'lightsleepduration');
		if (!is_object($lightsleepduration)) {
			$lightsleepduration = new withingsCmd();
			$lightsleepduration->setLogicalId('lightsleepduration');
			$lightsleepduration->setIsVisible(1);
			$lightsleepduration->setName(__('Sommeil léger', __FILE__));
			$lightsleepduration->setTemplate('dashboard', 'line');
			$lightsleepduration->setTemplate('mobile', 'line');
		}
		$lightsleepduration->setType('info');
		$lightsleepduration->setSubType('numeric');
		$lightsleepduration->setUnite('min');
		$lightsleepduration->setEqLogic_id($this->getId());
		$lightsleepduration->save();

		$wakeupcount = $this->getCmd(null, 'wakeupcount');
		if (!is_object($wakeupcount)) {
			$wakeupcount = new withingsCmd();
			$wakeupcount->setLogicalId('wakeupcount');
			$wakeupcount->setIsVisible(1);
			$wakeupcount->setName(__('Nombre de réveils', __FILE__));
			$wakeupcount->setTemplate('dashboard', 'line');
			$wakeupcount->setTemplate('mobile', 'line');
		}
		$wakeupcount->setType('info');
		$wakeupcount->setSubType('numeric');
		$wakeupcount->setEqLogic_id($this->getId());
		$wakeupcount->save();

		$measuregrps1 = $this->getCmd(null, 'measuregrps1');
		if (!is_object($measuregrps1)) {
			$measuregrps1 = new withingsCmd();
			$measuregrps1->setLogicalId('measuregrps1');
			$measuregrps1->setIsVisible(1);
			$measuregrps1->setName(__('Poids', __FILE__));
			$measuregrps1->setTemplate('dashboard', 'line');
			$measuregrps1->setTemplate('mobile', 'line');
		}
		$measuregrps1->setType('info');
		$measuregrps1->setSubType('numeric');
		$measuregrps1->setUnite('kg');
		$measuregrps1->setEqLogic_id($this->getId());
		$measuregrps1->save();

		$measuregrps5 = $this->getCmd(null, 'measuregrps5');
		if (!is_object($measuregrps5)) {
			$measuregrps5 = new withingsCmd();
			$measuregrps5->setLogicalId('measuregrps5');
			$measuregrps5->setIsVisible(1);
			$measuregrps5->setName(__('Masse maigre', __FILE__));
			$measuregrps5->setTemplate('dashboard', 'line');
			$measuregrps5->setTemplate('mobile', 'line');
		}
		$measuregrps5->setType('info');
		$measuregrps5->setSubType('numeric');
		$measuregrps5->setUnite('kg');
		$measuregrps5->setEqLogic_id($this->getId());
		$measuregrps5->save();

		$measuregrps6 = $this->getCmd(null, 'measuregrps6');
		if (!is_object($measuregrps6)) {
			$measuregrps6 = new withingsCmd();
			$measuregrps6->setLogicalId('measuregrps6');
			$measuregrps6->setIsVisible(1);
			$measuregrps6->setName(__('Ratio masse grasse', __FILE__));
			$measuregrps6->setTemplate('dashboard', 'line');
			$measuregrps6->setTemplate('mobile', 'line');
		}
		$measuregrps6->setType('info');
		$measuregrps6->setSubType('numeric');
		$measuregrps6->setUnite('%');
		$measuregrps6->setEqLogic_id($this->getId());
		$measuregrps6->save();

		$measuregrps8 = $this->getCmd(null, 'measuregrps8');
		if (!is_object($measuregrps8)) {
			$measuregrps8 = new withingsCmd();
			$measuregrps8->setLogicalId('measuregrps8');
			$measuregrps8->setIsVisible(1);
			$measuregrps8->setName(__('Masse grasse', __FILE__));
			$measuregrps8->setTemplate('dashboard', 'line');
			$measuregrps8->setTemplate('mobile', 'line');
		}
		$measuregrps8->setType('info');
		$measuregrps8->setSubType('numeric');
		$measuregrps8->setUnite('kg');
		$measuregrps8->setEqLogic_id($this->getId());
		$measuregrps8->save();

		$measuregrps9 = $this->getCmd(null, 'measuregrps9');
		if (!is_object($measuregrps9)) {
			$measuregrps9 = new withingsCmd();
			$measuregrps9->setLogicalId('measuregrps9');
			$measuregrps9->setIsVisible(1);
			$measuregrps9->setName(__('Diastolic', __FILE__));
			$measuregrps9->setTemplate('dashboard', 'line');
			$measuregrps9->setTemplate('mobile', 'line');
		}
		$measuregrps9->setType('info');
		$measuregrps9->setSubType('numeric');
		$measuregrps9->setUnite('mmHg');
		$measuregrps9->setEqLogic_id($this->getId());
		$measuregrps9->save();

		$measuregrps10 = $this->getCmd(null, 'measuregrps10');
		if (!is_object($measuregrps10)) {
			$measuregrps10 = new withingsCmd();
			$measuregrps10->setLogicalId('measuregrps10');
			$measuregrps10->setIsVisible(1);
			$measuregrps10->setName(__('Systolic', __FILE__));
			$measuregrps10->setTemplate('dashboard', 'line');
			$measuregrps10->setTemplate('mobile', 'line');
		}
		$measuregrps10->setType('info');
		$measuregrps10->setSubType('numeric');
		$measuregrps10->setUnite('mmHg');
		$measuregrps10->setEqLogic_id($this->getId());
		$measuregrps10->save();

		$measuregrps11 = $this->getCmd(null, 'measuregrps11');
		if (!is_object($measuregrps11)) {
			$measuregrps11 = new withingsCmd();
			$measuregrps11->setLogicalId('measuregrps11');
			$measuregrps11->setIsVisible(1);
			$measuregrps11->setName(__('Rythme cardiaque', __FILE__));
			$measuregrps11->setTemplate('dashboard', 'line');
			$measuregrps11->setTemplate('mobile', 'line');
		}
		$measuregrps11->setType('info');
		$measuregrps11->setSubType('numeric');
		$measuregrps11->setUnite('bpm');
		$measuregrps11->setEqLogic_id($this->getId());
		$measuregrps11->save();

		$measuregrps54 = $this->getCmd(null, 'measuregrps54');
		if (!is_object($measuregrps54)) {
			$measuregrps54 = new withingsCmd();
			$measuregrps54->setLogicalId('measuregrps54');
			$measuregrps54->setIsVisible(1);
			$measuregrps54->setName(__('SP02', __FILE__));
			$measuregrps54->setTemplate('dashboard', 'line');
			$measuregrps54->setTemplate('mobile', 'line');
		}
		$measuregrps54->setType('info');
		$measuregrps54->setSubType('numeric');
		$measuregrps54->setUnite('%');
		$measuregrps54->setEqLogic_id($this->getId());
		$measuregrps54->save();
		try {
			$this->syncWithWithings();
		} catch (Exception $e) {

		}
	}

	public function syncWithWithings() {
		$activity = $this->getActivity(date('Y-m-d'));
		$step = $this->getCmd(null, 'step');
		if (is_object($step)) {
			if (isset($activity['body']['steps']) && $step->execCmd() != $step->formatValue($activity['body']['steps'])) {
				$step->setCollectDate('');
				$step->event($activity['body']['steps']);
			}
		}

		$distance = $this->getCmd(null, 'distance');
		if (is_object($distance)) {
			if (isset($activity['body']['distance']) && $distance->execCmd() != $distance->formatValue(round($activity['body']['distance'] / 1000, 2))) {
				$distance->setCollectDate('');
				$distance->event(round($activity['body']['distance'] / 1000, 2));
			}
		}

		$calories = $this->getCmd(null, 'calories');
		if (is_object($calories)) {
			if (isset($activity['body']['calories']) && $calories->execCmd() != $calories->formatValue($activity['body']['calories'])) {
				$calories->setCollectDate('');
				$calories->event($activity['body']['calories']);
			}
		}

		$elevation = $this->getCmd(null, 'elevation');
		if (is_object($elevation)) {
			if (isset($activity['body']['elevation']) && $elevation->execCmd() != $elevation->formatValue($activity['body']['elevation'])) {
				$elevation->setCollectDate('');
				$elevation->event($activity['body']['elevation']);
			}
		}

		$sleepSummary = $this->getSleepSummary(date('Y-m-d', strtotime('-1 days')), date('Y-m-d', strtotime('-1 days')));

		$wakeupduration = $this->getCmd(null, 'wakeupduration');
		if (is_object($wakeupduration)) {
			if (isset($sleepSummary['body']['series'][0]['data']['wakeupduration']) && $wakeupduration->execCmd() !== $wakeupduration->formatValue(round($sleepSummary['body']['series'][0]['data']['wakeupduration'] / 60, 2))) {
				$wakeupduration->setCollectDate('');
				$wakeupduration->event(round($sleepSummary['body']['series'][0]['data']['wakeupduration'] / 60, 2));
			}
		}

		$durationtosleep = $this->getCmd(null, 'durationtosleep');
		if (is_object($durationtosleep)) {
			if (isset($sleepSummary['body']['series'][0]['data']['durationtosleep']) && $durationtosleep->execCmd() != $durationtosleep->formatValue(round($sleepSummary['body']['series'][0]['data']['durationtosleep'] / 60, 2))) {
				$durationtosleep->setCollectDate('');
				$durationtosleep->event(round($sleepSummary['body']['series'][0]['data']['durationtosleep'] / 60, 2));
			}
		}

		$deepsleepduration = $this->getCmd(null, 'deepsleepduration');
		if (is_object($deepsleepduration)) {
			if (isset($sleepSummary['body']['series'][0]['data']['deepsleepduration']) && $deepsleepduration->execCmd() != $deepsleepduration->formatValue(round($sleepSummary['body']['series'][0]['data']['deepsleepduration'] / 60, 2))) {
				$deepsleepduration->setCollectDate('');
				$deepsleepduration->event(round($sleepSummary['body']['series'][0]['data']['deepsleepduration'] / 60, 2));
			}
		}

		$lightsleepduration = $this->getCmd(null, 'lightsleepduration');
		if (is_object($lightsleepduration)) {
			if (isset($sleepSummary['body']['series'][0]['data']['lightsleepduration']) && $lightsleepduration->execCmd() != $lightsleepduration->formatValue(round($sleepSummary['body']['series'][0]['data']['lightsleepduration'] / 60, 2))) {
				$lightsleepduration->setCollectDate('');
				$lightsleepduration->event(round($sleepSummary['body']['series'][0]['data']['lightsleepduration'] / 60, 2));
			}
		}

		$wakeupcount = $this->getCmd(null, 'wakeupcount');
		if (is_object($wakeupcount)) {
			if (isset($sleepSummary['body']['series'][0]['data']['wakeupcount']) && $wakeupcount->execCmd() != $wakeupcount->formatValue($sleepSummary['body']['series'][0]['data']['wakeupcount'])) {
				$wakeupcount->setCollectDate('');
				$wakeupcount->event($sleepSummary['body']['series'][0]['data']['wakeupcount']);
			}
		}

		$body = $this->getBody(strtotime(date('Y-m-d')));
		$foundMeasure = array();
		if (isset($body['body']['measuregrps'][0]['measures'])) {
			foreach ($body['body']['measuregrps'] as $measures) {
				foreach ($measures['measures'] as $measure) {
					if (!isset($foundMeasure[$measure['type']])) {
						$foundMeasure[$measure['type']] = true;
						$cmd = $this->getCmd(null, 'measuregrps' . $measure['type']);
						if (is_object($cmd)) {
							$value = round($measure['value'], 2);
							if ($measure['type'] == 1 || $measure['type'] == 5 || $measure['type'] == 6 || $measure['type'] == 8) {
								$value = round($value / 1000, 2);
							}
							if ($cmd->execCmd() != $cmd->formatValue($value)) {
								$cmd->setCollectDate('');
								$cmd->event($value);
							}
						}
					}
				}
			}
		}

		$mc = cache::byKey('withingsWidgetmobile' . $this->getId());
		$mc->remove();
		$mc = cache::byKey('withingsWidgetdashboard' . $this->getId());
		$mc->remove();
		$this->toHtml('mobile');
		$this->toHtml('dashboard');
		$this->refreshWidget();
	}

	/*     * **********************Getteur Setteur*************************** */

}

class withingsCmd extends cmd {
	/*     * *************************Attributs****************************** */

	public static $_widgetPossibility = array('custom' => false);

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = null) {
		if ($this->getType() == '') {
			return '';
		}
		$eqLogic = $this->getEqlogic();
		$eqLogic->syncWithWithings();
	}

	/*     * **********************Getteur Setteur*************************** */
}

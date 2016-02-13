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
require_once dirname(__FILE__) . '/../../core/php/withings.inc.php';

class withings extends eqLogic {
	/*     * *************************Attributs****************************** */

	private $_collectDate = '';

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

	/*     * *********************Methode d'instance************************* */

	public function registerNotification() {
		$callback = network::getNetworkAccess('external') . '/plugins/withings/core/php/pull.php?eqLogic_id=' . $this->getId() . '&apikey=' . config::byKey('api');
		$withings = $this->getWithings();
		return $withings->doRequest('notify?action=subscribe&userid=' . $this->getConfiguration('userid') . '&callbackurl=' . urlencode($callback) . '&comment=Jeedom');
	}

	public function listNotification() {
		$withings = $this->getWithings();
		return $withings->doRequest('notify?action=list');
	}

	public function revokeNotification($_callback) {
		$withings = $this->getWithings();
		return $withings->doRequest('notify?action=revoke&userid=' . $this->getConfiguration('userid') . '&callbackurl=' . urlencode($_callback));
	}

	public function linkToUser() {
		if (!class_exists('OAuth')) {
			throw new Exception('Classe OAuth non trouvée merci de l\'installer : "sudo apt-get update;sudo apt-get install -y php5-dev;sudo pecl install oauth;echo "extension=oauth.so" >> /etc/php5/cli/php.ini;echo "extension=oauth.so" >> /etc/php5/fpm/php.ini; sudo service php5-fpm restart"');
		}
		@session_start();
		$_SESSION['withings_Session'] = 0;
		$withings = new WithingsPHP(config::byKey('client_key', 'withings'), config::byKey('secret_key', 'withings'));
		return $withings->initSession(network::getNetworkAccess() . '/plugins/withings/core/php/callback.php?eqLogic_id=' . $this->getId());
	}

	public function getWithings() {
		if (!class_exists('OAuth')) {
			throw new Exception('Classe OAuth non trouvée merci de l\'installer : "sudo apt-get update;sudo apt-get install -y php5-dev;sudo pecl install oauth;echo "extension=oauth.so" >> /etc/php5/cli/php.ini;echo "extension=oauth.so" >> /etc/php5/fpm/php.ini; sudo service php5-fpm restart"');
		}
		$withings = new WithingsPHP(config::byKey('client_key', 'withings'), config::byKey('secret_key', 'withings'));
		$withings->setOAuthDetails($this->getConfiguration('token'), $this->getConfiguration('secret'));
		return $withings;
	}

	public function getActivity($_date) {
		$withings = $this->getWithings();
		return $withings->doRequest("v2/measure?action=getactivity&userid=" . $this->getConfiguration('userid') . '&date=' . $_date);
	}

	public function getBody($_date) {
		$withings = $this->getWithings();
		return $withings->doRequest("measure?action=getmeas&userid=" . $this->getConfiguration('userid') . '&startdate=' . $_date);
	}

	public function getSleepMesure($_startdate, $_enddate) {
		$withings = $this->getWithings();
		return $withings->doRequest("v2/sleep?action=get&userid=" . $this->getConfiguration('userid') . '&startdate=' . $_startdate . '&enddate=' . $_enddate);
	}

	public function getSleepSummary($_startdate, $_enddate) {
		$withings = $this->getWithings();
		return $withings->doRequest("v2/sleep?action=getsummary&startdateymd=" . $_startdate . '&enddate=' . $_enddate);
	}

	public function toHtml($_version = 'dashboard') {
		if ($this->getIsEnable() != 1) {
			return '';
		}
		if (!$this->hasRight('r')) {
			return '';
		}
		$_version = jeedom::versionAlias($_version);
		$mc = cache::byKey('withingsWidget' . jeedom::versionAlias($_version) . $this->getId());
		if ($mc->getValue() != '') {
			return preg_replace("/" . preg_quote(self::UIDDELIMITER) . "(.*?)" . preg_quote(self::UIDDELIMITER) . "/", self::UIDDELIMITER . mt_rand() . self::UIDDELIMITER, $mc->getValue());
		}
		$hidesommeil = $this->getConfiguration('hidesleep');
		$hideactivity = $this->getConfiguration('hideactivity');
		$hidemeasure = $this->getConfiguration('hidemesure');
		$totshow = $hidesommeil + $hideactivity + $hidemeasure;
		if ($totshow == 2) {
			$colsm = 'col-sm-12';
			$width = 130;
		} else if ($totshow == 1) {
			$colsm = 'col-sm-6';
			$width = 230;
		} else {
			$colsm = 'col-sm-4';
			$width = 330;
		}
		$replace = array(
			'#name#' => $this->getName(),
			'#id#' => $this->getId(),
			'#background_color#' => '#009ee3',
			'#eqLink#' => ($this->hasRight('w')) ? $this->getLinkToConfiguration() : '#',
			'#uid#' => 'withings' . $this->getId() . self::UIDDELIMITER . mt_rand() . self::UIDDELIMITER,
			'#showsommeil#' => $hidesommeil,
			'#showactivity#' => $hideactivity,
			'#showmesure#' => $hidemeasure,
			'#colsm#' => $colsm,
			'#width#' => $width,
		);

		foreach ($this->getCmd('info') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_history#'] = '';
			if ($cmd->getIsVisible() == 1) {
				$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
				$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
				$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
				if ($cmd->getIsHistorized() == 1) {
					$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
				}
			} else {
				$replace['#' . $cmd->getLogicalId() . '#'] = '';
			}
		}

		$refresh = $this->getCmd(null, 'refresh');
		$replace['#refresh_id#'] = $refresh->getId();

		$parameters = $this->getDisplay('parameters');
		if (is_array($parameters)) {
			foreach ($parameters as $key => $value) {
				$replace['#' . $key . '#'] = $value;
			}
		}

		$html = template_replace($replace, getTemplate('core', $_version, 'withings', 'withings'));
		cache::set('withingsWidget' . $_version . $this->getId(), $html, 0);
		return $html;
	}

	public function postSave() {
		$step = $this->getCmd(null, 'step');
		if (!is_object($step)) {
			$step = new withingsCmd();
			$step->setLogicalId('step');
			$step->setIsVisible(1);
			$step->setName(__('Pas', __FILE__));
			$step->setTemplate('dashboard', 'tile');
			$step->setTemplate('mobile', 'tile');
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
			$distance->setTemplate('dashboard', 'tile');
			$distance->setTemplate('mobile', 'tile');
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
			$calories->setTemplate('dashboard', 'tile');
			$calories->setTemplate('mobile', 'tile');
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
			$elevation->setTemplate('dashboard', 'tile');
			$elevation->setTemplate('mobile', 'tile');
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
			$wakeupduration->setTemplate('dashboard', 'tile');
			$wakeupduration->setTemplate('mobile', 'tile');
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
			$durationtosleep->setTemplate('dashboard', 'tile');
			$durationtosleep->setTemplate('mobile', 'tile');
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
			$deepsleepduration->setTemplate('dashboard', 'tile');
			$deepsleepduration->setTemplate('mobile', 'tile');
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
			$lightsleepduration->setTemplate('dashboard', 'tile');
			$lightsleepduration->setTemplate('mobile', 'tile');
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
			$wakeupcount->setTemplate('dashboard', 'tile');
			$wakeupcount->setTemplate('mobile', 'tile');
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
			$measuregrps1->setTemplate('dashboard', 'tile');
			$measuregrps1->setTemplate('mobile', 'tile');
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
			$measuregrps5->setTemplate('dashboard', 'tile');
			$measuregrps5->setTemplate('mobile', 'tile');
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
			$measuregrps6->setTemplate('dashboard', 'tile');
			$measuregrps6->setTemplate('mobile', 'tile');
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
			$measuregrps8->setTemplate('dashboard', 'tile');
			$measuregrps8->setTemplate('mobile', 'tile');
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
			$measuregrps9->setTemplate('dashboard', 'tile');
			$measuregrps9->setTemplate('mobile', 'tile');
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
			$measuregrps10->setTemplate('dashboard', 'tile');
			$measuregrps10->setTemplate('mobile', 'tile');
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
			$measuregrps11->setTemplate('dashboard', 'tile');
			$measuregrps11->setTemplate('mobile', 'tile');
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
			$measuregrps54->setTemplate('dashboard', 'tile');
			$measuregrps54->setTemplate('mobile', 'tile');
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

		$body = $this->getBody(date('Y-m-d'));
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
		$this->setCollectDate(date('Y-m-d H:i:s'));
		$this->toHtml('mobile');
		$this->toHtml('dashboard');
		$this->refreshWidget();
	}

	/*     * **********************Getteur Setteur*************************** */

	public function getCollectDate() {
		return $this->_collectDate;
	}

	public function setCollectDate($_collectDate) {
		$this->_collectDate = $_collectDate;
	}

}

class withingsCmd extends cmd {
	/*     * *************************Attributs****************************** */

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

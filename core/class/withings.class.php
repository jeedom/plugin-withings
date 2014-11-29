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


    /*     * ***********************Methode static*************************** */

    public static function pull() {
        foreach (self::byType('withings') as $withings) {
            if ($withings->getIsEnable() == 1) {
                $withings->syncWithWithings();
            }
        }
    }

    public static function getCallbackUri() {
        $url = config::byKey('externalAddr');
        if (strpos('http://', $url) != 0 && strpos('https://', $url) != 0) {
            $url = 'http://' . $url;
        }
        if (config::byKey('externalPort') != '' && config::byKey('externalPort') != 80) {
            return $url . ':' . config::byKey('externalPort') . '/plugins/withings/core/php/callback.php';
        } else {
            return $url . '/plugins/withings/core/php/callback.php';
        }
    }

    /*     * *********************Methode d'instance************************* */

    public function linkToUser() {
        session_start();
        $_SESSION['withings_Session'] = 0;
        $withings = new WithingsPHP(config::byKey('client_key', 'withings'), config::byKey('secret_key', 'withings'));
        return $withings->initSession(self::getCallbackUri() . '?eqLogic_id=' . $this->getId());
    }

    public function getWithings() {
        $fitbit = new WithingsPHP(config::byKey('client_key', 'withings'), config::byKey('secret_key', 'withings'));
        $fitbit->setOAuthDetails($this->getConfiguration('token'), $this->getConfiguration('secret'));
        return $fitbit;
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
        $_version = jeedom::versionAlias($_version);
        $mc = cache::byKey('withingsWidget' . $_version . $this->getId());
        if ($mc->getValue() != '') {
            return $mc->getValue();
        }
        $html = parent::toHtml($_version);
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
        $step->setUnite('');
        $step->setEventOnly(1);
        $step->setEqLogic_id($this->getId());
        $step->save();

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
        $distance->setEventOnly(1);
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
        $calories->setUnite('');
        $calories->setEventOnly(1);
        $calories->setEqLogic_id($this->getId());
        $calories->save();

        $elevation = $this->getCmd(null, 'elevation');
        if (!is_object($elevation)) {
            $elevation = new withingsCmd();
            $elevation->setLogicalId('elevation');
            $elevation->setIsVisible(1);
            $elevation->setName(__('Elevation', __FILE__));
            $elevation->setTemplate('dashboard', 'tile');
            $elevation->setTemplate('mobile', 'tile');
        }
        $elevation->setType('info');
        $elevation->setSubType('numeric');
        $elevation->setUnite('');
        $elevation->setEventOnly(1);
        $elevation->setEqLogic_id($this->getId());
        $elevation->save();


        $wakeupduration = $this->getCmd(null, 'wakeupduration');
        if (!is_object($wakeupduration)) {
            $wakeupduration = new withingsCmd();
            $wakeupduration->setLogicalId('wakeupduration');
            $wakeupduration->setIsVisible(1);
            $wakeupduration->setName(__('Durée du réveille', __FILE__));
            $wakeupduration->setTemplate('dashboard', 'tile');
            $wakeupduration->setTemplate('mobile', 'tile');
        }
        $wakeupduration->setType('info');
        $wakeupduration->setSubType('numeric');
        $wakeupduration->setUnite('min');
        $wakeupduration->setEventOnly(1);
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
        $durationtosleep->setEventOnly(1);
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
        $deepsleepduration->setEventOnly(1);
        $deepsleepduration->setEqLogic_id($this->getId());
        $deepsleepduration->save();

        $lightsleepduration = $this->getCmd(null, 'lightsleepduration');
        if (!is_object($lightsleepduration)) {
            $lightsleepduration = new withingsCmd();
            $lightsleepduration->setLogicalId('lightsleepduration');
            $lightsleepduration->setIsVisible(1);
            $lightsleepduration->setName(__('Sommeil légé', __FILE__));
            $lightsleepduration->setTemplate('dashboard', 'tile');
            $lightsleepduration->setTemplate('mobile', 'tile');
        }
        $lightsleepduration->setType('info');
        $lightsleepduration->setSubType('numeric');
        $lightsleepduration->setUnite('min');
        $lightsleepduration->setEventOnly(1);
        $lightsleepduration->setEqLogic_id($this->getId());
        $lightsleepduration->save();

        $wakeupcount = $this->getCmd(null, 'wakeupcount');
        if (!is_object($wakeupcount)) {
            $wakeupcount = new withingsCmd();
            $wakeupcount->setLogicalId('wakeupcount');
            $wakeupcount->setIsVisible(1);
            $wakeupcount->setName(__('Nombre de reveils', __FILE__));
            $wakeupcount->setTemplate('dashboard', 'tile');
            $wakeupcount->setTemplate('mobile', 'tile');
        }
        $wakeupcount->setType('info');
        $wakeupcount->setSubType('numeric');
        $wakeupcount->setUnite('');
        $wakeupcount->setEventOnly(1);
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
        $measuregrps1->setEventOnly(1);
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
        $measuregrps5->setEventOnly(1);
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
        $measuregrps6->setEventOnly(1);
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
        $measuregrps8->setEventOnly(1);
        $measuregrps8->setEqLogic_id($this->getId());
        $measuregrps8->save();

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
        $measuregrps11->setEventOnly(1);
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
        $measuregrps54->setEventOnly(1);
        $measuregrps54->setEqLogic_id($this->getId());
        $measuregrps54->save();

        $this->syncWithWithings();
    }

    public function syncWithWithings() {
        $activity = $this->getActivity(date('Y-m-d'));

        $step = $this->getCmd(null, 'step');
        if (is_object($step)) {
            if (isset($activity['body']['step']) && $step->execCmd() != $step->formatValue($activity['body']['step'])) {
                $step->setCollectDate('');
                $step->event($activity['body']['step']);
            }
        }

        $distance = $this->getCmd(null, 'distance');
        if (is_object($distance)) {
            if (isset($activity['body']['distance']) && $distance->execCmd() != $distance->formatValue($activity['body']['distance'] / 1000)) {
                $distance->setCollectDate('');
                $distance->event($activity['body']['distance'] / 1000);
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
            if (isset($sleepSummary['body']['series'][0]['data']['wakeupduration']) && $wakeupduration->execCmd() !== $wakeupduration->formatValue($sleepSummary['body']['series'][0]['data']['wakeupduration'] / 60)) {
                $wakeupduration->setCollectDate('');
                $wakeupduration->event($sleepSummary['body']['series'][0]['data']['wakeupduration'] / 60);
            }
        }

        $durationtosleep = $this->getCmd(null, 'durationtosleep');
        if (is_object($durationtosleep)) {
            if (isset($sleepSummary['body']['series'][0]['data']['durationtosleep']) && $durationtosleep->execCmd() != $durationtosleep->formatValue($sleepSummary['body']['series'][0]['data']['durationtosleep'] / 60)) {
                $durationtosleep->setCollectDate('');
                $durationtosleep->event($sleepSummary['body']['series'][0]['data']['durationtosleep'] / 60);
            }
        }

        $deepsleepduration = $this->getCmd(null, 'deepsleepduration');
        if (is_object($deepsleepduration)) {
            if (isset($sleepSummary['body']['series'][0]['data']['deepsleepduration']) && $deepsleepduration->execCmd() != $deepsleepduration->formatValue($sleepSummary['body']['series'][0]['data']['deepsleepduration'] / 60)) {
                $deepsleepduration->setCollectDate('');
                $deepsleepduration->event($sleepSummary['body']['series'][0]['data']['deepsleepduration'] / 60);
            }
        }

        $lightsleepduration = $this->getCmd(null, 'lightsleepduration');
        if (is_object($lightsleepduration)) {
            if (isset($sleepSummary['body']['series'][0]['data']['lightsleepduration']) && $lightsleepduration->execCmd() != $lightsleepduration->formatValue($sleepSummary['body']['series'][0]['data']['lightsleepduration'] / 60)) {
                $lightsleepduration->setCollectDate('');
                $lightsleepduration->event($sleepSummary['body']['series'][0]['data']['lightsleepduration'] / 60);
            }
        }

        $wakeupcount = $this->getCmd(null, 'wakeupcount');
        if (is_object($wakeupcount)) {
            if (isset($sleepSummary['body']['series'][0]['data']['wakeupcount']) && $wakeupcount->execCmd() != $wakeupcount->formatValue($sleepSummary['body']['series'][0]['data']['wakeupcount'])) {
                $wakeupcount->setCollectDate('');
                $wakeupcount->event($sleepSummary['body']['series'][0]['data']['wakeupcount']);
            }
        }

        $body = $this->getBody();
        if (isset($body['body']['measuregrps'][0]['measures'])) {
            foreach ($body['body']['measuregrps'][0]['measures'] as $measure) {
                $cmd = $this->getCmd(null, 'measuregrps' . $measure['type']);
                if (is_object($cmd)) {
                    $value = $measure['value'];
                    if ($measure['type'] == 1 || $measure['type'] == 5 || $measure['type'] == 6 || $measure['type'] == 8) {
                        $value = $value / 1000;
                    }
                    if ($cmd->execCmd() != $cmd->formatValue($value)) {
                        $cmd->setCollectDate('');
                        $cmd->event($value);
                    }
                }
            }
        }
    }

    /*     * **********************Getteur Setteur*************************** */
}

class withingsCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    public function toHtml($_version = 'dashboard', $options = '', $_cmdColor = NULL, $_cache = 2) {
        $html = parent::toHtml($_version, $options, $_cmdColor, $_cache);
        $replace = array(
            '#goal#' => $this->getConfiguration('goal'),
        );
        return template_replace($replace, $html);
    }

    public function execute($_options = null) {
        return '';
    }

    /*     * **********************Getteur Setteur*************************** */
}

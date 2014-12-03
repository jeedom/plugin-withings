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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function withings_install() {
    if (!class_exists('OAuth')) {
        @exec('{ sudo apt-get update;sudo apt-get install -y php5-dev;sudo pecl install oauth;echo "extension=oauth.so" >> /etc/php5/cli/php.ini;echo "extension=oauth.so" >> /etc/php5/fpm/php.ini; sudo service php5-fpm restart } &"');
    }
    $cron = cron::byClassAndFunction('withings', 'pull');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('withing');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setSchedule('*/30 * * * *');
        $cron->save();
    }
}

function withings_update() {
    $cron = cron::byClassAndFunction('withings', 'pull');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('withing');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setSchedule('*/30 * * * *');
        $cron->save();
    }
    $cron->stop();
}

function withings_remove() {
    $cron = cron::byClassAndFunction('withings', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
}

?>

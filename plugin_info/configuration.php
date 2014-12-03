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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
    <fieldset>
        <?php
        if (!class_exists('OAuth')) {
            echo '<div class="alert alert-danger">';
            echo 'Classe OAuth non trouvée merci de l\'installer : "sudo apt-get update;sudo apt-get install -y php5-dev;sudo pecl install oauth;echo "extension=oauth.so" >> /etc/php5/cli/php.ini;echo "extension=oauth.so" >> /etc/php5/fpm/php.ini; sudo service php5-fpm restart"';
            echo '</div>';
        } ?>
    </fieldset>
</form>


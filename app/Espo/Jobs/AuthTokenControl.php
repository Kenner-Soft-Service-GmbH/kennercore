<?php
/**
 * This file is part of EspoCRM and/or TreoCore, and/or KennerCore.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2020 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoCore is EspoCRM-based Open Source application.
 * Copyright (C) 2017-2020 TreoLabs GmbH
 * Website: https://treolabs.com
 *
 * KennerCore is TreoCore-based Open Source application.
 * Copyright (C) 2020 Kenner Soft Service GmbH
 * Website: https://kennersoft.de
 *
 * KennerCore as well as TreoCore and EspoCRM is free software:
 * you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * KennerCore as well as TreoCore and EspoCRM is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of
 * the "KennerCore", "EspoCRM" and "TreoCore" words.
 */

namespace Espo\Jobs;

use \Espo\Core\Exceptions;

class AuthTokenControl extends \Espo\Core\Jobs\Base
{
    public function run()
    {
        $authTokenLifetime = $this->getConfig()->get('authTokenLifetime');
        $authTokenMaxIdleTime = $this->getConfig()->get('authTokenMaxIdleTime');

        if (!$authTokenLifetime && !$authTokenMaxIdleTime) {
            return;
        }

        $whereClause = array(
            'isActive' => true
        );

        if ($authTokenLifetime) {
            $dt = new \DateTime();
            $dt->modify('-' . $authTokenLifetime . ' hours');
            $authTokenLifetimeThreshold = $dt->format('Y-m-d H:i:s');

            $whereClause['createdAt<'] = $authTokenLifetimeThreshold;
        }

        if ($authTokenMaxIdleTime) {
            $dt = new \DateTime();
            $dt->modify('-' . $authTokenMaxIdleTime . ' hours');
            $authTokenMaxIdleTimeThreshold = $dt->format('Y-m-d H:i:s');

            $whereClause['lastAccess<'] = $authTokenMaxIdleTimeThreshold;
        }

        $tokenList = $this->getEntityManager()->getRepository('AuthToken')->where($whereClause)->limit(0, 500)->find();

        foreach ($tokenList as $token) {
            $token->set('isActive', false);
            $this->getEntityManager()->saveEntity($token);
        }
    }
}


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

namespace Espo\Core\Utils\Authentication;

use \Espo\Core\Exceptions\Error;

class Espo extends Base
{
    /**
     * @param string $username
     * @param string $password
     * @param mixed  $authToken
     * @param mixed  $isPortal
     *
     * @return mixed
     * @throws Error
     */
    public function login($username, $password, \Espo\Entities\AuthToken $authToken = null, $isPortal = null)
    {
        // is system updating ?
        $this->isUpdating($authToken);

        if ($authToken) {
            $hash = $authToken->get('hash');
        } else {
            $hash = $this->getPasswordHash()->hash($password);
        }

        $user = $this->getEntityManager()->getRepository('User')->findOne(array(
            'whereClause' => array(
                'userName' => $username,
                'password' => $hash
            )
        ));

        return $user;
    }

    /**
     * @param mixed $authToken
     *
     * @throws Error
     */
    protected function isUpdating($authToken)
    {
        if (is_null($authToken) && !empty($this->getConfig()->get('isUpdating'))) {
            throw new Error('System is updating now! Please try later.');
        }
    }
}

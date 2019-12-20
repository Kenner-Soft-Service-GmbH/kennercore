<?php
/**
 * This file is part of EspoCRM and/or TreoCore.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2019 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoCore is EspoCRM-based Open Source application.
 * Copyright (C) 2017-2019 TreoLabs GmbH
 * Website: https://treolabs.com
 *
 * TreoCore as well as EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TreoCore as well as EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
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
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word
 * and "TreoCore" word.
 */

namespace Treo\SelectManagers;

/**
 * Class Account
 *
 * @package Treo\SelectManagers
 */
class Account extends \Espo\Core\SelectManagers\Base
{
    /**
     * @param $result
     */
    protected function filterPartners(&$result)
    {
        $result['whereClause'][] = array(
            'type' => 'Partner'
        );
    }

    /**
     * @param $result
     */
    protected function filterCustomers(&$result)
    {
        $result['whereClause'][] = array(
            'type' => 'Customer'
        );
    }

    /**
     * @param $result
     */
    protected function filterResellers(&$result)
    {
        $result['whereClause'][] = array(
            'type' => 'Reseller'
        );
    }

    /**
     * @param $result
     *
     * @throws \Exception
     */
    protected function filterRecentlyCreated(&$result)
    {
        $dt = new \DateTime('now');
        $dt->modify('-7 days');

        $result['whereClause'][] = array(
            'createdAt>=' => $dt->format('Y-m-d H:i:s')
        );
    }

    /**
     * @param $result
     */
    protected function accessPortalOnlyAccount(&$result)
    {
        $accountIdList = $this->getUser()->getLinkMultipleIdList('accounts');

        if (count($accountIdList)) {
            $result['whereClause'][] = array(
                'id' => $accountIdList
            );
        } else {
            $result['whereClause'][] = array(
                'id' => null
            );
        }
    }
}

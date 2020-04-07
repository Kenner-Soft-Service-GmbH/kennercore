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
 * Copyright (C) 2020 KenerSoft Service GmbH
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

declare(strict_types=1);

namespace Treo\Controllers;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Slim\Http\Request;
use Treo\Core\EventManager\Event;

/**
 * Class MassActions
 *
 * @author r.ratsun <r.ratsun@treolabs.com>
 */
class MassActions extends \Espo\Core\Controllers\Base
{

    /**
     * @param array     $params
     * @param \stdClass $data
     * @param Request   $request
     *
     * @return array
     */
    public function actionMassUpdate(array $params, \stdClass $data, Request $request): array
    {
        if (!$request->isPut() || !isset($params['scope'])) {
            throw new BadRequest();
        }
        if (!$this->getAcl()->check($params['scope'], 'edit')) {
            throw new Forbidden();
        }
        if (empty($data->attributes)) {
            throw new BadRequest();
        }

        return $this->getService('MassActions')->massUpdate($params['scope'], $data);
    }

    /**
     * @param array     $params
     * @param \stdClass $data
     * @param Request   $request
     *
     * @return array
     * @throws BadRequest
     * @throws Forbidden
     */
    public function actionMassDelete(array $params, \stdClass $data, Request $request): array
    {
        if (!$request->isPost() || !isset($params['scope'])) {
            throw new BadRequest();
        }
        if (!$this->getAcl()->check($params['scope'], 'delete')) {
            throw new Forbidden();
        }

        $event = new Event(['params' => $params, 'data' => $data, 'request' => $request]);
        $this
            ->getContainer()
            ->get('eventManager')
            ->dispatch($params['scope'] . 'Controller', 'beforeActionMassDelete', $event);


        return $this->getService('MassActions')->massDelete($params['scope'], $data);
    }

    /**
     * Action add relation
     *
     * @param array     $params
     * @param \stdClass $data
     * @param Request   $request
     *
     * @return bool
     *
     * @throws BadRequest
     * @throws Forbidden
     */
    public function actionAddRelation(array $params, \stdClass $data, Request $request): bool
    {
        if (!$request->isPost()) {
            throw new BadRequest();
        }
        if (empty($data->ids) || empty($data->foreignIds) || !isset($params['scope']) || !isset($params['link'])) {
            throw new BadRequest();
        }
        if (!$this->getAcl()->check($params['scope'], 'edit')) {
            throw new Forbidden();
        }

        return $this
            ->getService('MassActions')
            ->addRelation($data->ids, $data->foreignIds, $params['scope'], $params['link']);
    }

    /**
     * Action remove relation
     *
     * @param array     $params
     * @param \stdClass $data
     * @param Request   $request
     *
     * @return bool
     *
     * @throws BadRequest
     * @throws Forbidden
     */
    public function actionRemoveRelation(array $params, \stdClass $data, Request $request): bool
    {
        if (!$request->isDelete()) {
            throw new BadRequest();
        }
        if (empty($data->ids) || empty($data->foreignIds) || !isset($params['scope']) || !isset($params['link'])) {
            throw new BadRequest();
        }
        if (!$this->getAcl()->check($params['scope'], 'edit')) {
            throw new Forbidden();
        }

        return $this
            ->getService('MassActions')
            ->removeRelation($data->ids, $data->foreignIds, $params['scope'], $params['link']);
    }
}

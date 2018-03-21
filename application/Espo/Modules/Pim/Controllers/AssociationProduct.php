<?php
/**
 * This file is part of EspoCRM and/or TreoPIM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2018 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoPIM is EspoCRM-based Open Source Product Information Management application.
 * Copyright (C) 2017-2018 Zinit Solutions GmbH
 * Website: http://www.treopim.com
 *
 * TreoPIM as well as EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TreoPIM as well as EspoCRM is distributed in the hope that it will be useful,
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
 * and "TreoPIM" word.
 */

namespace Espo\Modules\Pim\Controllers;

use Espo\Core\Controllers\Base;
use Espo\Core\Exceptions;
use Slim\Http\Request;
use Espo\Core\Utils\Json;

/**
 * ChannelProductAttributeValue controller
 *
 * @author y.haiduchyk <y.haiduchyk@zinitsolutions.com>
 */
class AssociationProduct extends AbstractTechnicalController
{

    /**
     * Action Get
     *
     * @param array   $params
     * @param array   $data
     * @param Request $request
     *
     * @return mixed
     * @throws Exceptions\BadRequest
     * @throws Exceptions\Forbidden
     * @throws Exceptions\NotFound
     */
    public function actionRead($params, $data, Request $request)
    {
        if (!$this->isValidReadAction($params, $request)) {
            throw new Exceptions\BadRequest();
        }

        // check Acl
        if (!$this->getAcl()->check('Product', 'read') || !$this->getAcl()->check('Association', 'read')) {
            throw new Exceptions\Forbidden();
        }

        // get data
        $result = $this->getService('AssociationProduct')->getAssociationProduct($params['id']);

        if (empty($result)) {
            throw new Exceptions\NotFound();
        }

        return $result;
    }

    /**
     * Action create
     *
     * @param array   $params
     * @param array   $data
     * @param Request $request
     *
     * @return bool
     * @throws Exceptions\BadRequest
     */
    public function actionCreate($params, $data, Request $request): bool
    {
        // prepare data
        $data = Json::decode(Json::encode($data), true);

        // check Request
        if (!$this->isValidCreateAction($data, $request)) {
            throw new Exceptions\BadRequest();
        }

        // check Acl
        if (!$this->getAcl()->check('Product', 'edit')) {
            throw new Forbidden();
        }

        // Crate value
        return $this->getService('AssociationProduct')->createAssociationProduct($data);
    }

    /**
     * @param array   $params
     * @param array   $data
     * @param Request $request
     *
     * @return bool
     * @throws Exceptions\BadRequest
     * @throws Exceptions\Forbidden
     */
    public function actionUpdate($params, $data, Request $request): bool
    {
        // prepare data
        $data = Json::decode(Json::encode($data), true);

        // check request
        if (!$this->isValidUpdateAction($params, $data, $request)) {
            throw new Exceptions\BadRequest();
        }

        // check Acl
        if (!$this->getAcl()->check('Product', 'edit')) {
            throw new Exceptions\Forbidden();
        }

        // update Data
        return $this
            ->getService('AssociationProduct')
            ->updateAssociationProduct($params['id'], $data);
    }

    /**
     * Delete value
     *
     * @param array   $params
     * @param array   $data
     * @param Request $request
     *
     * @return bool
     */
    public function actionDelete($params, $data, Request $request): bool
    {
        // check action
        if (!$this->isValidDeleteAction($params, $request)) {
            throw new BadRequest();
        }

        // check Acl
        if (!$this->getAcl()->check('Product', 'edit')) {
            throw new Forbidden();
        }

        return $this
            ->getService('AssociationProduct')
            ->deleteAssociationProduct($params['id']);
    }
}

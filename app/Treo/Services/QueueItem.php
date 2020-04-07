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

namespace Treo\Services;

use Espo\ORM\Entity;

/**
 * Class QueueItem
 *
 * @author r.ratsun@zinitsolutions.com
 */
class QueueItem extends \Espo\Core\Templates\Services\Base
{
    /**
     * @var array
     */
    private $services = [];

    /**
     * @inheritdoc
     */
    public function prepareEntityForOutput(Entity $entity)
    {
        parent::prepareEntityForOutput($entity);

        // prepare entity
        $entity->set('actions', $this->getItemActions($entity));
    }

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();

        $this->addDependency('language');
    }

    /**
     * @param Entity $entity
     *
     * @return array
     */
    protected function getItemActions(Entity $entity): array
    {
        // prepare result
        $result = [];

        // prepare service name
        $serviceName = $this->getServiceName($entity);

        // prepare action statuses
        $statuses = ['Pending', 'Running', 'Success', 'Failed'];

        if (in_array($entity->get('status'), $statuses)) {
            // create service
            if (!isset($this->services[$serviceName])) {
                $this->services[$serviceName] = $this->getServiceFactory()->create($serviceName);
            }

            // prepare methodName
            $methodName = "get" . $entity->get('status') . "StatusActions";

            // prepare result
            $result = $this->services[$serviceName]->$methodName($entity);
        }

        return $result;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function exception(string $key): string
    {
        return $this->getInjection('language')->translate($key, 'exceptions', 'QueueItem');
    }

    /**
     * @param Entity $entity
     *
     * @return string
     */
    protected function getServiceName(Entity $entity): string
    {
        $serviceName = (string)$entity->get('serviceName');
        if (empty($serviceName) || !$this->checkExists($serviceName)) {
            $serviceName = 'QueueManagerBase';
        }

        return $serviceName;
    }

    /**
     * @param string $serviceName
     *
     * @return bool
     */
    protected function checkExists(string $serviceName): bool
    {
        return $this->getServiceFactory()->checkExists($serviceName);
    }
}

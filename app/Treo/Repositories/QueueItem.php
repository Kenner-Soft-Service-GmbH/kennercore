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
declare(strict_types=1);

namespace Treo\Repositories;

use Espo\ORM\Entity;

/**
 * Class QueueItem
 *
 * @author r.ratsun@zinitsolutions.com
 */
class QueueItem extends \Espo\Core\Templates\Repositories\Base
{
    /**
     * @inheritdoc
     */
    protected function afterSave(Entity $entity, array $options = [])
    {
        // call parent
        parent::afterSave($entity, $options);

        // unset
        if (in_array($entity->get('status'), ['Canceled', 'Closed'])) {
            $this->unsetItem((int)$entity->get('stream'), (string)$entity->get('id'));
        }
    }

    /**
     * @inheritdoc
     */
    protected function afterRemove(Entity $entity, array $options = [])
    {
        // call parent
        parent::afterRemove($entity, $options);

        // unset
        $this->unsetItem((int)$entity->get('stream'), (string)$entity->get('id'));
    }

    /**
     * @inheritdoc
     */
    protected function init()
    {
        // call parent
        parent::init();

        $this->addDependency('queueManager');
    }

    /**
     * @param int    $stream
     * @param string $id
     */
    protected function unsetItem(int $stream, string $id): void
    {
        $this->getInjection('queueManager')->unsetItem($stream, $id);
    }
}

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

namespace Treo\Core\Formula\Functions\EntityGroup;

use Espo\Core\Exceptions\Error;
use Espo\ORM\EntityManager;
use Espo\Core\Formula\Functions\EntityGroup\SumRelatedType as EspoSumRelatedType;
use Treo\Core\SelectManagerFactory;

/**
 * Class SumRelatedType
 *
 * @author r.ratsun <r.ratsun@zinitsolutions.com>
 */
class SumRelatedType extends EspoSumRelatedType
{
    /**
     * @inheritdoc
     */
    public function process(\StdClass $item)
    {
        if (!property_exists($item, 'value')) {
            throw new Error();
        }

        if (!is_array($item->value)) {
            throw new Error();
        }

        if (count($item->value) < 2) {
            throw new Error();
        }

        $link = $this->evaluate($item->value[0]);

        if (empty($link)) {
            throw new Error("No link passed to sumRelated function.");
        }

        $field = $this->evaluate($item->value[1]);

        if (empty($field)) {
            throw new Error("No field passed to sumRelated function.");
        }

        $filter = null;
        if (count($item->value) > 2) {
            $filter = $this->evaluate($item->value[2]);
        }

        $entity = $this->getEntity();

        $foreignEntityType = $entity->getRelationParam($link, 'entity');

        if (empty($foreignEntityType)) {
            throw new Error();
        }

        $foreignSelectManager = $this->getSelectManagerFactory()->create($foreignEntityType);

        $foreignLink = $entity->getRelationParam($link, 'foreign');

        if (empty($foreignLink)) {
            throw new Error("No foreign link for link {$link}.");
        }

        $selectParams = $foreignSelectManager->getEmptySelectParams();

        if ($filter) {
            $foreignSelectManager->applyFilter($filter, $selectParams);
        }

        $selectParams['select'] = [[$foreignLink . '.id', 'foreignId'], 'SUM:' . $field];

        $foreignSelectManager->addJoin($foreignLink, $selectParams);

        $selectParams['groupBy'] = [$foreignLink . '.id'];
        // @todo treoinject. Espo bug fix
        $selectParams['whereClause'][] = [$foreignLink . '.id' => $entity->get('id')];

        $this->handleSelectParams($foreignEntityType, $selectParams);

        $rowList = $this->query($foreignEntityType, $selectParams);

        if (empty($rowList)) {
            return 0;
        }

        return $rowList[0]['SUM:' . $field];
    }

    /**
     * Get entity manager
     *
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->getInjection('entityManager');
    }

    /**
     * Get select manager factory
     *
     * @return SelectManagerFactory
     */
    protected function getSelectManagerFactory(): SelectManagerFactory
    {
        return $this->getInjection('selectManagerFactory');
    }

    /**
     * Handle select params
     *
     * @param string $foreignEntityType
     * @param array $selectParams
     */
    protected function handleSelectParams(string $foreignEntityType, array $selectParams)
    {
        $this
            ->getEntityManager()
            ->getRepository($foreignEntityType)
            ->handleSelectParams($selectParams);
    }

    /**
     * Execute query
     *
     * @param string $foreignEntityType
     * @param array $selectParams
     *
     * @return array
     */
    protected function query(string $foreignEntityType, array $selectParams): array
    {
        $sql = $this->getEntityManager()->getQuery()->createSelectQuery($foreignEntityType, $selectParams);

        $pdo = $this->getEntityManager()->getPDO();
        $sth = $pdo->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }
}

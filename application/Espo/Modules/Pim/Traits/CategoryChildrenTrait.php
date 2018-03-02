<?php
/**
 * This file is part of EspoCRM and/or TreoPIM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2018 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoPIM ist Open Source Product Information Managegement (PIM) application,
 * based on EspoCRM.
 * Copyright (C) 2017-2018 Zinit Solutions GmbH
 * Website: http://www.treopim.com
 *
 * TreoPIM as well es EspoCRM is free software: you can redistribute it and/or modify
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

namespace Espo\Modules\Pim\Traits;

/**
 * CategoryTrait trait
 *
 * @author r.ratsun <r.ratsun@zinitsolutions.com>
 */
trait CategoryChildrenTrait
{

    /**
     * Get all category children by recursive
     *
     * @param string $categoryId
     * @param array  $data
     * @return array
     */
    public function getCategoryChildren(string $categoryId, array $data)
    {
        // get children
        $children = $this->getDbCategoryChildren($categoryId);

        // merge data
        $data = array_merge($data, $children);

        // get children in child
        foreach ($children as $childCategoryId) {
            $data = $this->getCategoryChildren($childCategoryId, $data);
        }

        return $data;
    }

    /**
     * Get category children from DB
     *
     * @param string $categoryId
     * @return array
     */
    protected function getDbCategoryChildren($categoryId)
    {
        $pdo = $this->getEntityManager()->getPDO();

        $sql = 'SELECT
          id
        FROM
          category
        WHERE
          category_parent_id ='.$pdo->quote($categoryId).'
          AND is_active = 1
          AND deleted = 0';

        $sth = $pdo->prepare($sql);
        $sth->execute();

        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return (!empty($result)) ? array_column($result, 'id') : [];
    }
}

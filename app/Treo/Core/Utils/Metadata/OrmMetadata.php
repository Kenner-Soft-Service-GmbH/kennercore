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
 * KennerCore is TreoCore-based Open Source application.
 * Copyright (C) 2020 KenerSoft Service GmbH
 * Website: https://kennersoft.de
 *
 * TreoCore as well as EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TreoCore as well as EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
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

declare(strict_types=1);

namespace Treo\Core\Utils\Metadata;

use Espo\Core\Utils\Metadata\OrmMetadata as Base;

/**
 * Class OrmMetadata
 *
 * @author r.ratsun <r.ratsun@treolabs.com>
 */
class OrmMetadata extends Base
{
    /**
     * @inheritDoc
     */
    public function getData($reload = false)
    {
        return $this->unsetLinkName(parent::getData($reload));
    }

    /**
     * Unset link field name if it needs
     *
     * @param array $data
     *
     * @return array
     */
    protected function unsetLinkName(array $data): array
    {
        /** @var array $entityDefs */
        $entityDefs = $this->metadata->get('entityDefs', []);

        foreach ($entityDefs as $scope => $rows) {
            if (!isset($rows['links'])) {
                continue 1;
            }

            foreach ($rows['links'] as $link => $settings) {
                if (isset($settings['type'])) {
                    if ($settings['type'] == 'belongsTo'
                        && !isset($entityDefs[$settings['entity']]['fields']['name'])
                        && isset($data[$scope]['fields'][$link . 'Name'])) {
                        unset($data[$scope]['fields'][$link . 'Name']);
                    }
                    if ($settings['type'] == 'hasMany'
                        && !isset($entityDefs[$settings['entity']]['fields']['name'])
                        && isset($data[$scope]['fields'][$link . 'Names'])) {
                        unset($data[$scope]['fields'][$link . 'Names']);
                    }
                }
            }
        }

        return $data;
    }
}

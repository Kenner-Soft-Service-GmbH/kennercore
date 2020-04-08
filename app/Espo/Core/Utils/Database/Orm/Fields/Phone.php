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

namespace Espo\Core\Utils\Database\Orm\Fields;

class Phone extends Base
{
    protected function load($fieldName, $entityName)
    {
        return array(
            $entityName => array(
                'fields' => array(
                    $fieldName => array(
                        'select' => 'phoneNumbers.name',
                        'where' =>
                        array (
                            'LIKE' => \Espo\Core\Utils\Util::toUnderScore($entityName) . ".id IN (
                                SELECT entity_id
                                FROM entity_phone_number
                                JOIN phone_number ON phone_number.id = entity_phone_number.phone_number_id
                                WHERE
                                    entity_phone_number.deleted = 0 AND entity_phone_number.entity_type = '{$entityName}' AND
                                    phone_number.deleted = 0 AND phone_number.name LIKE {value}
                            )",
                            '=' => array(
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersMultiple']],
                                'sql' => 'phoneNumbersMultiple.name = {value}',
                                'distinct' => true
                            ),
                            '<>' => array(
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersMultiple']],
                                'sql' => 'phoneNumbersMultiple.name <> {value}',
                                'distinct' => true
                            ),
                            'IN' => array(
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersMultiple']],
                                'sql' => 'phoneNumbersMultiple.name IN {value}',
                                'distinct' => true
                            ),
                            'NOT IN' => array(
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersMultiple']],
                                'sql' => 'phoneNumbersMultiple.name NOT IN {value}',
                                'distinct' => true
                            ),
                            'IS NULL' => array(
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersMultiple']],
                                'sql' => 'phoneNumbersMultiple.name IS NULL',
                                'distinct' => true
                            ),
                            'IS NOT NULL' => array(
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersMultiple']],
                                'sql' => 'phoneNumbersMultiple.name IS NOT NULL',
                                'distinct' => true
                            )
                        ),
                        'orderBy' => 'phoneNumbers.name {direction}',
                    ),
                    $fieldName .'Data' => array(
                        'type' => 'text',
                        'notStorable' => true
                    ),
                    $fieldName . 'Numeric' => [
                        'type' => 'varchar',
                        'notStorable' => true,
                        'where' => [
                            'LIKE' => \Espo\Core\Utils\Util::toUnderScore($entityName) . ".id IN (
                                SELECT entity_id
                                FROM entity_phone_number
                                JOIN phone_number ON phone_number.id = entity_phone_number.phone_number_id
                                WHERE
                                    entity_phone_number.deleted = 0 AND entity_phone_number.entity_type = '{$entityName}' AND
                                    phone_number.deleted = 0 AND phone_number.numeric LIKE {value}
                            )",
                            '=' => [
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersNumericMultiple']],
                                'sql' => 'phoneNumbersNumericMultiple.numeric = {value}',
                                'distinct' => true
                            ],
                            '<>' => [
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersNumericMultiple']],
                                'sql' => 'phoneNumbersNumericMultiple.numeric <> {value}',
                                'distinct' => true
                            ],
                            'IN' => [
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersNumericMultiple']],
                                'sql' => 'phoneNumbersNumericMultiple.numeric IN {value}',
                                'distinct' => true
                            ],
                            'NOT IN' => [
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersNumericMultiple']],
                                'sql' => 'phoneNumbersNumericMultiple.numeric NOT IN {value}',
                                'distinct' => true
                            ],
                            'IS NULL' => [
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersNumericMultiple']],
                                'sql' => 'phoneNumbersNumericMultiple.numeric IS NULL',
                                'distinct' => true
                            ],
                            'IS NOT NULL' => [
                                'leftJoins' => [['phoneNumbers', 'phoneNumbersNumericMultiple']],
                                'sql' => 'phoneNumbersNumericMultiple.numeric IS NOT NULL',
                                'distinct' => true
                            ]
                        ]
                    ]
                ),
                'relations' => [
                    'phoneNumbers' => [
                        'type' => 'manyMany',
                        'entity' => 'PhoneNumber',
                        'relationName' => 'entityPhoneNumber',
                        'midKeys' => [
                            'entityId',
                            'phoneNumberId'
                        ],
                        'conditions' => [
                            'entityType' => $entityName
                        ],
                        'additionalColumns' => [
                            'entityType' => [
                                'type' => 'varchar',
                                'len' => 100
                            ],
                            'primary' => [
                                'type' => 'bool',
                                'default' => false
                            ]
                        ]
                    ]
                ]
            )
        );
    }
}

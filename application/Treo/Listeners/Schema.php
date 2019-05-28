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

declare(strict_types=1);

namespace Treo\Listeners;

use Treo\Core\EventManager\Event;

/**
 * Class Schema
 *
 * @author r.ratsun r.ratsun@treolabs.com
 */
class Schema extends AbstractListener
{
    /**
     * Prepare entityDefs before rebuild action
     *
     * @param Event $event
     */
    public function beforeRebuild(Event $event)
    {
        // prepare LONGTEXT default
        $this->prepareLongTextDefault($event);
    }

    /**
     * Prepare comment default value
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareCommentDefaultValue(array $data): array
    {
        foreach ($data as $key => $query) {
            if (preg_match("/COMMENT 'default={(.*)}'/s", $query)) {
                $data[$key] = str_replace("\n", "{break}", $query);
            }
        }

        return $data;
    }

    /**
     * Prepare LONGTEXT default value
     *
     * @param Event $event
     */
    protected function prepareLongTextDefault(Event $event)
    {
        $queries = $this->prepareCommentDefaultValue($event->getArgument('queries'));

        foreach ($queries as $key => $query) {
            // prepare fields
            $fields = [];
            while (preg_match_all(
                "/^.* (.*) (MEDIUMTEXT|LONGTEXT) DEFAULT NULL COMMENT 'default={(.*)}'/",
                $query,
                $matches
            )) {
                // prepare data
                $field = $matches[1][0];
                $type = $matches[2][0];
                $value = $matches[3][0];

                // push
                $fields[$field] = $value;

                // remove from query
                $query =
                    str_replace("{$field} {$type} DEFAULT NULL COMMENT 'default={{$value}}'", "", $query);
            }

            // prepare table name
            if (!empty($fields) && preg_match_all("/^ALTER TABLE `(.*)` .*$/", $query, $matches)) {
                $tableName = explode("`", $matches[1][0])[0];
            }

            if (!empty($tableName) && !empty($fields)) {
                foreach ($fields as $field => $value) {
                    $queries[$key]
                        .= ";UPDATE {$tableName} SET {$field}='{$this->parseDefaultValue($value)}'
                         WHERE {$field} IS NULL";
                }
            }
        }
        $event->setArgument('queries', $queries);
    }

    /**
     * Parse default value
     *
     * @param string $value
     *
     * @return string
     */
    protected function parseDefaultValue(string $value): string
    {
        if (!empty($value) && preg_match("/({break})+/", $value)) {
            $value = str_replace("{break}", "\n", $value);
        }

        return $value;
    }
}

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

namespace Espo\Core\Formula\Functions\DatetimeGroup;

use \Espo\Core\Exceptions\Error;

abstract class AddIntervalType extends \Espo\Core\Formula\Functions\Base
{
    protected function init()
    {
        $this->addDependency('dateTime');
    }

    protected $timeOnly = false;

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

        $dateTimeString = $this->evaluate($item->value[0]);

        if (!$dateTimeString) {
            return null;
        }

        if (!is_string($dateTimeString)) {
            throw new Error();
        }

        $interval = $this->evaluate($item->value[1]);

        if (!is_numeric($interval)) {
            throw new Error();
        }

        $isTime = false;
        if (strlen($dateTimeString) > 10) {
            $isTime = true;
        }

        if ($this->timeOnly && !$isTime) {
            $dateTimeString .= ' 00:00:00';
            $isTime = true;
        }

        try {
            $dateTime = new \DateTime($dateTimeString);
        } catch (\Exception $e) {
            return null;
        }

        $dateTime->modify(($interval > 0 ? '+' : '') . strval($interval) . ' ' . $this->intervalTypeString);

        if ($isTime) {
            return $dateTime->format($this->getInjection('dateTime')->getInternalDateTimeFormat());
        } else {
            return $dateTime->format($this->getInjection('dateTime')->getInternalDateFormat());
        }
    }
}
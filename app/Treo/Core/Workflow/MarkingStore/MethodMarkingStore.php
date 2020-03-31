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

namespace Treo\Core\Workflow\MarkingStore;

use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;

/**
 * Class MethodMarkingStore
 *
 * @author r.ratsun <r.ratsun@treolabs.com>
 */
final class MethodMarkingStore implements MarkingStoreInterface
{
    /**
     * @var bool
     */
    private $singleState;

    /**
     * @var string
     */
    private $property;

    /**
     * MethodMarkingStore constructor.
     *
     * @param bool   $singleState
     * @param string $property
     */
    public function __construct(bool $singleState = false, string $property = 'marking')
    {
        $this->singleState = $singleState;
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function getMarking($subject)
    {
        $marking = $subject->get($this->property);

        if (!$marking) {
            return new Marking();
        }

        if ($this->singleState) {
            $marking = [$marking => 1];
        }

        return new Marking($marking);
    }

    /**
     * {@inheritdoc}
     */
    public function setMarking($subject, Marking $marking, array $context = [])
    {
        $marking = $marking->getPlaces();

        if ($this->singleState) {
            $marking = key($marking);
        }

        $subject->set($this->property, $marking, $context);
    }
}

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

namespace Treo\Core\Utils;

use Espo\Core\Utils\Route as Base;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\File\Manager as FileManager;
use Treo\Core\ModuleManager\Manager as ModuleManager;

/**
 * Class Route
 *
 * @author r.ratsun r.ratsun@treolabs.com
 */
class Route extends Base
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @inheritdoc
     */
    public function __construct(
        Config $config,
        Metadata $metadata,
        FileManager $fileManager,
        ModuleManager $moduleManager
    ) {
        // call parent
        parent::__construct($config, $metadata, $fileManager);

        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritdoc
     */
    protected function unify()
    {
        // for custom
        $data = $this->getAddData([], $this->paths['customPath']);

        // for module
        foreach ($this->getModuleManager()->getModules() as $module) {
            $module->loadRoutes($data);
        }

        // for treo core
        $data = $this->getAddData($data, CORE_PATH . '/Treo/Resources/routes.json');

        // for core
        $data = $this->getAddData($data, CORE_PATH . '/Espo/Resources/routes.json');

        return $data;
    }

    /**
     * @return ModuleManager
     */
    protected function getModuleManager(): ModuleManager
    {
        return $this->moduleManager;
    }
}

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

namespace Treo\Core\Migration;

use PDO;
use Treo\Core\Utils\Config;

/**
 * Base class
 *
 * @author r.ratsun <r.ratsun@treolabs.com>
 */
class Base
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param PDO    $pdo
     * @param Config $config
     */
    public function __construct(PDO $pdo, Config $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    /**
     * Up to current
     */
    public function up(): void
    {
    }

    /**
     * Down to previous version
     */
    public function down(): void
    {
    }

    /**
     * @param string $message
     * @param bool   $break
     */
    protected function renderLine(string $message, bool $break = true)
    {
        \Treo\Composer\PostUpdate::renderLine($message, $break);
    }

    /**
     * Get config
     *
     * @return Config
     */
    protected function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Get PDO
     *
     * @return PDO
     */
    protected function getPDO(): PDO
    {
        return $this->pdo;
    }

    /**
     * @param string $version
     */
    protected function updateCoreVersion(string $version): void
    {
        $data = json_decode(file_get_contents('composer.json'), true);
        $data['require']['treolabs/treocore'] = $version;
        file_put_contents('composer.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        copy('composer.json', 'data/stable-composer.json');
    }
}

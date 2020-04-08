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

namespace Treo\Migrations;

use Treo\Core\Migration\Base;

/**
 * Migration class for version 3.25.15
 *
 * @author r.ratsun <r.ratsun@treolabs.com>
 */
class V3Dot25Dot15 extends Base
{
    /**
     * @inheritdoc
     */
    public function up(): void
    {
        echo ' Update scheduled jobs ... ';
        $this->getPDO()->exec("DELETE FROM scheduled_job WHERE job='Cleanup'");
        $this->getPDO()->exec("DELETE FROM scheduled_job WHERE job='TreoCleanup'");
        $this->getPDO()->exec("DELETE FROM scheduled_job WHERE job='RestApiDocs'");
        $this->getPDO()->exec("DELETE FROM job WHERE name='Cleanup'");
        $this->getPDO()->exec("DELETE FROM job WHERE name='TreoCleanup'");
        $this->getPDO()->exec("DELETE FROM job WHERE name='RestApiDocs'");
        $this->getPDO()->exec(
            "INSERT INTO scheduled_job (id, name, job, status, scheduling) VALUES ('TreoCleanup','Unused data cleanup. Deleting old data and unused db tables, db columns, etc.','TreoCleanup','Active','0 0 1 * *')"
        );
        $this->getPDO()->exec(
            "INSERT INTO scheduled_job (id, name, job, status, scheduling) VALUES ('RestApiDocs','Generate REST API docs','RestApiDocs','Active','0 0 * * *')"
        );
        echo ' Done!' . PHP_EOL;

        if (file_exists('.htaccess')) {
            echo ' Update .htaccess file ... ';
            file_put_contents('.htaccess', str_replace('RewriteRule ^ index.php [QSA,L]', 'RewriteRule ^(.*)$ index.php?treoq=$1 [L,QSA]', file_get_contents('.htaccess')));
            echo ' Done!' . PHP_EOL;
        }
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        if (file_exists('.htaccess')) {
            echo ' Update .htaccess file ... ';
            file_put_contents('.htaccess', str_replace('RewriteRule ^(.*)$ index.php?treoq=$1 [L,QSA]', 'RewriteRule ^ index.php [QSA,L]', file_get_contents('.htaccess')));
            echo ' Done!' . PHP_EOL;
        }
    }
}

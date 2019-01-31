<?php
/**
 * This file is part of EspoCRM and/or TreoPIM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2018 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoPIM is EspoCRM-based Open Source Product Information Management application.
 * Copyright (C) 2017-2018 TreoLabs GmbH
 * Website: http://www.treopim.com
 *
 * TreoPIM as well as EspoCRM is free software: you can redistribute it and/or modify
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

declare(strict_types=1);

namespace Treo\Console;

/**
 * ComposerLog console
 *
 * @author r.ratsun@treolabs.com
 */
class ComposerLog extends AbstractConsole
{
    /**
     * Get console command description
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Send composer log to stream.';
    }

    /**
     * Run action
     *
     * @param array $data
     */
    public function run(array $data): void
    {
        // save
        $this->stream();

        self::show('Composer log successfully saved', self::SUCCESS);
    }

    /**
     * Save
     */
    protected function stream(): void
    {
        if (file_exists('data/composer.log')) {
            // get content
            $content = str_replace("{{finished}}", "", file_get_contents('data/composer.log'));

            // prepare createdById
            $createdById = 'system';
            if (!empty($this->getConfig()->get('composerUser'))) {
                $createdById = $this->getConfig()->get('composerUser');
            }

            // prepare status
            $status = 1;
            if (strpos($content, 'postUpdate') !== false) {
                $status = 0;
            }

            // get em
            $em = $this->getContainer()->get('entityManager');

            // prepare note
            $note = $em->getEntity('Note');
            $note->set('type', 'composerUpdate');
            $note->set('parentType', 'ModuleManager');
            $note->set('data', ['status' => $status, 'output' => $content]);
            $note->set('createdById', $createdById);

            // save
            $em->saveEntity($note, ['skipCreatedBy' => true]);

            // unset user
            $this->getConfig()->set('composerUser', null);
            $this->getConfig()->save();
        }
    }
}

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

namespace Treo\Services;

use Espo\Core\Templates\Services\Base;
use Treo\Core\Utils\Util;

/**
 * Class TreoStore
 *
 * @author r.ratsun@treolabs.com
 */
class TreoStore extends Base
{
    const PACKAGES = 'https://packagist.treopim.com/packages.json';

    /**
     * @inheritDoc
     */
    public function findEntities($params)
    {
        // update store cache
        $this->updateStoreCache();

        return parent::findEntities($params);
    }

    /**
     * Update store cache if it needs
     */
    protected function updateStoreCache(): void
    {
        // prepare cache path
        $path = 'data/cache/store-last-update-time.json';

        // prepare last update
        $lastUpdate = strtotime('2019-01-01 00:00:00');
        if (file_exists($path)) {
            $lastUpdate = json_decode(file_get_contents($path), true)['time'];
        }

        // get diff in minutes
        $minutes = (time() - $lastUpdate) / 60;

        if ($minutes > 120 && !empty($packages = $this->getRemotePackages())) {
            // caching
            $this->caching($packages);

            // create dir if it needs
            if (!file_exists('data/cache')) {
                mkdir('data/cache', 0777, true);
            }

            // save cache file
            file_put_contents($path, json_encode(['time' => time()]));
        }
    }

    /**
     * @param array $data
     */
    protected function caching(array $data): void
    {
        // delete all
        $sth = $this
            ->getEntityManager()
            ->getPDO()
            ->prepare("DELETE FROM treo_store");
        $sth->execute();

        foreach ($data as $package) {
            $entity = $this->getEntityManager()->getEntity("TreoStore");
            $entity->id = $package['treoId'];
            $entity->set('packageId', $package['packageId']);
            $entity->set('url', $package['url']);
            $entity->set('status', $package['status']);
            $entity->set('versions', $package['versions']);
            foreach ($package['name'] as $locale => $value) {
                if ($locale == 'default') {
                    $entity->set('name', $value);
                } else {
                    $entity->set('name' . Util::toCamelCase(strtolower($locale), "_", true), $value);
                }
            }
            foreach ($package['description'] as $locale => $value) {
                if ($locale == 'default') {
                    $entity->set('description', $value);
                } else {
                    $entity->set('description' . Util::toCamelCase(strtolower($locale), "_", true), $value);
                }
            }
            if (!empty($package['tags']) && is_array($package['tags'])) {
                $entity->set('tags', $package['tags']);
            }

            $this->getEntityManager()->saveEntity($entity, ['skipAll' => true]);
        }
    }

    /**
     * @return array
     */
    protected function getRemotePackages(): array
    {
        // get all
        $all = self::getPathContent(self::PACKAGES);

        // get public
        $public = self::getPathContent(self::PACKAGES . '?id=public');

        // get private
        $private = [];
        if (!empty($treoId = $this->getConfig()->get('treoId'))) {
            $private = self::getPathContent(self::PACKAGES . '?id=' . $treoId);
        }

        // parse all
        $packages = $this->parsePackages($all);

        // parse public
        if (!empty($public)) {
            foreach ($this->parsePackages($public, 'available') as $id => $row) {
                $packages[$id] = $row;
            }
        }

        // parse private
        if (!empty($private)) {
            foreach ($this->parsePackages($private, 'available') as $id => $row) {
                $packages[$id] = $row;
            }
        }

        return array_values($packages);
    }

    /**
     * @param string $path
     *
     * @return array
     */
    private static function getPathContent(string $path): array
    {
        $content = @file_get_contents($path);

        return (empty($content)) ? [] : json_decode($content, true);
    }

    /**
     * @param array  $packages
     * @param string $status
     *
     * @return array
     */
    private function parsePackages(array $packages, string $status = 'buyable'): array
    {
        /** @var array $result */
        $result = [];

        /** @var array $data */
        $data = [];

        foreach ($packages['packages'] as $repository => $versions) {
            if (is_array($versions)) {
                foreach ($versions as $version => $row) {
                    if (!empty($row['extra']['treoId'])) {
                        $treoId = $row['extra']['treoId'];
                        $version = strtolower($version);
                        if (preg_match_all('/^v\d+.\d+.\d+$/', $version, $matches)
                            || preg_match_all('/^v\d+.\d+.\d+-rc\d+$/', $version, $matches)
                            || preg_match_all('/^\d+.\d+.\d+$/', $version, $matches)
                            || preg_match_all('/^\d+.\d+.\d+-rc\d+$/', $version, $matches)
                        ) {
                            // prepare version
                            $version = str_replace('v', '', $matches[0][0]);

                            // skip if unstable version
                            if (strpos($version, 'rc') !== false) {
                                continue;
                            }

                            // push
                            $data[$treoId][$version] = $row;
                        }
                    }
                }
            }
        }

        foreach ($data as $treoId => $rows) {
            // find max version
            $versions = array_keys($rows);
            natsort($versions);
            $versions = array_reverse($versions);
            $max = $versions[0];

            // prepare tags
            $tags = [];
            if (!empty($rows[$max]['extra']['tags'])) {
                $tags = $rows[$max]['extra']['tags'];
            }

            // prepare item
            $item = [
                'treoId'      => $treoId,
                'packageId'   => $rows[$max]['name'],
                'url'         => $rows[$max]['source']['url'],
                'name'        => $rows[$max]['extra']['name'],
                'description' => $rows[$max]['extra']['description'],
                'tags'        => $tags,
                'status'      => $status
            ];

            foreach ($versions as $version) {
                $item['versions'][] = [
                    'version' => $version,
                    'require' => $rows[$version]['require'],
                ];
            }

            // push
            $result[$treoId] = $item;
        }

        return $result;
    }
}

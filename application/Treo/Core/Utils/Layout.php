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

namespace Treo\Core\Utils;

use Espo\Core\Utils\Json;
use Treo\Core\Utils\Util;
use Treo\Layouts\AbstractLayout;
use Treo\Core\Portal\Container as PortalContainer;

/**
 * Class of Layout
 *
 * @author r.ratsun <r.ratsun@zinitsolutions.com>
 */
class Layout extends \Espo\Core\Utils\Layout
{
    use \Treo\Traits\ContainerTrait;

    /**
     * Get Layout context
     *
     * @param string $scope
     * @param string $name
     *
     * @return json
     */
    public function get($scope, $name)
    {
        // prepare scope
        $scope = $this->sanitizeInput($scope);

        // prepare name
        $name = $this->sanitizeInput($name);

        // cache
        if (isset($this->changedData[$scope][$name])) {
            return Json::encode($this->changedData[$scope][$name]);
        }

        // compose
        $layout = $this->compose($scope, $name);

        // remove fields from layout if this fields not exist in metadata
        $layout = $this->disableNotExistingFields($scope, $name, $layout);

        // modify layouts
        $layout = $this->modifyLayouts($scope, $name, $layout);

        return Json::encode($layout);
    }

    protected function compose(string $scope, string $name): array
    {
        // prepare data
        $data = [];

        // from custom data
        $fileFullPath = $this->concatPath($this->getLayoutPath($scope, true), $name . '.json');
        if (file_exists($fileFullPath)) {
            $fileData = $this->getFileManager()->getContents($fileFullPath);

            // prepare data
            $data = array_merge_recursive($data, Json::decode($fileData, true));
        }

        // from modules data
        foreach ($this->getMetadata()->getModules() as $module) {
            $module->loadLayouts($scope, $name, $data);
        }

        // from treo core data
        if (empty($data)) {
            // prepare file path
            $filePath = $this->concatPath('application/Treo/Resources/layouts', $scope);
            $fileFullPath = $this->concatPath($filePath, $name . '.json');
            if (file_exists($fileFullPath)) {
                // get file data
                $fileData = $this->getFileManager()->getContents($fileFullPath);

                // prepare data
                $data = Json::decode($fileData, true);
            }
        }

        // from core data
        if (empty($data)) {
            // prepare file path
            $filePath = $this->concatPath($this->paths['corePath'], $scope);
            $fileFullPath = $this->concatPath($filePath, $name . '.json');
            if (file_exists($fileFullPath)) {
                // get file data
                $fileData = $this->getFileManager()->getContents($fileFullPath);

                // prepare data
                $data = Json::decode($fileData, true);
            }
        }

        // default
        if (empty($data)) {
            // prepare file path
            $fileFullPath = $this->concatPath(
                $this->concatPath($this->params['defaultsPath'], 'layouts'),
                $name . '.json'
            );

            if (file_exists($fileFullPath)) {
                // get file data
                $fileData = $this->getFileManager()->getContents($fileFullPath);

                // prepare data
                $data = Json::decode($fileData, true);
            }
        }

        return $data;
    }

    /**
     * Disable fields from layout if this fields not exist in metadata
     *
     * @param string $scope
     * @param string $name
     * @param array  $data
     *
     * @return array
     */
    protected function disableNotExistingFields($scope, $name, $data): array
    {
        // get entityDefs
        $entityDefs = $this->getMetadata()->get('entityDefs')[$scope] ?? [];

        // check if entityDefs exists
        if (!empty($entityDefs)) {
            // get fields for entity
            $fields = array_keys($entityDefs['fields']);

            // remove fields from layout if this fields not exist in metadata
            switch ($name) {
                case 'filters':
                case 'massUpdate':
                    $data = array_values(array_intersect($data, $fields));

                    break;
                case 'detail':
                case 'detailSmall':
                    foreach ($data[0]['rows'] as $key => $row) {
                        foreach ($row as $fieldKey => $fieldData) {
                            if (isset($fieldData['name']) && !in_array($fieldData['name'], $fields)) {
                                $data[0]['rows'][$key][$fieldKey] = false;
                            }
                        }
                    }

                    break;
                case 'list':
                case 'listSmall':
                    foreach ($data as $key => $row) {
                        if (isset($row['name']) && !in_array($row['name'], $fields)) {
                            array_splice($data, $key, 1);
                        }
                    }

                    break;
            }
        }

        return $data;
    }

    /**
     * @param string $scope
     * @param string $name
     * @param array  $data
     *
     * @return array
     */
    protected function modifyLayouts(string $scope, string $name, array $data): array
    {
        // prepare classes
        $classes = [
            "Treo\\Layouts\\$scope"
        ];
        foreach ($this->getMetadata()->getModules() as $id => $module) {
            $classes[] = "\\$id\\Layouts\\$scope";
        }

        // modify data
        foreach ($classes as $className) {
            if (class_exists($className)) {
                // create class
                $layout = new $className();

                // set container
                if ($layout instanceof AbstractLayout) {
                    $layout->setContainer($this->getContainer());
                }

                // call method
                $method = 'layout' . ucfirst($name);
                if (method_exists($layout, $method)) {
                    $data = $layout->{$method}($data);
                }
            }
        }

        return $data;
    }

    /**
     * @return bool
     */
    protected function isPortal(): bool
    {
        return (get_class($this->getContainer()) == PortalContainer::class);
    }

    /**
     * Get a full path of the file
     *
     * @param string | array $folderPath - Folder path, Ex. myfolder
     * @param string         $filePath   - File path, Ex. file.json
     *
     * @return string
     */
    public function concatPath($folderPath, $filePath = null)
    {
        // for portal
        if ($this->isPortal()) {
            $portalPath = Util::concatPath($folderPath, 'portal/' . $filePath);
            if (file_exists($portalPath)) {
                return $portalPath;
            }
        }

        return Util::concatPath($folderPath, $filePath);
    }
}

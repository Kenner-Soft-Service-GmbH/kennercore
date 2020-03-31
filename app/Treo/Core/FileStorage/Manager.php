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

namespace Treo\Core\FileStorage;

use Espo\Core\Exceptions\Error;
use Treo\Core\Container;
use Treo\Entities\Attachment;

/**
 * Class Manager
 * @package Treo\Core\FileStorage
 */
class Manager
{
    /**
     * @var array
     */
    private $implementations = [];

    /**
     * @var array
     */
    private $implementationClassNameMap = [];

    /**
     * @var Container
     */
    private $container;

    /**
     * Manager constructor.
     * @param array $implementationClassNameMap
     * @param $container
     */
    public function __construct(array $implementationClassNameMap, $container)
    {
        $this->implementationClassNameMap = $implementationClassNameMap;
        $this->container = $container;
    }

    /**
     * @param null $storage
     * @return mixed
     * @throws Error
     */
    protected function getImplementation($storage = null)
    {
        if (!$storage) {
            $storage = 'UploadDir';
        }

        if (array_key_exists($storage, $this->implementations)) {
            return $this->implementations[$storage];
        }

        if (!array_key_exists($storage, $this->implementationClassNameMap)) {
            throw new Error("FileStorageManager: Unknown storage '{$storage}'");
        }
        $className = $this->implementationClassNameMap[$storage];

        $implementation = new $className();
        foreach ($implementation->getDependencyList() as $dependencyName) {
            $implementation->inject($dependencyName, $this->container->get($dependencyName));
        }
        $this->implementations[$storage] = $implementation;

        return $implementation;
    }

    /**
     * @param Attachment $attachment
     * @return mixed
     * @throws Error
     */
    public function isFile(Attachment $attachment)
    {
        $implementation = $this->getImplementation($attachment->get('storage'));
        return $implementation->isFile($attachment);
    }

    /**
     * @param Attachment $attachment
     * @return mixed
     * @throws Error
     */
    public function getContents(Attachment $attachment)
    {
        $implementation = $this->getImplementation($attachment->get('storage'));
        return $implementation->getContents($attachment);
    }

    /**
     * @param Attachment $attachment
     * @param $contents
     * @return mixed
     * @throws Error
     */
    public function putContents(Attachment $attachment, $contents)
    {
        $implementation = $this->getImplementation($attachment->get('storage'));
        return $implementation->putContents($attachment, $contents);
    }

    /**
     * @param Attachment $attachment
     * @return mixed
     * @throws Error
     */
    public function unlink(Attachment $attachment)
    {
        $implementation = $this->getImplementation($attachment->get('storage'));
        return $implementation->unlink($attachment);
    }

    /**
     * @param Attachment $attachment
     * @return mixed
     * @throws Error
     */
    public function getLocalFilePath(Attachment $attachment)
    {
        $implementation = $this->getImplementation($attachment->get('storage'));
        return $implementation->getLocalFilePath($attachment);
    }

    /**
     * @param Attachment $attachment
     * @return mixed
     * @throws Error
     */
    public function hasDownloadUrl(Attachment $attachment)
    {
        $implementation = $this->getImplementation($attachment->get('storage'));
        return $implementation->hasDownloadUrl($attachment);
    }

    /**
     * @param Attachment $attachment
     * @return mixed
     * @throws Error
     */
    public function getDownloadUrl(Attachment $attachment)
    {
        $implementation = $this->getImplementation($attachment->get('storage'));
        return $implementation->getDownloadUrl($attachment);
    }
}

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

namespace Treo\Core\FileStorage\Storages;

use \Espo\Core\Interfaces\Injectable;
use Treo\Entities\Attachment;

/**
 * Class Base
 * @package Treo\Core\FileStorage\Storages
 */
abstract class Base implements Injectable
{
    /**
     * @var array
     */
    protected $dependencyList = [];

    /**
     * @var array
     */
    protected $injections = array();

    /**
     * @param $name
     * @param $object
     */
    public function inject($name, $object)
    {
        $this->injections[$name] = $object;
    }

    /**
     * Base constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Init method
     */
    protected function init()
    {
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function getInjection($name)
    {
        return $this->injections[$name];
    }

    /**
     * @param $name
     */
    protected function addDependency($name)
    {
        $this->dependencyList[] = $name;
    }

    /**
     * @param array $list
     */
    protected function addDependencyList(array $list)
    {
        foreach ($list as $item) {
            $this->addDependency($item);
        }
    }

    /**
     * @return array
     */
    public function getDependencyList()
    {
        return $this->dependencyList;
    }

    /**
     * @param Attachment $attachment
     * @return mixed
     */
    abstract public function hasDownloadUrl(Attachment $attachment);

    /**
     * @param Attachment $attachment
     * @return mixed
     */
    abstract public function getDownloadUrl(Attachment $attachment);

    /**
     * @param Attachment $attachment
     * @return mixed
     */
    abstract public function unlink(Attachment $attachment);

    /**
     * @param Attachment $attachment
     * @return mixed
     */
    abstract public function getContents(Attachment $attachment);

    /**
     * @param Attachment $attachment
     * @return mixed
     */
    abstract public function isFile(Attachment $attachment);

    /**
     * @param Attachment $attachment
     * @param $contents
     * @return mixed
     */
    abstract public function putContents(Attachment $attachment, $contents);

    /**
     * @param Attachment $attachment
     * @return mixed
     */
    abstract public function getLocalFilePath(Attachment $attachment);
}

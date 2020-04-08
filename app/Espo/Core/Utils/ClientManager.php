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

namespace Espo\Core\Utils;

class ClientManager
{
    private $themeManager;

    private $config;

    protected $mainHtmlFilePath = 'client/html/main.html';

    protected $htmlFilePathForDeveloperMode = 'client/html/main.html';

    protected $runScript = "app.start();";

    protected $basePath = '';

    public function __construct(Config $config, ThemeManager $themeManager)
    {
        $this->config = $config;
        $this->themeManager = $themeManager;
    }

    protected function getThemeManager()
    {
        return $this->themeManager;
    }

    protected function getConfig()
    {
        return $this->config;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    protected function getCacheTimestamp()
    {
        if (!$this->getConfig()->get('useCache')) {
            return (string) time();
        }
        return $this->getConfig()->get('cacheTimestamp', 0);
    }

    public function display($runScript = null, $htmlFilePath = null, $vars = array())
    {
        if (is_null($runScript)) {
            $runScript = $this->runScript;
        }
        if (is_null($htmlFilePath)) {
            $htmlFilePath = $this->mainHtmlFilePath;
        }

        $isDeveloperMode = $this->getConfig()->get('isDeveloperMode');

        if ($isDeveloperMode) {
            if (file_exists('client/html/' . $htmlFilePath)) {
                $htmlFilePath = 'client/html/' . $htmlFilePath;
            }
        }

        $html = file_get_contents($htmlFilePath);
        foreach ($vars as $key => $value) {
            $html = str_replace('{{'.$key.'}}', $value, $html);
        }
        $html = str_replace('{{applicationName}}', $this->getConfig()->get('applicationName', 'EspoCRM'), $html);
        $html = str_replace('{{cacheTimestamp}}', $this->getCacheTimestamp(), $html);
        $html = str_replace('{{useCache}}', $this->getConfig()->get('useCache') ? 'true' : 'false', $html);
        $html = str_replace('{{stylesheet}}', $this->getThemeManager()->getStylesheet(), $html);
        $html = str_replace('{{runScript}}', $runScript , $html);
        $html = str_replace('{{basePath}}', $this->basePath , $html);
        if ($isDeveloperMode) {
            $html = str_replace('{{useCacheInDeveloperMode}}', $this->getConfig()->get('useCacheInDeveloperMode') ? 'true' : 'false', $html);
        }

        echo $html;
    }
}

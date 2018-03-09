<?php
/**
 * This file is part of EspoCRM and/or TreoPIM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2018 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoPIM is EspoCRM-based Open Source Product Information Management application.
 * Copyright (C) 2017-2018 Zinit Solutions GmbH
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

declare(strict_types = 1);

namespace Espo\Modules\TreoCrm\Services;

use Espo\Core\Services\Base;
use Espo\Core\Utils\Util;
use Espo\Core\Utils\Json;
use Espo\Modules\TreoCrm\Core\Utils\Metadata;
use Espo\Modules\TreoCrm\Documentator\Extractor;

/**
 * RestApiDocs service
 *
 * @author r.ratsun <r.ratsun@zinitsolutions.com>
 */
class RestApiDocs extends Base
{
    /**
     * @var array
     */
    protected $dependencies = [
        'config',
        'entityManager',
        'user',
        'metadata'
    ];

    /**
     * @var array
     */
    protected $documentatorConfig = null;

    /**
     * @var array
     */
    protected $httpCode = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        406 => 'Not Acceptable',
        415 => 'Unsupported Media Type',
        500 => 'Internal Server Error'
    ];

    /**
     * Generate documentation
     *
     * @param array $data
     *
     * @return bool
     */
    public function generateDocumentation(array $data = []): bool
    {
        // prepare result
        $result = false;

        // get html
        $html = $this->getHtml();

        if (!empty($html)) {
            // prepare file path
            $filePath = 'apidocs/index.html';

            // delete file
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // create file
            $file = fopen($filePath, 'w');
            fwrite($file, $html);
            fclose($file);

            $result = true;
        }

        return $result;
    }

    /**
     * Render HTML
     *
     * @return string
     */
    protected function getHtml(): string
    {
        // prepare content
        $content = [
            '{{ title }}'   => 'TreoCRM REST API documentation',
            '{{ date }}'    => date('d.m.Y'),
            '{{ content }}' => $this->getContent()
        ];

        return strtr($this->getTemplateContent('index'), $content);
    }

    /**
     * Get controllers
     *
     * @return array
     */
    protected function getControllers(): array
    {
        // prepare result
        $result = [];

        foreach ($this->getMetadata()->get('scopes') as $scope => $row) {
            $className = $this->getControllerClassName($scope);
            if (class_exists($className)) {
                $result[] = $className;
            }
        }

        return $result;
    }

    /**
     * Get controller class name
     *
     * @param string $controller
     *
     * @return string
     */
    protected function getControllerClassName(string $controller): string
    {
        $customClassName = '\\Espo\\Custom\\Controllers\\'.Util::normilizeClassName($controller);
        if (class_exists($customClassName)) {
            $controllerClassName = $customClassName;
        } else {
            $moduleName = $this->getMetadata()->getScopeModuleName($controller);
            if ($moduleName) {
                $controllerClassName = '\\Espo\\Modules\\'.$moduleName.'\\Controllers\\'.
                    Util::normilizeClassName($controller);
            } else {
                $controllerClassName = '\\Espo\\Controllers\\'.Util::normilizeClassName($controller);
            }
        }

        return $controllerClassName;
    }

    /**
     * Get content
     *
     * @return string
     */
    protected function getContent(): string
    {
        $result = '';

        foreach ($this->getContentSections() as $key => $value) {
            array_unshift($value, '<h2>'.$key.'</h2>');
            $result .= implode(PHP_EOL, $value);
        }

        return $result;
    }

    /**
     * Get content sections
     *
     * @return array
     */
    protected function getContentSections(): array
    {
        // prepare data
        $result  = [];
        $counter = 0;
        $section = null;
        $config  = $this->getDocumentatorConfig();

        foreach ($this->extractAnnotations() as $class => $methods) {
            // get section name
            $section = $this->prepareSectionName($class);

            foreach ($methods as $name => $docs) {
                // prepare docs
                if (empty($docs) && isset($config['method'][$name])) {
                    $docs = $this->prepareDynamicDocBlockData($config['method'][$name], $section);
                }
                if (!empty($docs)) {
                    // prepare docs
                    $docs = $docs + $config['common'];

                    // prepare content data
                    $data = [
                        '{{ elt_id }}'                => $counter,
                        '{{ method }}'                => $this->generateBadgeForMethod($docs),
                        '{{ route }}'                 => $docs['ApiRoute'][0]['name'],
                        '{{ description }}'           => $docs['ApiDescription'][0]['description'],
                        '{{ headers }}'               => $this->generateHeadersTemplate($docs),
                        '{{ parameters }}'            => $this->generateParamsTemplate($docs, $section),
                        '{{ body }}'                  => $this->generateBodyTemplate($counter, $docs, $section),
                        '{{ sample_response_codes }}' => $this->getResponseCodes($docs, $counter),
                        '{{ sample_response_body }}'  => $this->getResponseBody($docs, $counter, $section)
                    ];

                    // push section
                    $result[$section][] = strtr($this->getTemplateContent('Parts/content'), $data);

                    // prepare counter
                    $counter++;
                }
            }
        }

        return $result;
    }

    /**
     * Extract annotations
     *
     * @return array
     */
    protected function extractAnnotations(): array
    {
        $result = [];

        foreach ($this->getControllers() as $class) {
            $result = Extractor::getAllClassAnnotations($class);
        }

        return (!empty($result)) ? $result : [];
    }

    /**
     * Get response codes
     *
     * @param array $docs
     * @param int $counter
     *
     * @return string
     */
    protected function getResponseCodes(array $docs, int $counter): string
    {
        // prepare result
        $result = '';
        $config = $this->getDocumentatorConfig();
        $codes  = [];

        if (!empty($docs['ApiResponseCode'][0]['sample'])) {
            $codes = Json::decode($docs['ApiResponseCode'][0]['sample'], true);
        } elseif (isset($docs['ApiMethod'][0]['type'])) {
            $codes = $config['responseCode'][strtolower($docs['ApiMethod'][0]['type'])];
        }

        if (!empty($codes)) {
            $data = [];
            foreach ($codes as $code) {
                $tr = array(
                    '{{ elt_id }}'      => $counter,
                    '{{ response }}'    => $this->prepareResponseCode($code),
                    '{{ description }}' => ''
                );

                $data[] = strtr($this->getTemplateContent('Parts/sample-reponse-code'), $tr);
            }

            $result = implode(PHP_EOL, $data);
        }

        return $result;
    }

    /**
     * Prepare response code
     *
     * @param int $code
     *
     * @return string
     */
    protected function prepareResponseCode(int $code): string
    {
        $result = '';

        if (isset($this->httpCode[$code])) {
            $result = $code.' '.$this->httpCode[$code];
        }

        return $result;
    }

    /**
     * Get response body
     *
     * @param array $docs
     * @param int $counter
     * @param string $entity
     *
     * @return string
     */
    protected function getResponseBody(array $docs, int $counter, string $entity): string
    {
        // prepare result
        $result = '';

        if (!empty($docs['ApiReturn'])) {
            $data = [];
            foreach ($docs['ApiReturn'] as $row) {
                if (isset($row['sample'])) {
                    // prepare route
                    $route = $docs['ApiRoute'][0]['name'];

                    $tr = [
                        '{{ elt_id }}'   => $counter,
                        '{{ response }}' => $this->getEntityFields($row['sample'], $entity, $route)
                    ];

                    // push data
                    $data[] = strtr($this->getTemplateContent('Parts/sample-reponse'), $tr);
                }
            }

            $result = implode(PHP_EOL, $data);
        }

        return $result;
    }

    /**
     * Generates the template for headers
     *
     * @param  array        $st_params
     *
     * @return string
     */
    protected function generateHeadersTemplate($st_params): string
    {
        // prepare result
        $result = '';

        if (!empty($st_params['ApiHeaders'])) {
            // prepare content
            $body = [];
            foreach ($st_params['ApiHeaders'] as $params) {
                $tr     = [
                    '{{ key }}'   => $params['key'],
                    '{{ value }}' => $params['value']
                ];
                $body[] = strtr($this->getTemplateContent('Parts/headers-row'), $tr);
            }
            $content = ['{{ tbody }}' => implode(PHP_EOL, $body)];

            $result = strtr($this->getTemplateContent('Parts/headers-table'), $content);
        }

        return $result;
    }

    /**
     * Generates the template for parameters
     *
     * @param  array  $docs
     * @param  string $entity
     *
     * @return string
     */
    protected function generateParamsTemplate(array $docs, string $entity): string
    {
        // prepare result
        $result = '';

        // prepare docs
        $docs = $this->prepareApiParams($docs, $entity);

        if (!empty($docs['ApiParams'])) {
            $body = [];
            foreach ($docs['ApiParams'] as $params) {
                $tr = [
                    '{{ name }}'        => $params['name'],
                    '{{ type }}'        => $params['type'],
                    '{{ description }}' => @$params['description'],
                    '{{ is_required }}' => @$params['is_required'] == '1' ? 'Yes' : 'No',
                ];
                if (in_array($params['type'], ['object', 'array(object) ', 'array']) && isset($params['sample'])) {
                    // get template
                    $template = $this->getTemplateContent('Parts/param-sample-btn');

                    $tr['{{ type }}'] .= ' '.strtr($template, ['{{ sample }}' => $params['sample']]);
                }
                $body[] = strtr($this->getTemplateContent('Parts/param-content'), $tr);
            }

            $result = strtr($this->getTemplateContent('Parts/param-table'), ['{{ tbody }}' => implode(PHP_EOL, $body)]);
        }

        return $result;
    }

    /**
     * Generate POST body template
     *
     * @param  int      $id
     * @param  array    $docs
     * @param  string    $entity
     *
     * @return string
     */
    protected function generateBodyTemplate($id, $docs, $entity): string
    {
        // prepare result
        $result = '';

        if (!empty($docs['ApiBody'])) {
            // prepare route
            $route = $docs['ApiRoute'][0]['name'];

            $content = [
                '{{ elt_id }}' => $id,
                '{{ body }}'   => $this->getEntityFields($docs['ApiBody'][0]['sample'], $entity, $route, true)
            ];
            $result  = strtr($this->getTemplateContent('Parts/sample-post-body'), $content);
        }

        return $result;
    }

    /**
     * Generates a badge for method
     *
     * @param  array  $data
     * @return string
     */
    protected function generateBadgeForMethod($data)
    {
        $method    = strtoupper($data['ApiMethod'][0]['type']);
        $st_labels = array(
            'POST'    => 'label-primary',
            'GET'     => 'label-success',
            'PUT'     => 'label-warning',
            'PATCH'   => 'label-warning',
            'DELETE'  => 'label-danger',
            'OPTIONS' => 'label-info'
        );

        return '<span class="label '.$st_labels[$method].'">'.$method.'</span>';
    }

    /**
     * Get template content
     *
     * @return string
     */
    protected function getTemplateContent(string $template): string
    {
        // prepare file
        $file = 'application/Espo/Modules/TreoCrm/Documentator/Views/Templates/'.$template.'.html';

        return (file_exists($file)) ? file_get_contents($file) : '';
    }

    /**
     * Get documentator config
     *
     * @return array
     */
    protected function getDocumentatorConfig(): array
    {
        if (is_null($this->documentatorConfig)) {
            $this->documentatorConfig = include 'application/Espo/Modules/TreoCrm/Configs/RestApiDocumentator.php';
        }

        return (array) $this->documentatorConfig;
    }

    /**
     * Prepare section name
     *
     * @param string $class
     *
     * @return string
     */
    protected function prepareSectionName(string $class): string
    {
        // get parts
        $parts = explode('\\', $class);

        return end($parts);
    }

    /**
     * Prepare dynamic DocBlock data
     *
     * @param array $data
     * @param string $class
     *
     * @return array
     */
    protected function prepareDynamicDocBlockData(array $data, string $class): array
    {
        // prepare description
        if (isset($data['ApiDescription'])) {
            foreach ($data['ApiDescription'] as $k => $row) {
                if (isset($row['description'])) {
                    $data['ApiDescription'][$k]['description'] = sprintf($row['description'], $class);
                }
            }
        }

        // prepare route
        if (isset($data['ApiRoute'])) {
            foreach ($data['ApiRoute'] as $k => $row) {
                if (isset($row['name'])) {
                    $data['ApiRoute'][$k]['name'] = sprintf($row['name'], $class);
                }
            }
        }

        return $data;
    }

    /**
     * Get response entity data
     *
     * @param string $entity
     *
     * @return array
     */
    protected function getResponseEntityData(string $entity): array
    {
        // prepare result
        $result = [];

        $data = $this->getEntityData($entity);
        if (!empty($data)) {
            $result = [
                'id'         => 'string',
                'deleted'    => 'bool',
                'teamsIds'   => ['string', 'string', '...'],
                'teamsNames' => ['teamId - string' => 'teamName - string']
            ];
            foreach ($data as $name => $row) {
                $result[$name] = $row['type'];
            }
        }

        return $result;
    }

    /**
     * Get entity data
     *
     * @param string $entity
     *
     * @return array
     */
    protected function getEntityData(string $entity): array
    {
        // prepare result
        $result = [];

        // get entity defs
        $defs = $this->getMetadata()->get('entityDefs.'.$entity);

        if (isset($defs['fields'])) {
            // get config
            $inputLanguageList = $this->getConfig()->get('inputLanguageList');

            foreach ($defs['fields'] as $name => $row) {
                if (isset($row['type'])) {
                    switch ($row['type']) {
                        case 'link':
                            $result[$name.'Id']   = [
                                'type'     => 'string',
                                'required' => !empty($row['required'])
                            ];
                            $result[$name.'Name'] = [
                                'type'     => 'string',
                                'required' => !empty($row['required'])
                            ];
                            break;
                        case 'linkMultiple':
                            break;

                        case 'varcharMultiLang':
                            $result[$name] = [
                                'type'     => 'string',
                                'required' => !empty($row['required'])
                            ];
                            if (!empty($inputLanguageList)) {
                                foreach ($inputLanguageList as $locale) {
                                    // prepare locale
                                    $locale = ucfirst(Util::toCamelCase(strtolower($locale)));

                                    $result[$name.$locale] = [
                                        'type'     => 'string',
                                        'required' => !empty($row['required'])
                                    ];
                                }
                            }
                            break;
                        case 'textMultiLang':
                            $result[$name] = [
                                'type'     => 'string',
                                'required' => !empty($row['required'])
                            ];
                            if (!empty($inputLanguageList)) {
                                foreach ($inputLanguageList as $locale) {
                                    // prepare locale
                                    $locale = ucfirst(Util::toCamelCase(strtolower($locale)));

                                    $result[$name.$locale] = [
                                        'type'     => 'string',
                                        'required' => !empty($row['required'])
                                    ];
                                }
                            }
                            break;
                        case 'bool':
                            $result[$name] = [
                                'type'     => 'bool',
                                'required' => !empty($row['required'])
                            ];
                            break;
                        case 'int':
                            $result[$name] = [
                                'type'     => 'integer',
                                'required' => !empty($row['required'])
                            ];
                            break;
                        case 'float':
                            $result[$name] = [
                                'type'     => 'float',
                                'required' => !empty($row['required'])
                            ];
                            break;
                        default:
                            $result[$name] = [
                                'type'     => 'string',
                                'required' => !empty($row['required'])
                            ];
                            break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Prepare API params
     *
     * @param array $docs
     * @param string $entity
     *
     * @return array
     */
    protected function prepareApiParams(array $docs, string $entity): array
    {
        if (!empty($docs['ApiEntityParams'])) {
            foreach ($this->getEntityData($entity) as $name => $row) {
                $docs['ApiParams'][] = [
                    'name'        => $name,
                    'type'        => $row['type'],
                    'description' => '',
                    'is_required' => $row['required'],
                ];
            }
        }

        return $docs;
    }

    /**
     * Get entity fields
     *
     * @param string $sample
     * @param string $entity
     * @param string $route
     * @param bool $isBody
     *
     * @return string
     */
    protected function getEntityFields($sample, string $entity, string $route, bool $isBody = false)
    {
        // prepare sample
        if (is_string($sample) && strpos($sample, '{entityDeff}') !== false) {
            // get entity defs
            $entityDeffs = $this->getResponseEntityData($entity);

            if (!$isGet) {
                unset($entityDeffs['id']);
            }

            // if action getDuplicateAttributes replace parameter key
            if (preg_match('/.+\/action\/getDuplicateAttributes$/', $route)) {
                if (!empty($entityDeffs['id'])) {
                    $entityDeffs['_duplicatingEntityId'] = $entityDeffs['id'];
                    unset($entityDeffs['id']);
                }
            }

            // for request body
            if ($isBody) {
                unset($entityDeffs['id']);
                unset($entityDeffs['deleted']);
            }

            $sample = str_replace(['{entityDeff}', "'"], [Json::encode($entityDeffs), '"'], $sample);
        }

        return $sample;
    }

    /**
     * Get metadata
     *
     * @return Metadata
     */
    protected function getMetadata(): Metadata
    {
        return $this->getInjection('metadata');
    }
}

<?php
/**
 * This file is part of EspoCRM and/or TreoPIM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2018 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoPIM is EspoCRM-based Open Source Product Information Management application.
 * Copyright (C) 2017-2019 TreoLabs GmbH
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

namespace Treo\Documentator;

/**
 * Class of Extractor
 *
 * @author r.ratsun <r.ratsun@zinitsolutions.com>
 */
class Extractor
{
    /**
     * Static array to store already parsed annotations
     *
     * @var array
     */
    private static $annotationCache;

    /**
     * Indicates that annotations should has strict behavior, 'false' by default
     *
     * @var boolean
     */
    private $strict = false;

    /**
     * Stores the default namespace for Objects instance, usually used on methods like getMethodAnnotationsObjects()
     *
     * @var string
     */
    public $defaultNamespace = '';

    /**
     * Sets strict variable to true/false
     *
     * @param bool $value boolean value to indicate that annotations to has strict behavior
     */
    public function setStrict($value)
    {
        $this->strict = (bool)$value;
    }

    /**
     * Sets default namespace to use in object instantiation
     *
     * @param string $namespace default namespace
     */
    public function setDefaultNamespace($namespace)
    {
        $this->defaultNamespace = $namespace;
    }

    /**
     * Gets default namespace used in object instantiation
     *
     * @return string $namespace default namespace
     */
    public function getDefaultAnnotationNamespace()
    {
        return $this->defaultNamespace;
    }

    /**
     * Gets all anotations with pattern @SomeAnnotation() from a given class
     *
     * @param  string $className class name to get annotations
     *
     * @return array  self::$annotationCache all annotated elements
     */
    public static function getClassAnnotations($className)
    {
        if (!isset(self::$annotationCache[$className])) {
            $class = new \ReflectionClass($className);
            self::$annotationCache[$className] = self::parseAnnotations($class->getDocComment());
        }

        return self::$annotationCache[$className];
    }

    /**
     * @param string $className
     *
     * @return array
     */
    public static function getAllClassAnnotations($className)
    {
        $class = new \ReflectionClass($className);

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $object) {
            self::$annotationCache['annotations'][$className][$object->name] = self::getMethodAnnotations(
                $className,
                $object->name
            );
        }

        return self::$annotationCache['annotations'];
    }

    /**
     * Gets all anotations with pattern @SomeAnnotation() from a determinated method of a given class
     *
     * @param  string $className  class name
     * @param  string $methodName method name to get annotations
     *
     * @return array  self::$annotationCache all annotated elements of a method given
     */
    public static function getMethodAnnotations($className, $methodName)
    {
        if (!isset(self::$annotationCache[$className . '::' . $methodName])) {
            try {
                $method = new \ReflectionMethod($className, $methodName);
                $class = new \ReflectionClass($className);
                $annotations = self::consolidateAnnotations($method->getDocComment(), $class->getDocComment());
            } catch (\ReflectionException $e) {
                $annotations = [];
            }

            self::$annotationCache[$className . '::' . $methodName] = $annotations;
        }

        return self::$annotationCache[$className . '::' . $methodName];
    }

    /**
     * Gets all anotations with pattern @SomeAnnotation() from a determinated method of a given class
     * and instance its abcAnnotation class
     *
     * @param  string $className  class name
     * @param  string $methodName method name to get annotations
     *
     * @return array  self::$annotationCache all annotated objects of a method given
     */
    public function getMethodAnnotationsObjects($className, $methodName)
    {
        $annotations = $this->getMethodAnnotations($className, $methodName);
        $objects = [];

        $i = 0;

        foreach ($annotations as $annotationClass => $listParams) {
            $annotationClass = ucfirst($annotationClass);
            $class = $this->defaultNamespace . $annotationClass . 'Annotation';

            // verify is the annotation class exists, depending if Annotations::strict is true
            // if not, just skip the annotation instance creation.
            if (!class_exists($class)) {
                if ($this->strict) {
                    throw new \Exception(sprintf('Runtime Error: Annotation Class Not Found: %s', $class));
                } else {
                    // silent skip & continue
                    continue;
                }
            }

            if (empty($objects[$annotationClass])) {
                $objects[$annotationClass] = new $class();
            }

            foreach ($listParams as $params) {
                if (is_array($params)) {
                    foreach ($params as $key => $value) {
                        $objects[$annotationClass]->set($key, $value);
                    }
                } else {
                    $objects[$annotationClass]->set($i++, $params);
                }
            }
        }

        return $objects;
    }

    /**
     * @param type $docblockMethod
     * @param type $dockblockClass
     *
     * @return array
     */
    private static function consolidateAnnotations($docblockMethod, $dockblockClass)
    {
        $methodAnnotations = self::parseAnnotations($docblockMethod);
        $classAnnotations = self::parseAnnotations($dockblockClass);

        if (count($methodAnnotations) === 0) {
            return [];
        }

        foreach ($classAnnotations as $name => $valueClass) {
            if (count($valueClass) !== 1) {
                continue;
            }

            if ($name === 'ApiRoute') {
                if (isset($methodAnnotations[$name])) {
                    foreach ($methodAnnotations[$name] as $key => $valueMethod) {
                        $methodAnnotations[$name][$key]['name'] = $valueClass[0]['name'] . $valueMethod['name'];
                    }
                }
            }

            if ($name === 'ApiSector') {
                $methodAnnotations[$name] = $valueClass;
            }
        }

        return $methodAnnotations;
    }

    /**
     * Parse annotations
     *
     * @param  string $docblock
     *
     * @return array  parsed annotations params
     */
    private static function parseAnnotations($docblock)
    {
        $annotations = [];

        if (!empty($docblock)) {
            // Strip away the docblock header and footer to ease parsing of one line annotations
            $docblock = substr($docblock, 3, -2);

            if (preg_match_all('/@(?<name>[A-Za-z_-]+)[\s\t]*\((?<args>(?:(?!"\)).)*")\)\r?/s', $docblock, $matches)) {
                $numMatches = count($matches[0]);

                for ($i = 0; $i < $numMatches; ++$i) {
                    // annotations has arguments
                    if (isset($matches['args'][$i])) {
                        $argsParts = trim($matches['args'][$i]);
                        $name = $matches['name'][$i];
                        $value = self::parseArgs($argsParts);
                    } else {
                        $value = [];
                    }

                    $annotations[$name][] = $value;
                }
            }
        }


        return $annotations;
    }

    /**
     * Parse individual annotation arguments
     *
     * @param  string $content arguments string
     *
     * @return array  annotated arguments
     */
    private static function parseArgs($content)
    {
        // Replace initial stars
        $content = preg_replace('/^\s*\*/m', '', $content);

        $data = [];
        $len = strlen($content);
        $i = 0;
        $var = '';
        $val = '';
        $level = 1;

        $prevDelimiter = '';
        $nextDelimiter = '';
        $nextToken = '';
        $composing = false;
        $type = 'plain';
        $delimiter = null;
        $quoted = false;
        $tokens = array('"', '"', '{', '}', ',', '=');

        while ($i <= $len) {
            $c = substr($content, $i++, 1);

            //if ($c === '\'' || $c === '"') {
            if ($c === '"') {
                $delimiter = $c;
                //open delimiter
                if (!$composing && empty($prevDelimiter) && empty($nextDelimiter)) {
                    $prevDelimiter = $nextDelimiter = $delimiter;
                    $val = '';
                    $composing = true;
                    $quoted = true;
                } else {
                    // close delimiter
                    if ($c !== $nextDelimiter) {
                        throw new \Exception(
                            sprintf(
                                "Parse Error: enclosing error -> expected: [%s], given: [%s]",
                                $nextDelimiter,
                                $c
                            )
                        );
                    }

                    // validating sintax
                    if ($i < $len) {
                        if (',' !== substr($content, $i, 1)) {
                            throw new \Exception(
                                sprintf(
                                    "Parse Error: missing comma separator near: ...%s<--",
                                    substr($content, ($i - 10), $i)
                                )
                            );
                        }
                    }

                    $prevDelimiter = $nextDelimiter = '';
                    $composing = false;
                    $delimiter = null;
                }
            } elseif (!$composing && in_array($c, $tokens)) {
                switch ($c) {
                    case '=':
                        $prevDelimiter = $nextDelimiter = '';
                        $level = 2;
                        $composing = false;
                        $type = 'assoc';
                        $quoted = false;
                        break;
                    case ',':
                        $level = 3;

                        // If composing flag is true yet,
                        // it means that the string was not enclosed, so it is parsing error.
                        if ($composing === true && !empty($prevDelimiter) && !empty($nextDelimiter)) {
                            throw new \Exception(
                                sprintf(
                                    "Parse Error: enclosing error -> expected: [%s], given: [%s]",
                                    $nextDelimiter,
                                    $c
                                )
                            );
                        }

                        $prevDelimiter = $nextDelimiter = '';
                        break;
                    case '{':
                        $subc = '';
                        $subComposing = true;

                        while ($i <= $len) {
                            $c = substr($content, $i++, 1);

                            if (isset($delimiter) && $c === $delimiter) {
                                throw new \Exception(
                                    sprintf(
                                        "Parse Error: Composite variable is not enclosed correctly."
                                    )
                                );
                            }

                            if ($c === '}') {
                                $subComposing = false;
                                break;
                            }
                            $subc .= $c;
                        }

                        // if the string is composing yet means that the structure of var. never was enclosed with '}'
                        if ($subComposing) {
                            throw new \Exception(
                                sprintf(
                                    "Parse Error: Composite variable is not enclosed correctly. near: ...%s'",
                                    $subc
                                )
                            );
                        }

                        $val = self::parseArgs($subc);
                        break;
                }
            } else {
                if ($level == 1) {
                    $var .= $c;
                } elseif ($level == 2) {
                    $val .= $c;
                }
            }

            if ($level === 3 || $i === $len) {
                if ($type == 'plain' && $i === $len) {
                    $data = self::castValue($var);
                } else {
                    $data[trim($var)] = self::castValue($val, !$quoted);
                }

                $level = 1;
                $var = $val = '';
                $composing = false;
                $quoted = false;
            }
        }

        return $data;
    }

    /**
     * Try determinate the original type variable of a string
     *
     * @param  string  $val  string containing possibles variables that can be cast to bool or int
     * @param  boolean $trim indicate if the value passed should be trimmed after to try cast
     *
     * @return mixed   returns the value converted to original type if was possible
     */
    private static function castValue($val, $trim = false)
    {
        if (is_array($val)) {
            foreach ($val as $key => $value) {
                $val[$key] = self::castValue($value);
            }
        } elseif (is_string($val)) {
            if ($trim) {
                $val = trim($val);
            }

            $tmp = strtolower($val);

            if ($tmp === 'false' || $tmp === 'true') {
                $val = $tmp === 'true';
            } elseif (is_numeric($val)) {
                return $val + 0;
            }

            unset($tmp);
        }

        return $val;
    }
}

<?php
/**
 *  This file is part of the Aplorm package.
 *
 *  (c) Nicolas Moral <n.moral@live.fr>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Aplorm\Interpreter;

use Aplorm\Common\Annotations\NativeAnnotations;
use Aplorm\Common\Interpreter\TypeInterface;
use Aplorm\Common\Lexer\LexedPartInterface;
use Aplorm\Interpreter\Exception\ClassNotFoundException;
use Aplorm\Interpreter\Exception\ClassPartNotFoundException;
use Aplorm\Interpreter\Exception\ConstantNotFoundException;
use Aplorm\Interpreter\Exception\InvalidAnnotationConfigurationException;

/**
 * Interprete data from Lexer and transform :
 * - annotation in object
 * - parameter and attribute value into real value (for number, boolean, null value or constant).
 */
class Interpreter
{
    /**
     * Analysed part to interpret.
     *
     * @var array<mixed>
     */
    protected static array $parts = [];

    protected static ?string $currentClassName = null;
    protected static ?string $fullyQualifiedClassName = null;
    protected static ?string $classNamespace = null;

    /**
     * transforme string constant into real value.
     */
    protected const STRING_CONST_VALUE = [
        'false' => false,
        'true' => true,
        'null' => null,
    ];

    /**
     * transforme analysed part into interpreted.
     *
     * @param array<mixed> $parts analysed by respecting LexedPartInterface
     *
     * @return array<mixed> parts interpreted
     */
    public static function &interprete(array &$parts): array
    {
        self::$parts = &$parts;
        self::handleClass();
        self::handleVariables();
        self::handleFunctions();

        unset(
            self::$parts[LexedPartInterface::NAMESPACE_PART],
            self::$parts[LexedPartInterface::CLASS_ALIASES_PART],
            self::$parts[LexedPartInterface::USE_PART]
        );

        return self::$parts;
    }

    /**
     * Analyse class and transform annotations data into class.
     */
    protected static function handleClass(): void
    {
        $part = &self::getPart(LexedPartInterface::CLASS_NAME_PART);
        self::$currentClassName = $part['className'];
        self::$fullyQualifiedClassName = $part['fullyQualifiedClassName'];
        self::$classNamespace = $part['namespace'];

        if (isset($part['annotations']) && !empty($part['annotations'])) {
            self::handleAnnotations($part['annotations']);
        }
    }

    /**
     * Analyse variables.
     */
    protected static function handleVariables(): void
    {
        $parts = &self::getPart(LexedPartInterface::VARIABLE_PART);

        foreach ($parts as $key => &$part) {
            self::handleVariable($part);
        }
    }

    /**
     * Analyse functions.
     */
    protected static function handleFunctions(): void
    {
        $parts = &self::getPart(LexedPartInterface::FUNCTION_PART);

        foreach ($parts as $key => &$part) {
            self::handleFunction($part);
        }
    }

    /**
     * Analyse a function and transform parameter and function.
     *
     * @param array<mixed> $function
     */
    protected static function handleFunction(array &$function): void
    {
        if (isset($function['annotations'])) {
            self::handleAnnotations($function['annotations']);
        }
        foreach ($function['parameters'] as $key => &$part) {
            self::handleVariable($part);
        }
    }

    /**
     * Analyse a class attribute or function parameter and transform parameter default value by real value.
     *
     * @param array<mixed> $variable
     */
    protected static function handleVariable(array &$variable): void
    {
        if (isset($variable['isValueAConstant']) && $variable['isValueAConstant']) {
            if (false !== strstr($variable['value'], '::')) {
                $parts = explode('::', $variable['value']);
                $fullyQualifiedName = self::findClass($parts[0]);
                $variable['value'] = self::getClassConstantValue($fullyQualifiedName, $parts[1]);

                return;
            }

            self::handleGlobalConstant($variable['value']);

            return;
        }

        self::transformToNumber($variable);
    }

    /**
     * handle annotations.
     *
     * @param array<mixed> $annotations
     */
    protected static function handleAnnotations(array &$annotations): void
    {
        $interpretedAnnotations = [];
        foreach ($annotations as $key => &$annotation) {
            if (!\in_array($annotation['name'], NativeAnnotations::TYPE_ANNOTATIONS, true)) {
                self::handleAnnotation($annotation);
                $key = \get_class($annotation);
            }
            $interpretedAnnotations[$key] = $annotation;
        }

        $annotations = $interpretedAnnotations;
        unset($interpretedAnnotations);
    }

    /**
     * transform annotation parameter in object.
     *
     * @param array<mixed> $annotation
     */
    protected static function handleAnnotation(array &$annotation): void
    {
        $namedParameter = false;
        $anonymousParameter = false;
        $annotationParameter = [];
        foreach ($annotation['params'] as &$parameter) {
            self::handleParameter($parameter);
            if (isset($parameter['name'])) {
                $namedParameter = true;
                $annotationParameter[$parameter['name']] = $parameter['value'];
            } else {
                $anonymousParameter = true;
                $annotationParameter[] = $parameter['value'];
            }
        }

        if ($namedParameter && $anonymousParameter) {
            throw new InvalidAnnotationConfigurationException('You can\'t use named and anonymousParameter');
        }

        $className = self::findClass($annotation['name']);

        if ($namedParameter) {
            $annotation = new $className($annotationParameter);
        } elseif ($annotationParameter) {
            $annotation = new $className(...$annotationParameter);
        } else {
            $annotation = new $className();
        }
        unset($className, $annotationParameter);
    }

    /**
     * handle parameter and transform to the real value;.
     *
     * @param array<mixed> $parameter
     */
    protected static function handleParameter(array &$parameter): void
    {
        switch ($parameter['type']) {
            case TypeInterface::INT_CONSTANT_TYPE:
            case TypeInterface::FLOAT_CONSTANT_TYPE:
                self::transformToNumber($parameter);

                break;
            case TypeInterface::CLASS_CONSTANT_TYPE:
                self::handleConstant($parameter);

                break;
            case TypeInterface::ARRAY_TYPE:
            case TypeInterface::OBJECT_TYPE:
                if (isset($parameter['value'])) {
                    foreach ($parameter['value'] as &$value) {
                        if (isset($value['value'])) {
                            self::handleParameter($value);
                            $value = $value['value'];
                        }
                    }
                }

                break;
            case TypeInterface::ANNOTATION_TYPE:
                self::handleAnnotation($parameter['value']);

                break;
            case TypeInterface::OTHER_CONSTANT_TYPE:
                self::handleGlobalConstant($parameter['value']);

                break;
            case TypeInterface::STRING_TYPE:
            default:
                break;
        }
    }

    /**
     * get constant value.
     */
    protected static function handleGlobalConstant(string &$value): void
    {
        if (isset(self::STRING_CONST_VALUE[strtolower($value)])) {
            $value = self::STRING_CONST_VALUE[strtolower($value)];

            return;
        }

        $value = self::getConstantValue($value);
    }

    /**
     * handle class constant.
     *
     * @param array<mixed> $parameter
     */
    protected static function handleConstant(array &$parameter): void
    {
        [
            $alias,
            $constant
        ] = explode('::', $parameter['value']);

        $fullyQualifiedName = self::findClass($alias);

        $parameter['value'] = self::getClassConstantValue($fullyQualifiedName, $constant);
    }

    /**
     * find class with a name.
     *
     * @return string the class fully qualified name
     */
    protected static function findClass(string $alias): string
    {
        if ('self' === $alias || 'static' === $alias || $alias === self::$currentClassName) {
            return self::$fullyQualifiedClassName;
        }

        $aliases = self::getPart(LexedPartInterface::CLASS_ALIASES_PART);

        if (isset($aliases[$alias])) {
            $fullyQualifiedName = $aliases[$alias];
        } elseif (class_exists($alias)) {
            $fullyQualifiedName = $alias;
        } elseif (class_exists(self::$classNamespace.'\\'.$alias)) {
            $fullyQualifiedName = self::$classNamespace.'\\'.$alias;
        } else {
            throw new ClassNotFoundException($alias, self::$fullyQualifiedClassName);
        }

        return $fullyQualifiedName;
    }

    /**
     * transform a string number in a real number.
     *
     * @param array<mixed> $variable
     */
    protected static function transformToNumber(array &$variable): void
    {
        $variable['value'] = str_replace('_', '', $variable['value']);

        if (('int' === $variable['type'] || TypeInterface::INT_CONSTANT_TYPE === $variable['type'])
            && is_numeric($variable['value'])) {
            $variable['value'] = (int) ($variable['value']);

            return;
        }
        if (('float' === $variable['type'] || TypeInterface::FLOAT_CONSTANT_TYPE === $variable['type'])
            && is_numeric($variable['value'])) {
            $variable['value'] = (float) ($variable['value']);

            return;
        }
    }

    /**
     * handle class constant.
     *
     * @return mixed|null
     */
    protected static function getClassConstantValue(string $fullyQualifiedName, string $constant)
    {
        return self::getConstantValue($fullyQualifiedName.'::'.$constant);
    }

    /**
     * get constant value.
     *
     * @throws ConstantNotFoundException if constant does not exist
     *
     * @return mixed|null
     */
    protected static function getConstantValue(string $constant)
    {
        if (!\defined($constant)) {
            throw new ConstantNotFoundException($constant);
        }

        return \constant($constant);
    }

    /**
     * return a part of analysed data.
     *
     * @return array<mixed>
     */
    protected static function &getPart(string $partName): ?array
    {
        if (!isset(self::$parts[$partName])) {
            throw new ClassPartNotFoundException($partName);
        }

        return self::$parts[$partName];
    }
}

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

namespace Aplorm\Interpreter\Tests\Interpreter;

use Aplorm\Common\Lexer\LexedPartInterface;
use Aplorm\Common\Test\AbstractTest;
use Aplorm\Interpreter\Exception\ClassNotFoundException;
use Aplorm\Interpreter\Exception\ClassPartNotFoundException;
use Aplorm\Interpreter\Exception\ConstantNotFoundException;
use Aplorm\Interpreter\Exception\InvalidAnnotationConfigurationException;
use Aplorm\Interpreter\Exception\WrongAnnotationTypeException;
use Aplorm\Interpreter\Interpreter;
use Aplorm\Lexer\Lexer\Lexer;

class FailTest extends AbstractTest
{
    /**
     * function call in setUp function.
     */
    protected function doSetup(): void
    {
    }

    /**
     * function call in tearDown function.
     */
    protected function doTearDown(): void
    {
    }

    public static function setupBeforeClass(): void
    {
    }

    public static function tearDownAfterClass(): void
    {
    }

    /**
     * @dataProvider classFileProvider
     *
     * @param string                   $fileName
     * @param class-string<\Throwable> $exception
     */
    public function testInvalidClass($fileName, $exception): void
    {
        $this->expectException($exception);

        $parts = Lexer::analyse($fileName);
        Interpreter::interprete($parts);
    }

    /**
     * @dataProvider classPartNotFoundProvider
     *
     * @param string                   $fileName
     * @param class-string<\Throwable> $exception
     * @param string                   $part
     */
    public function testClassPartNotFound($fileName, $exception, $part): void
    {
        $this->expectException($exception);

        $parts = Lexer::analyse($fileName);
        unset($parts[$part]);
        Interpreter::interprete($parts);
    }

    /**
     * @return array<mixed>
     */
    public function classFileProvider(): iterable
    {
        if (isset($_SERVER['TRAVIS_BUILD_DIR'])) {
            $dir = $_SERVER['TRAVIS_BUILD_DIR'].'/'.$_ENV['SAMPLE_CLASSES'];
        } else {
            $dir = $_ENV['PWD'].'/'.$_ENV['SAMPLE_CLASSES'];
        }

        yield [
            $dir.'/ErrorInterpreterClassTest.php',
            InvalidAnnotationConfigurationException::class,
        ];

        yield [
            $dir.'/ClassNotFound.php',
            ClassNotFoundException::class,
        ];

        yield [
            $dir.'/ConstantNotFound.php',
            ConstantNotFoundException::class,
        ];

        yield [
            $dir.'/ErrorAnnotationClass.php',
            WrongAnnotationTypeException::class,
        ];
    }

    /**
     * @return array<mixed>
     */
    public function classPartNotFoundProvider(): iterable
    {
        if (isset($_SERVER['TRAVIS_BUILD_DIR'])) {
            $dir = $_SERVER['TRAVIS_BUILD_DIR'].'/'.$_ENV['SAMPLE_CLASSES'];
        } else {
            $dir = $_ENV['PWD'].'/'.$_ENV['SAMPLE_CLASSES'];
        }

        yield [
            $dir.'/InterpreterClassTest.php',
            ClassPartNotFoundException::class,
            LexedPartInterface::METHOD_PART,
        ];

        yield [
            $dir.'/InterpreterClassTest.php',
            ClassPartNotFoundException::class,
            LexedPartInterface::CLASS_NAME_PART,
        ];

        yield [
            $dir.'/InterpreterClassTest.php',
            ClassPartNotFoundException::class,
            LexedPartInterface::CLASS_ALIASES_PART,
        ];
    }
}

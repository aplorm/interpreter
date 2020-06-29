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
use Aplorm\Common\Memory\ObjectJar;
use Aplorm\Common\Test\AbstractTest;
use Aplorm\Interpreter\Interpreter;
use Aplorm\Interpreter\Tests\Sample\SampleClass;
use Aplorm\Interpreter\Tests\Sample\TestAnnotations\Annotation;
use Aplorm\Interpreter\Tests\Sample\TestAnnotations\Annotation7;
use Aplorm\Lexer\Lexer\Lexer;

class SuccessTest extends AbstractTest
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
        ObjectJar::clean();
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
     * @param string $fileName
     */
    public function testInterpreter($fileName): void
    {
        $parts = Lexer::analyse($fileName);
        Interpreter::interprete($parts);
        self::assertTrue(true);
    }

    /**
     * @dataProvider classFileProvider
     *
     * @param string $fileName
     */
    public function testClassAnnotation($fileName): void
    {
        $parts = Lexer::analyse($fileName);
        Interpreter::interprete($parts);

        self::assertArrayHasKey(Annotation::class, $parts[LexedPartInterface::CLASS_NAME_PART]['annotations']);

        self::assertTrue($parts[LexedPartInterface::CLASS_NAME_PART]['annotations'][Annotation::class]->get() instanceof Annotation);
        self::assertIsArray($parts[LexedPartInterface::CLASS_NAME_PART]['annotations'][Annotation7::class]);
        self::assertCount(2, $parts[LexedPartInterface::CLASS_NAME_PART]['annotations'][Annotation7::class]);
        self::assertEquals(SampleClass::A_CONSTANT, $parts[LexedPartInterface::CLASS_NAME_PART]['annotations'][Annotation7::class][1]->get()->data);

        foreach ($parts[LexedPartInterface::VARIABLE_PART] as $variable) {
            self::assertIsArray($variable['annotations']);
        }
    }

    /**
     * @dataProvider classFileParameterProvider
     *
     * @param string $fileName
     * @param string $parameter
     * @param string $type
     * @param string $value
     */
    public function testParameter($fileName, $parameter, $type, $value): void
    {
        $parts = Lexer::analyse($fileName);
        Interpreter::interprete($parts);

        self::assertArrayHasKey($parameter, $parts[LexedPartInterface::VARIABLE_PART]);
        $part = $parts[LexedPartInterface::VARIABLE_PART][$parameter];
        self::assertEquals($value, $part['value']);
        self::assertEquals($type, $part['type']);
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
            $dir.'/InterpreterClassTest.php',
        ];
    }

    /**
     * @return array<mixed>
     */
    public function classFileParameterProvider(): iterable
    {
        if (isset($_SERVER['TRAVIS_BUILD_DIR'])) {
            $dir = $_SERVER['TRAVIS_BUILD_DIR'].'/'.$_ENV['SAMPLE_CLASSES'];
        } else {
            $dir = $_ENV['PWD'].'/'.$_ENV['SAMPLE_CLASSES'];
        }

        yield [
            $dir.'/InterpreterClassTest.php',
            'float',
            'float',
            1.5,
        ];

        yield [
            $dir.'/InterpreterClassTest.php',
            'int',
            'int',
            1,
        ];

        yield [
            $dir.'/InterpreterClassTest.php',
            'longInt',
            'int',
            1000000,
        ];

        yield [
            $dir.'/InterpreterClassTest.php',
            'boolean',
            'bool',
            true,
        ];

        yield [
            $dir.'/InterpreterClassTest.php',
            'nullable',
            'bool',
            null,
        ];
    }
}

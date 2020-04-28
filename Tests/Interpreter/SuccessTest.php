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

use Aplorm\Common\Test\AbstractTest;
use Aplorm\Interpreter\Interpreter;
use Aplorm\Interpreter\Tests\Sample\InterpreterClassTest;
use Aplorm\Lexer\Lexer\Lexer;
use ReflectionClass;

class SuccessTest extends AbstractTest
{
    private static ?array $parts = [];

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
        $reflectionClass = new ReflectionClass(InterpreterClassTest::class);
        /** @var string */
        $fileName = $reflectionClass->getFileName();

        self::$parts = Lexer::analyse($fileName);
    }

    public static function tearDownAfterClass(): void
    {
        self::$parts = null;
    }

    public function testLexer(): void
    {
        Interpreter::interprete(self::$parts);
        self::assertTrue(true);
    }
}

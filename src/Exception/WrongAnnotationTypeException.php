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

namespace Aplorm\Interpreter\Exception;

use Exception;

class WrongAnnotationTypeException extends Exception
{
    private const CODE = 0X495;

    public function __construct(string $annotation, string $interface)
    {
        parent::__construct('Annotation :\''.$annotation.'\' must be a '.$interface, self::CODE);
    }
}

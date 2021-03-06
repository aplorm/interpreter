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

class ClassNotFoundException extends Exception
{
    private const CODE = 0X491;

    public function __construct(string $alias, string $inClass)
    {
        parent::__construct('Alias :\''.$alias.'\' not found in '.$inClass, self::CODE);
    }
}

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

class ConstantNotFoundException extends Exception
{
    private const CODE = 0X493;

    public function __construct(string $constant)
    {
        parent::__construct($constant.' not found', self::CODE);
    }
}

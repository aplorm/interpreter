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

namespace Aplorm\Interpreter\Tests\Sample;

use Aplorm\Interpreter\Tests\Sample\TestAnnotations\Annotation2;

/**
 * class comment.
 * @Annotation2(self::BLA)
 */
class ConstantNotFound
{
}

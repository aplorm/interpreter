<?php

namespace Aplorm\Interpreter\Tests\Sample\TestAnnotations;

use Aplorm\Common\DataConfigurator\AnnotationInterface;

class Annotation6 implements AnnotationInterface
{
    public $data;

    public function __construct(bool $data)
    {
        $this->data = $data;
    }
}

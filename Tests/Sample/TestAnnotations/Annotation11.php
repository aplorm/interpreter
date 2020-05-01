<?php

namespace Aplorm\Interpreter\Tests\Sample\TestAnnotations;

use Aplorm\Common\DataConfigurator\AnnotationInterface;

class Annotation11 implements AnnotationInterface
{
    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}

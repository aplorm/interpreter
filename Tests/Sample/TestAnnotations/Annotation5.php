<?php

namespace Aplorm\Interpreter\Tests\Sample\TestAnnotations;

use Aplorm\Common\DataConfigurator\AnnotationInterface;

class Annotation5 implements AnnotationInterface
{
    public $data;

    public function __construct(int $data)
    {
        $this->data = $data;
    }
}

<?php

namespace Aplorm\Interpreter\Tests\Sample\TestAnnotations;

use Aplorm\Common\DataConfigurator\AnnotationInterface;

class Annotation7 implements AnnotationInterface
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}

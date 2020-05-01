<?php

namespace Aplorm\Interpreter\Tests\Sample\TestAnnotations;

use Aplorm\Common\DataConfigurator\AnnotationInterface;

class Annotation8 implements AnnotationInterface
{
    public $a;
    public $b;
    public $c;
    public $d;

    public function __construct(int $a, int $b, int $c, int $d)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
        $this->d = $d;
    }
}


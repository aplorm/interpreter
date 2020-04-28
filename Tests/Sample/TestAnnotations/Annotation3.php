<?php

namespace Aplorm\Interpreter\Tests\Sample\TestAnnotations;

class Annotation3
{
    public $data;

    public function __construct(Annotation11 $data)
    {
        $this->data = $data;
    }
}

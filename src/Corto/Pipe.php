<?php
namespace Rodriguezric\Corto;

/**
 * Invokable object for mutating an initial value
 * over a chain of functions.
 *
 * Usage:
 *
 * (new Pipe(10))
 *   (fn($x) => $x * 2)
 *   ->value;
 *
 * //returns 20
 **/

class Pipe
{
    public function __construct(public mixed $value) {}

    public function __invoke(callable $callable): Pipe
    {
        $this->value = $callable($this->value);

        return $this;
    }
}

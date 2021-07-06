<?php

namespace Rodriguezric\Corto;

class Request
{
    public string $method; 
    public string $uri; 
    public array $input;

    public static Request $request;
    
    /**
     * Creates and object storing the HTTP method, URI
     * and JSON decoded input.
     **/
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->input = json_decode(
            file_get_contents("php://input")
            , true) ?? [];
    }

    /**
     * Creates a memoized instance of Request.
     **/
    public static function instance(): static
    {
        self::$request ??= new Request();

        return self::$request;
    }

    /**
     * Checks if request input key exists.
     **/
    public function has(array|string $keys): bool
    {
        is_array($keys) ?: $keys = [$keys];
        return !array_diff($keys, array_keys($this->input));
    }

    /**
     * Check if request input key is missing.
     **/
    public function is_missing(array|string $keys): bool
    {
        return !$this->has($keys);
    }

    /**
     * Return input filtered to keys in $keys
     *
     * Example:
     *
     * ['name' => 'Name', 'age' => 38]
     * ->only(['name']);
     *
     * //returns ['name' => 'Name']
     **/
    public function only(array|string $keys) {
        is_array($keys) ?: $keys = [$keys];
        
        return array_intersect_key(
            $this->input,
            array_flip($keys)
        );
    }
}

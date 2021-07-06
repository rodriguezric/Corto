<?php

namespace Rodriguezric\Corto;

/**
 * Returns the Request singleton.
 **/
function request(): Request
{
    return Request::instance();
}

function response(int $http_code = 200): callable
{
    return function (array|JsonSerializable $data) use ($http_code) {
        http_response_code($http_code);
        header('Content-Type: application/json');
        echo json_encode($data, true);

        exit();
    };
}

/**
 * Replaces a path string to a regex string. It accounts
 * for variables wrapped in curly braces and replacing 
 * them with regex groups.
 *
 * Example:
 * /path/to/route/{id}  ->  #/path/to/route/(.+)#
 */
function path_to_regex(string $path): string
{
    $path = preg_replace(['#{.+}#U'], ['(.+)'], $path);

    return "#{$path}#";
}

/**
 * Returns path arguments designated by {}.
 **/
function path_args(string $path): array
{
    $regex = path_to_regex($path);

    $matches = [];
    preg_match($regex, request()->uri, $matches);

    return array_slice($matches, 1);
}

/**
 * Checks if path matches URI.
 * If the path has arguments (designated by {}) it compares
 * a regex of the path to the URI. Otherwise, it makes a 
 * direct comparison of the URI and the path.
 **/
function path_matches_uri(string $path): bool
{
    return preg_match("/[{}]/", $path)
        ? preg_match(path_to_regex($path), request()->uri)
        : request()->uri === $path;
}

/**
 * Listens to an HTTP method then confirms if the path matches 
 * the URI. If it pases both these tests, it runs the callable
 * on any path arguments designated by {}.
 **/
function route(string $method): callable
{
    return function (string $path, callable $callable) use ($method) {
        if (request()->method !== $method) {
            return;
        }

        if (path_matches_uri($path)) {
            $callable(...path_args($path));
        }
    };
}

/**
 * Listens to an HTTP method then confirms if the path matches 
 * the URI. If it pases both these tests, it iterates over a 
 * list of callables applying any path arguments to each 
 * function.
 **/
function route_chain(string $method): callable
{
    return function (string $path, array $callables) use ($method) {
        if (request()->method !== $method) {
            return;
        }

        if (path_matches_uri($path)) {
            foreach ($callables as $callable) {
                $callable(...path_args($path));
            }
        }
    };
}

/**
 * Helper function for piping functions onto a value.
 *
 * Example:
 * pipe(10)
 *   (fn($x) => $x * 2)
 *   ->value;
 *
 * //returns 20;
 **/
function pipe(mixed $value): Pipe
{
    return new Pipe($value);
}

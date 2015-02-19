<?php

class Router
{
    const CLASS_METHOD_DELIMITER = '->';

    /**
     * @var array
     */
    protected $routes;

    /**
     * @var string|callable
     */
    protected $notFound;

    /**
     * Maps route
     *
     * @param string $path Path
     * @param string|callable $callback
     * @param string|array $methods
     * @param array $conditions
     */
    public function map($path, $callback, $methods = 'GET', array $conditions = array())
    {
        if (is_string($methods)) {
            $methods = array($methods);
        }

        $this->routes[] = array(
            'path'       => $path,
            'callback'   => $callback,
            'methods'    => $methods,
            'conditions' => $conditions
        );

        return $this;
    }

    public function notFound($callback)
    {
        $this->notFound = $callback;

        return $this;
    }

    /**
     * Starts router
     */
    public function run()
    {
        // Route search
        foreach ($this->routes as $route) {
            try {
                if ($this->executeRoute($route)) {
                    return;
                }
            } catch (RoutePassException $e) {
                continue;
            }
        }

        // Appropriate route is not found
        call_user_func($this->getCallback($this->notFound));
    }

    /**
     * Executes given route
     *
     * @param array $route
     * @return bool
     */
    protected function executeRoute($route)
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Check HTTP methods
        if (!count($route['methods']) || array_search($requestMethod, $route['methods']) !== false) {
            // Compiling path
            $pattern = $this->compilePath($route['path'], $route['conditions']);

            // Checking request uri
            if (preg_match($pattern, $requestUri, $matches)) {
                // Remove capturing groups without names
                foreach (range(0, floor(count($matches) / 2)) as $index) {
                    unset($matches[$index]);
                }

                // Executing callback
                call_user_func_array($this->getCallback($route['callback']), $matches);

                return true;
            }
        }

        return false;
    }

    protected function getCallback($callback)
    {
        if (is_string($callback) && strpos($callback, self::CLASS_METHOD_DELIMITER) !== false) {
            list($class, $method) = explode(self::CLASS_METHOD_DELIMITER, $callback);

            $classInstance = new $class;
            return array($classInstance, $method);
        }

        return $callback;
    }

    /**
     * Compiles path to regular expression
     *
     * @param string $path
     * @param array $conditions
     * @return string
     */
    protected function compilePath($path, array $conditions)
    {
        // Return path, if it is already a regular expression
        if (preg_match('/^\/\^.+\$\/[a-zA-Z]*$/', $path)) {
            return $path;
        } else {
            // Make slashes are regex-compatible
            $pattern = str_replace('/', '\/', $path);

            // Make existing capturing groups optional
            $pattern = str_replace(')', ')?', $pattern);

            // Creating named capturing groups and injecting conditions
            $pattern = preg_replace_callback('/\<([a-zA-Z_]+)\>/', function ($matches) use ($conditions) {
                $condition = isset($conditions[$matches[1]]) ? $conditions[$matches[1]] : '[a-z]+';

                return sprintf('(?P<%s>%s)', $matches[1], $condition);
            }, $pattern);

            return sprintf('/^%s$/i', $pattern);
        }
    }
}

/**
 * Exception to pass the route
 */
class RoutePassException extends Exception
{
}
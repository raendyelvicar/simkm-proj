<?php

namespace App\Core;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $key, callable $resolver): void
    {
        $this->bindings[$key] = $resolver;
    }

    public function make(string $key)
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        if (isset($this->bindings[$key])) {
            $this->instances[$key] = ($this->bindings[$key])($this);
            return $this->instances[$key];
        }

        // Fallback: try to auto-instantiate the class directly
        if (class_exists($key)) {
            $this->instances[$key] = new $key();
            return $this->instances[$key];
        }

        throw new \Exception("No binding found for {$key}");
    }
}

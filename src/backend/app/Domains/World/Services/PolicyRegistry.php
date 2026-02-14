<?php

namespace App\Domains\World\Services;

use Exception;
use Illuminate\Container\Container;

class PolicyRegistry
{
    protected array $policies = [];

    public function __construct(protected Container $container) {}

    public function register(string $alias, string $class): void
    {
        $this->policies[$alias] = $class;
    }

    /**
     * Resolve a policy instance by alias.
     * 
     * @template T
     * @param string $alias
     * @param class-string<T>|null $expectedInterface
     * @return object|T
     */
    public function resolve(string $alias, ?string $expectedInterface = null): object
    {
        if (!isset($this->policies[$alias])) {
            throw new Exception("Policy alias not found: {$alias}");
        }

        $class = $this->policies[$alias];
        $instance = $this->container->make($class);

        if ($expectedInterface && !($instance instanceof $expectedInterface)) {
             throw new Exception("Policy {$alias} ({$class}) must implement {$expectedInterface}");
        }

        return $instance;
    }
}

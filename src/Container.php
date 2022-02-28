<?php

declare(strict_types=1);

namespace Note\Container;

use Closure;
use Note\Container\ContainerInterface;
use Note\Container\Exception\NotFoundException;
use Note\Container\Exception\ContainerException;

class Container implements ContainerInterface
{
    /**
     * Undocumented variable
     *
     * @var array[]
     */
    protected $bindings = [];

    /**
     * Undocumented variable
     *
     * @var array[]
     */
    protected $instances = [];

    /**
     * Undocumented variable
     *
     * @var array[]
     */
    protected $aliases = [];

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
    }

    /**
     * Undocumented function
     *
     * @param string $id
     * @param string|Closure|null $entry
     *
     * @return void
     *
     * @throws ContainerException
     */
    public function set(string $id, string|Closure|null $entry = null): void
    {
        $this->setBindings($id, $entry, false, 'set', 'entry');
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * Undocumented function
     *
     * @param string $abstract
     * @param string|Closure|null $concrete
     * @param bool $shared
     *
     * @return void
     *
     * @throws ContainerException
     */
    public function bind(string $abstract, string|Closure|null $concrete = null, bool $shared = false): void
    {
        $this->setBindings($abstract, $concrete, $shared);
    }

    /**
     * Undocumented function
     *
     * @param string $abstract
     *
     * @return void
     */
    public function unbind(string $abstract): void
    {
        unset(
            $this->bindings[$abstract],
            $this->instances[$abstract]
        );
    }

    /**
     * Undocumented function
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function bound(string $abstract): bool
    {
        return $this->has($abstract);
    }

    /**
     * Undocumented function
     *
     * @param string $abstract
     * @param string|Closure|null $concrete
     *
     * @return void
     *
     * @throws ContainerException
     */
    public function singleton(string $abstract, string|Closure|null $concrete = null): void
    {
        $this->setBindings($abstract, $concrete, true, 'singleton');
    }

    /**
     * Undocumented function
     *
     * @param string $alias
     * @param string $abstract
     *
     * @return void
     *
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function alias(string $alias, string $abstract): void
    {
        // todo fix error message
        if (!$this->has($abstract)) throw new NotFoundException("Error Processing Request", E_ERROR);
        if (!is_string($alias)) throw new ContainerException("Error Processing Request", E_ERROR);
        if ($alias === $abstract) throw new ContainerException("Error Processing Request", E_ERROR);

        $this->aliases[$alias] = $abstract;
    }

    /**
     * @param string $abstract
     * @param string|Closure|null $concrete
     * @param bool $shared
     *
     * @return void
     *
     * @throws ContainerException
     */
    private function setBindings(string $abstract, string|Closure|null $concrete = null, bool $shared = false): void
    {
        $this->unbind($abstract);

        if (is_null($concrete)) $concrete = $abstract;

        if (!$concrete instanceof Closure && !class_exists($concrete)) {

            $method = count(func_get_args()) >= 4 ? func_get_arg(3) : 'bind';
            $param = count(func_get_args()) >= 5 ? func_get_arg(4) : 'concrete';

            throw new ContainerException(self::class . "::{$method}() Argument #2 \${$param} could not be resolved or class does not exist.", E_ALL);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }
}

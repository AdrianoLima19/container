<?php

declare(strict_types=1);

namespace Note\Container;

use Closure;
use ReflectionClass;
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
     * @param string $abstract
     * @param array $parameters
     *
     * @return mixed
     *
     * @throws ContainerException
     */
    public function resolve(string $abstract, array $parameters = []): mixed
    {
        if (isset($this->instance[$abstract])) return $this->instance[$abstract];

        $bind = $this->resolveBinding($abstract);

        if ($bind['concrete'] instanceof Closure) {

            if ($bind['shared']) return $this->instance[$abstract] = $bind['concrete']($this);

            return $bind['concrete']($this);
        }

        $class = new ReflectionClass($bind['concrete']);

        if (!$class->isInstantiable()) {

            throw new ContainerException(self::class . "::resolve() Argument #1 \$abstract {$abstract} is not instantiable.", E_USER_ERROR);
        }

        $dependencies = $class?->getConstructor()?->getParameters();

        if (empty($dependencies)) {

            if ($bind['shared']) return $this->instance[$abstract] = $class->newInstanceWithoutConstructor();

            return $class->newInstanceWithoutConstructor();
        }

        $dependencies = $this->resolveDependencies($dependencies, $parameters, $abstract);

        if ($bind['shared']) return $this->instance[$abstract] = $class->newInstanceArgs($dependencies);

        return $class->newInstanceArgs($dependencies);
    }

    /**
     * Undocumented function
     *
     * @param string $abstract
     * @param array $parameters
     *
     * @return mixed
     *
     * @throws ContainerException
     */
    public function make(string $abstract, array $parameters = []): mixed
    {
        throw new ContainerException('Method not yet implemented', 1);
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
     * Undocumented function
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function isShared(string $abstract)
    {
        return
            isset($this->bindings[$abstract]) && $this->bindings[$abstract]['shared'] === true ||
            isset($this->bindings[$this->getAlias($abstract)]) && $this->bindings[$this->getAlias($abstract)]['shared'] == true;
    }

    /**
     * Undocumented function
     *
     * @param string $alias
     *
     * @return bool
     */
    public function isAlias(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * Undocumented function
     *
     * @param string $alias
     *
     * @return string|bool
     */
    public function getAlias(string $alias): string|bool
    {
        return isset($this->aliases[$alias])
            ? $this->aliases[$alias]
            : false;
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

    /**
     * Undocumented function
     *
     * @param string $abstract
     *
     * @return array
     *
     * @throws NotFoundException
     */
    private function resolveBinding(string $abstract): array
    {
        if ($this->has($abstract)) return $this->bindings[$abstract];

        if ($this->isAlias($abstract)) return $this->bindings[$this->getAlias($abstract)];

        if (class_exists($abstract)) {
            return [
                'concrete' => $abstract,
                'shared' => false
            ];
        }

        throw new NotFoundException(self::class . "::resolve() Argument #1 \$abstract could not be resolved or target class [{$abstract}] does not exist.", E_USER_ERROR);
    }

    /**
     * Undocumented function
     *
     * @param array $dependencies
     * @param array $parameters
     * @param string $abstract
     *
     * @return array
     *
     * @throws NotFoundException
     */
    private function resolveDependencies(array $dependencies, array $parameters = [], string $abstract): array
    {
        $resolve = [];

        foreach ($dependencies as $dependency) {
            $name = $dependency->name;

            if (!empty($parameters[$name]) && $resolve[] = $parameters[$name]) continue;

            if ($class = $dependency->getClass()?->name) {

                $resolve[] = $this->resolve($class, $parameters);
                continue;
            }

            if ($dependency->isDefaultValueAvailable()) {

                $resolve[] = $dependency->getDefaultValue();
                continue;
            }

            throw new NotFoundException("Error Processing Request {$abstract}", 1); //todo fix error message
        }

        return $resolve;
    }
}

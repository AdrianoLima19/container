<?php

declare(strict_types=1);

namespace Artosh\Container;

use Closure;
use TypeError;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use Artosh\Container\Exception\NotFoundException;
use Artosh\Container\Exception\ContainerException;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected $binding;

    /**
     * @var object[]
     */
    protected $resolved;

    /**
     * @param  string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->binding[$id]);
    }

    /**
     * @param  string $id
     *
     * @return mixed
     *    
     * @throws TypeError
     * @throws NotFoundException
     */
    public function get(string $id): mixed
    {
        if (isset($this->binding[$id])) {
            return $this->make($id);
        }

        throw new NotFoundException(sprintf("%s::get() \$id was not set in container. found null", self::class), E_ERROR);
    }

    /**
     * @param  string               $id
     * @param  string|\Closure|null $entry
     *
     * @return void
     * 
     * @throws ContainerException
     */
    public function set(string $id, string|\Closure $entry = null): void
    {
        $this->createBind($id, $entry, false);
    }

    /**
     * @param  string $id
     *
     * @return void
     */
    public function unset(string $id): void
    {
        unset(
            $this->binding[$id],
            $this->resolved[$id]
        );
    }

    /**
     * @param  string $id
     *
     * @return bool
     */
    public function isShared(string $id): bool
    {
        if (!$this->has($id)) {
            return false;
        }

        return $this->binding[$id]['shared'];
    }

    /**
     * @param  string               $id
     * @param  string|\Closure|null $entry
     *
     * @return void
     *    
     * @throws TypeError
     */
    public function share(string $id, string|\Closure $entry = null): void
    {
        $this->createBind($id, $entry, true);
    }

    /**
     * @param  string $id
     * @param  array  $parameters
     *
     * @return mixed
     * 
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function make(string $id, array $parameters = []): mixed
    {
        if (isset($this->resolved[$id])) {
            return $this->resolved[$id];
        }

        if ($this->has($id)) {
            $bind = $this->binding[$id];
        } else if (class_exists($id)) {
            $bind = [
                'entry' => $id,
                'shared' => false,
            ];
        } else {
            throw new NotFoundException(sprintf("%s::make() Argument #1 \$id could not be resolved or target class [%s] does not exist.", self::class, $id), E_ERROR);
        }

        if ($bind['entry'] instanceof Closure) {
            if ($bind['shared']) {
                return $this->resolved[$id] = $bind['entry']($this);
            }

            return $bind['entry']($this);
        }

        $reflectClass = new ReflectionClass($bind['entry']);

        if (!$reflectClass->isInstantiable()) {
            $method = (debug_backtrace()[1]['function']  ?? 'make');
            throw new ContainerException(sprintf("%s::%s() Argument #1 \$id [%s] is not instantiable.", self::class, $method, $id), E_ERROR);
        }

        $dependencies = $reflectClass?->getConstructor()?->getParameters();

        if (empty($dependencies)) {
            if ($bind['shared']) {
                return $this->resolved[$id] = $reflectClass->newInstanceWithoutConstructor();
            }

            return $reflectClass->newInstanceWithoutConstructor();
        }

        $resolve = [];

        foreach ($dependencies as $dependency) {
            $name = $dependency->name;

            if (!empty($parameters[$name]) && $resolve[] = $parameters[$name]) {
                continue;
            }

            if (class_exists($class = $dependency->getType()?->getName())) {
                $resolve[] = $this->make($class, $parameters);
                continue;
            }

            if ($dependency->isDefaultValueAvailable()) {
                $resolve[] = $dependency->getDefaultValue();
                continue;
            }

            if ($dependency->getType()?->allowsNull()) {
                $resolve[] = null;
                continue;
            }

            throw new NotFoundException("Error Processing Request {$id}", 1);
        }

        if ($bind['shared']) {
            return $this->resolved[$id] = $reflectClass->newInstanceArgs($resolve);
        }

        return $reflectClass->newInstanceArgs($resolve);
    }

    /**
     * @param  string|array|callable $callback
     * @param  array                 $parameters
     *
     * @return mixed
     * 
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function call(string|array|callable $callback, array $parameters = []): mixed
    {
        if (is_string($callback) && strpos($callback, "::")) {
            $callback = explode("::", $callback);
        }

        if (is_array($callback)) {
            if ($this->has($callback[0])) {
                $callback[0] = $this->binding[$callback[0]]['entry'];
            }
            if (!class_exists($callback[0])) {
                throw new ContainerException("Error Processing Request", 1);
            }
            $reflectClass = new ReflectionMethod($callback[0], $callback[1]);
        } else if (gettype($callback) === 'object') {
            if (method_exists($callback, '__invoke')) {
                $reflectClass = new ReflectionMethod($callback, '__invoke');
            } else {
                throw new ContainerException(self::class . "::call() Argument #1 \$callback method not provided nor the class have a __invoke method.", E_ERROR);
            }
        } else {
            $reflectClass = new ReflectionFunction($callback);
        }

        $resolve = [];

        foreach ($reflectClass?->getParameters() as $param) {
            $name = $param->name;

            if (!empty($parameters[$name]) && $resolve[] = $parameters[$name]) {
                continue;
            }

            if (class_exists($class = $param->getType()?->getName())) {
                $resolve[] = $this->make($class, $parameters);
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $resolve[] = $param->getDefaultValue();
                continue;
            }

            if ($param->getType()?->allowsNull()) {
                $resolve[] = null;
                continue;
            }

            throw new NotFoundException("Error Processing Request {$callback[0]}", 1);
        }

        if (gettype($callback) === 'string' || $callback instanceof Closure || gettype($callback) === 'object') {

            return $reflectClass->invokeArgs($callback, $resolve);
        }

        if (is_string($callback[0])) {

            $callback[0] = $this->make($callback[0], $parameters);
        }

        return $reflectClass->invokeArgs($callback[0], $resolve);
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        unset(
            $this->binding,
            $this->resolved,
        );
    }

    final public function __destruct()
    {
        $this->reset();
    }

    private function __clone()
    {
    }

    public function __get($arg)
    {
        throw new ContainerException("Error Processing Request", 1);
    }

    /**
     * @param  string               $id
     * @param  string|\Closure|null $entry
     * @param  bool                 $shared
     *
     * @return void
     * 
     * @throws TypeError
     */
    private function createBind(string $id, string|\Closure $entry = null, bool $shared = false)
    {
        unset(
            $this->binding[$id],
            $this->resolved[$id]
        );

        if (is_null($entry)) {
            $entry = $id;
        }

        if (!$entry instanceof Closure && !is_string($entry)) {
            $method = (debug_backtrace()[1]['function']  ?? 'set');

            throw new TypeError(
                sprintf("%s::%s() \$entry expected type 'string|\\Closure|null'. found %s", self::class, $method, gettype($entry)),
                E_ERROR
            );
        }

        $this->binding[$id] = compact('entry', 'shared');
    }
}

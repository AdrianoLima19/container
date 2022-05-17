<?php

declare(strict_types=1);

namespace Artosh\Container;

use TypeError;
use Artosh\Container\Exception\NotFoundException;
use Artosh\Container\Exception\ContainerException;
use Psr\Container\ContainerInterface as PsrContainer;

interface ContainerInterface extends PsrContainer
{
    /**
     * @param  string $id
     *
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * @param  string $id
     *
     * @return mixed
     *    
     * @throws TypeError
     * @throws NotFoundException
     */
    public function get(string $id): mixed;

    /**
     * @param  string               $id
     * @param  string|\Closure|null $entry
     *
     * @return void
     * 
     * @throws ContainerException
     */
    public function set(string $id, string|\Closure $entry = null): void;

    /**
     * @param  string $id
     *
     * @return void
     */
    public function unset(string $id): void;

    /**
     * @param  string $id
     *
     * @return bool
     */
    public function isShared(string $id): bool;

    /**
     * @param  string               $id
     * @param  string|\Closure|null $entry
     *
     * @return void
     *    
     * @throws TypeError
     */
    public function share(string $id, string|\Closure $entry = null): void;

    /**
     * @param  string $id
     * @param  array  $parameters
     *
     * @return mixed
     * 
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function make(string $id, array $parameters = []): mixed;

    /**
     * @param  string|array|callable $callback
     * @param  array                 $parameters
     *
     * @return mixed
     * 
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function call(string|array|callable $callback, array $parameters = []): mixed;

    /**
     * @return void
     */
    public function reset(): void;
}

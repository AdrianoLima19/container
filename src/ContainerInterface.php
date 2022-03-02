<?php

declare(strict_types=1);

namespace Note\Container;

use Closure;
use Psr\Container\ContainerInterface as PsrContainer;
use Psr\Container\NotFoundExceptionInterface as NotFoundException;
use Psr\Container\ContainerExceptionInterface as ContainerException;

interface ContainerInterface extends PsrContainer
{
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
    public function set(string $id, string|Closure|null $entry = null): void;

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
    public function bind(string $abstract, string|Closure|null $concrete = null, bool $shared = false): void;

    /**
     * Undocumented function
     *
     * @param string $abstract
     *
     * @return void
     */
    public function unbind(string $abstract): void;

    /**
     * Undocumented function
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function bound(string $abstract): bool;

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
    public function singleton(string $abstract, string|Closure|null $concrete = null): void;

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
    public function resolve(string $abstract, array $parameters = []): mixed;

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
    public function make(string $abstract, array $parameters = []): mixed;

    // todo call

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
    public function alias(string $alias, string $abstract): void;

    /**
     * Undocumented function
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function isShared(string $abstract);

    /**
     * Undocumented function
     *
     * @param string $alias
     *
     * @return bool
     */
    public function isAlias(string $alias): bool;

    /**
     * Undocumented function
     *
     * @param string $alias
     *
     * @return string|bool
     */
    public function getAlias(string $alias): string|bool;
}

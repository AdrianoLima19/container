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

    // todo resolve
    // todo make
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

    // todo isShared
    // todo isAlias
    // todo getAlias
}

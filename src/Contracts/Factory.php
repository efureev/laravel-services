<?php

namespace Fureev\Services\Contracts;

/**
 * Interface Factory
 *
 * @package Fureev\Services\Contracts
 */
interface Factory
{
    /**
     * Get an provider implementation.
     *
     * @param  string $driver
     *
     * @return \Fureev\Services\Contracts\Provider
     */
    public function driver($driver = null);
}

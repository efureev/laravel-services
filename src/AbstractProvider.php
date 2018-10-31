<?php

namespace Fureev\Services;

use Fureev\Services\Contracts\Provider;
use Illuminate\Support\Arr;
use Php\Support\Exceptions\MissingPropertyException;
use Php\Support\Traits\ConfigurableTrait;

/**
 * Class AbstractProvider
 *
 * @package App\Services
 */
abstract class AbstractProvider implements Provider
{
    use ConfigurableTrait;

    /**
     * Enable Driver
     *
     * @var bool
     */
    public $enabled = true;

    /**
     * The name of this driver
     *
     * @var string
     */
    public $name;

    /**
     * The config
     *
     * @var array
     */
    protected $config;

    /**
     * @var bool
     */
    protected $defer = true;


    /**
     * Create a new provider instance.
     *
     * @param array $config
     *
     * @throws \Php\Support\Exceptions\MissingPropertyException
     */
    public function __construct(array $config)
    {
        $this
            ->validateConfig($config)
            ->setDriverConfig($config)
            ->configurable($config);
    }

    /**
     * @param array $config
     *
     * @return $this
     * @throws \Php\Support\Exceptions\MissingPropertyException
     */
    public function validateConfig(array $config)
    {
        if (empty($config['name'])) {
            throw new MissingPropertyException($config, 'name');
        }

        $this->name = $config['name'];

        return $this;
    }

    /**
     * Set driver name
     *
     * @param string $name
     *
     * @return $this
     */
    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string|null $key
     *
     * @return mixed
     * @throws \Exception
     */
    public function getDriverConfig(?string $key = null)
    {
        return Arr::get($this->config, $key);
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    protected function setDriverConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function label()
    {
        return $this->getDriverConfig('label') ?? $this->name;
    }
}

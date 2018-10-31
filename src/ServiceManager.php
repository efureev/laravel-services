<?php

namespace Fureev\Services;

//use App\Services\User\Provider as UserProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Manager as BaseManager;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Php\Support\Exceptions\InvalidConfigException;
use Php\Support\Exceptions\MissingConfigException;

/**
 * Class ServiceManager
 *
 * @package App\Services
 * @method AbstractProvider driver(string $driver = null)
 */
class ServiceManager extends BaseManager implements Contracts\Factory
{
    /** @var array */
    protected $config;

    /**
     * ServiceManager constructor.
     *
     * @param $app
     *
     * @throws \Php\Support\Exceptions\InvalidConfigException
     * @throws \Php\Support\Exceptions\MissingConfigException
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->loadDrivers();
    }

    /**
     * @param bool $andDefer
     *
     * @throws \Php\Support\Exceptions\InvalidConfigException
     * @throws \Php\Support\Exceptions\MissingConfigException
     */
    public function loadDrivers(bool $andDefer = false)
    {
        foreach ($this->getConfig('drivers') ?? [] as $service => $params) {
            if ($andDefer || (!$andDefer && isset($params['defer']) && $params['defer'] === false)) {
                $this->loadService($service);
            }
        }
    }

    /**
     * @param string $name
     *
     * @throws \Php\Support\Exceptions\InvalidConfigException
     * @throws \Php\Support\Exceptions\MissingConfigException
     */
    public function loadService(string $name)
    {
        $driver = $this->driver($name);

        $this->app->singleton($this->getConfig('name') . '.' . $driver->name, function () use ($driver) {
            return $driver;
        });
    }

    /**
     * Config path
     *
     * @return string
     */
    public static function configSection()
    {
        return 'di';
    }

    /**
     * Get config
     *
     * @param string|null $key
     *
     * @return mixed
     * @throws \Php\Support\Exceptions\InvalidConfigException
     * @throws \Php\Support\Exceptions\MissingConfigException
     */
    public function getConfig($key = null)
    {
        if (!$this->config) {

            if (!$config = $this->app['config'][ static::configSection() ]) {
                throw new MissingConfigException($config, static::configSection());
            }

            if (!is_array($config)) {
                throw new InvalidConfigException($config);
            }

            $this->config = $config;
        }

        return Arr::get($this->config, $key);

    }

    /**
     * Return list of all Providers: driver, customDriver (\Closure)
     *
     * @param bool $andDefer
     *
     * @return \Illuminate\Support\Collection
     * @throws \Php\Support\Exceptions\InvalidConfigException
     * @throws \Php\Support\Exceptions\MissingConfigException
     */
    public function getProviders(bool $andDefer = false)
    {
        if ($andDefer) {
            $this->loadDrivers(true);
        }

        $result = $this->getDrivers();

        foreach ($this->customCreators ?? [] as $key => $val) {
            $result[ $key ] = $val;
        }

        return collect($result);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @param array|null $driverConfig
     *
     * @return mixed
     */
    /*protected function createUserDriver(?array $driverConfig = [])
    {
        return $this->buildProvider(UserProvider::class, $driverConfig);
    }*/

    /**
     * Build a provider instance.
     *
     * @param  string $provider
     * @param  array  $config
     *
     * @return mixed
     */
    public function buildProvider($provider, ?array $config)
    {
        return new $provider($this->formatConfig($config));
    }

    /**
     * Format the configuration.
     *
     * @param  array $config
     *
     * @return array
     */
    public function formatConfig(?array $config = [])
    {
        $config = $config ?? [];
        unset($config['provider']);

        return $config;
    }


    /**
     * Get the default driver name.
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException('No service driver was specified.');
    }


    /**
     * @param array|null $drivers
     *
     * @return $this
     * @throws \Php\Support\Exceptions\InvalidConfigException
     * @throws \Php\Support\Exceptions\MissingConfigException
     */
    public function buildCustomProviders(?array $drivers)
    {
        foreach ($drivers ?? [] as $driverName) {
            $this->buildCustomProvider($driverName);
        }

        return $this;
    }

    /**
     * @param string     $name
     * @param array|null $driverConfig
     *
     * @return CustomProvider|null
     * @throws \Php\Support\Exceptions\InvalidConfigException
     * @throws \Php\Support\Exceptions\MissingConfigException
     */
    public function buildCustomProvider(string $name, array $driverConfig = null)
    {
        if (!$driverConfig) {
            $driverConfig = $this->getConfig('drivers.' . $name);
        }

        $driverConfig['name'] = $name;

        if (isset($driverConfig['enabled']) && $driverConfig['enabled'] === false) {
            return null;
        }

        if ($this->hasBuildInDriver($name)) {
            $this->drivers[ $name ] = $this->{static::buildInDriverMethodName($name)}($driverConfig);
        } else {
            $provider = (!empty($driverConfig['provider']) && class_exists((string)$driverConfig['provider']))
                ? $driverConfig['provider']
                : CustomProvider::class;

            $this->drivers[ $name ] = $this->buildProvider($provider, $driverConfig);
        }

        return $this->drivers[ $name ];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasBuildInDriver(string $name): bool
    {
        return method_exists($this, static::buildInDriverMethodName($name));
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function buildInDriverMethodName(string $name): string
    {
        return 'create' . Str::studly($name) . 'Driver';
    }

    /**
     * @param string $driver
     *
     * @return \Fureev\Socialite\Two\CustomProvider|mixed|null
     * @throws \Php\Support\Exceptions\InvalidConfigException
     * @throws \Php\Support\Exceptions\MissingConfigException
     */
    protected function createDriver($driver)
    {
        if (isset($this->customCreators[ $driver ])) {
            return $this->callCustomCreator($driver);
        }

        return $this->buildCustomProvider($driver);
    }

}

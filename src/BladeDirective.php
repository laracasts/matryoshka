<?php

namespace Laracasts\Matryoshka;

use Exception;

class BladeDirective
{
    /**
     * The cache instance.
     *
     * @var RussianCaching
     */
    protected $cache;

    /**
     * A list of model cache keys.
     *
     * @param array $keys
     */
    protected $keys = [];

    /**
     * Create a new instance.
     *
     * @param RussianCaching $cache
     */
    public function __construct(RussianCaching $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle the @cache setup.
     *
     * @param mixed $model
     */
    public function setUp($model, $key = null)
    {
        ob_start();

        $this->keys[] = $key = $this->normalizeKey($model, $key);

        return $this->cache->has($key);
    }

    /**
     * Handle the @endcache teardown.
     */
    public function tearDown()
    {
        return $this->cache->put(
            array_pop($this->keys), ob_get_clean()
        );
    }

    /**
     * Normalize the cache key.
     *
     * @param mixed       $model
     * @param string|null $key
     */
    protected function normalizeKey($model, $key = null)
    {
        // If the user wants to provide their own cache
        // key, we'll opt for that.
        if (is_string($model) || is_string($key)) {
            return is_string($model) ? $model : $key;
        }
    
        // Otherwise we'll try to use the model to calculate
        // the cache key, itself.
        if (is_object($model) && method_exists($model, 'getCacheKey')) {
            return $model->getCacheKey();
        }

        throw new Exception('Could not determine an appropriate cache key.');
    }
}

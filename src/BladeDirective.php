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
        if ($model instanceof \Illuminate\Database\Eloquent\Model) {
            // If we were given a key to use, we'll always
            // prefer it over the model's cache key.
            if ($key) {
                return $key;
            }

            // Otherwise, if the model can calculate the
            // key itself, we'll use that option.
            if (method_exists($model, 'getCacheKey')) {
                return $model->getCacheKey();
            }

            // But, finally, at this point, we don't know.
            throw new Exception(
                'Please have your model use the "Laracasts\Matryoshka\Cacheable" trait.'
            );
        }

        // If we weren't given an Eloquent model, but instead
        // a string, that will be our cache key.
        if (is_string($model)) {
            return $model;
        }

        throw new Exception('Could not calculate the cache key.');
    }
}

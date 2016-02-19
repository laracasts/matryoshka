<?php

namespace Laracasts\Matryoshka;

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
    public function setUp($model)
    {
        ob_start();

        $this->keys[] = $key = $model->getCacheKey();

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
}


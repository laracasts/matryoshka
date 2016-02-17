<?php

namespace Laracasts\Dolly;

use Cache;

class RussianCaching
{
    /**
     * A list of model cache keys.
     *
     * @param array $keys
     */
    protected static $keys = [];

    /**
     * Setup our caching mechanism.
     *
     * @param mixed $model
     */
    public static function setUp($model)
    {
        static::$keys[] = $key = $model->getCacheKey();

        ob_start();

        return Cache::tags('views')->has($key);
    }

    /**
     * Teardown our cache setup.
     */
    public static function tearDown()
    {
        $key = array_pop(static::$keys);

        $html = ob_get_clean();

        return Cache::tags('views')
            ->rememberForever($key, function () use ($html) {
                return $html;
            });
    }
}

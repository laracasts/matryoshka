<?php

namespace Laracasts\Dolly;

trait Cacheable
{
    /**
     * Calculate a unique cache key for the model instance.
     */
    public function getCacheKey()
    {
        return sprintf("%s/%s-%s", 
            get_class($this),
            $this->id,
            $this->updated_at->timestamp
        );
    }
}

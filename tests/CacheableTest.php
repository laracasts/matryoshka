<?php

class CacheableTest extends TestCase
{
    /** @test */
    public function it_gets_a_unique_cache_key_for_an_eloquent_model()
    {
        $model = $this->makePost();

        $this->assertEquals(
            'Post/1-'.$model->updated_at->timestamp,
            $model->getCacheKey()
        );
    }
}

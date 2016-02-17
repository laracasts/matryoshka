<?php

use Laracasts\Dolly\RussianCaching;
use Laracasts\Dolly\BladeDirective;

class BladeDirectiveTest extends TestCase
{
    protected $doll;

    /** @test */
    public function it_sets_up_the_opening_cache_directive()
    {
        $directive = $this->createNewCacheDirective();

        $isCached = $directive->setUp($post = $this->makePost());

        $this->assertFalse($isCached);

        echo '<div>fragment</div>';

        $cachedFragment = $directive->tearDown();

        $this->assertEquals('<div>fragment</div>', $cachedFragment);
        $this->assertTrue($this->doll->has($post));
    }

    protected function createNewCacheDirective()
    {
        $cache = new \Illuminate\Cache\Repository(
            new \Illuminate\Cache\ArrayStore
        );

        $this->doll = new RussianCaching($cache);

        return new BladeDirective($this->doll);
    }
}


<?php

use Laracasts\Matryoshka\RussianCaching;
use Laracasts\Matryoshka\BladeDirective;

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

    /** @test */
    function it_can_use_a_string_as_the_cache_key()
    {
        $doll = $this->prophesize(RussianCaching::class);
        $directive = new BladeDirective($doll->reveal());

        $doll->has('foo')->shouldBeCalled();
        $directive->setUp('foo');

        ob_end_clean(); // Since we're not doing teardown.
    }

    /** @test */
    function it_can_use_a_collection_as_the_cache_key()
    {
        $doll = $this->prophesize(RussianCaching::class);
        $directive = new BladeDirective($doll->reveal());
        
        $collection = collect(['one', 'two']);
        $doll->has(md5($collection))->shouldBeCalled();
        $directive->setUp($collection);

        ob_end_clean(); // Since we're not doing teardown.
    }

    /** @test */
    function it_can_use_the_model_to_determine_the_cache_key()
    {
        $doll = $this->prophesize(RussianCaching::class);
        $directive = new BladeDirective($doll->reveal());

        $post = $this->makePost(); 
        $doll->has('Post/1-' . $post->updated_at->timestamp)->shouldBeCalled();
        $directive->setUp($post);

        ob_end_clean(); // Since we're not doing teardown.
    }

    /** @test */
    function it_can_use_a_string_to_override_the_models_cache_key()
    {
        $doll = $this->prophesize(RussianCaching::class);
        $directive = new BladeDirective($doll->reveal());

        $doll->has('override-key')->shouldBeCalled();
        $directive->setUp($this->makePost(), 'override-key');

        ob_end_clean(); // Since we're not doing teardown.
    }


    /** 
     * @test 
     * @expectedException Exception
     * */
    function it_throws_an_exception_if_it_cannot_determine_the_cache_key()
    {
        $directive = $this->createNewCacheDirective();

        $directive->setUp(new UnCacheablePost);
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

class UnCacheablePost extends \Illuminate\Database\Eloquent\Model {}

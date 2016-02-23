# Matryoshka

Matryoshka is a package for Laravel that provides Russian-Doll caching for your view logic.

> Want to learn how this exact package was made from scratch? [See Laracasts.com](https://laracasts.com/series/russian-doll-caching-in-laravel).

## Installation

### Step 1: Composer

From the command line, run:

```
composer require laracasts/matryoshka
```

### Step 2: Service Provider

For your Laravel app, open `config/app.php` and, within the `providers` array, append:

```
Laracasts\Matryoshka\MatryoshkaServiceProvider::class
```

This will bootstrap the package into Laravel.

### Step 3: Cache Driver

For this package to function properly, you must use a Laravel cache driver that supports tagging (like `Cache::tags('foo')`). Drivers such as Memcached and Redis support this feature.

Check your `.env` file, and ensure that your `CACHE_DRIVER` choice accomodates this requirement:

```
CACHE_DRIVER=memcached
```

> Have a look at [Laravel's cache configuration documentation](https://laravel.com/docs/5.2/cache#configuration), if you need any help.

## Usage

### The Basics

With the package now installed, you may use the provided `@cache` Blade directive anywhere in your views, like so:

```html
@cache('my-cache-key')
    <div>
        <h1>Hello World</h1>
    </div>
@endcache
```

By surrounding this block of HTML with the `@cache` and `@endcache` directives, we're asking the package to cache the given HTML. Now this example is trivial, however, you can imagine a more complex view that includes various nested caches, as well as lazy-loaded relationship calls that trigger additional database queries. After the initial page load that caches the HTML fragment, each subsequent refresh will instead pull from the cache. As such, those additional database queries will never be executed.

Please keep in mind that, in production, this will cache the HTML fragment "forever." For local development, on the other hand, we'll automatically flush the relevant cache for you each time you refresh the page. That way, you may update your views and templates however you wish, without needing to worry about clearing the cache manually.

Now because your production server will cache the fragments forever, you'll want to add a step to your deployment process that clears the relevant cache.

```php
Cache::tags('views')->flush();
```

### Caching Models

While you're free to hard-code any string for the cache key, the true power of Russian-Doll caching comes into play when we use a timestamp-based approach.

Consider the following fragment:

```html
@cache($post)
    <article>
        <h2>{{ $post->title }}></h2>
        <p>Written By: {{ $post->author->username }}</p>

        <div class="body">{{ $post->body }}</div>
    </article>
@endcache
```

In this example, we're passing the `$post` object, itself, to the `@cache` directive - rather than a string. The package will then look for a `getCacheKey()` method on the model. We've already done that work for you; just have your Eloquent model use the `Laracasts\Matryoshka\Cacheable` trait, like so:

```php
use Laracasts\Matryoshka\Cacheable;

class Post extends Eloquent
{
    use Cacheable;
}
```

Alternatively, you may use this trait on a parent class that each of your Eloquent models extend.

That should do it! Now, the cache key for this fragment will include the object's `id` and `updated_at` timestamp: `App\Post/1-13241235123`.

> The key is that, because we factor the `updated_at` timestamp into the cache key, whenever you update the given post, the cache key will change. This will then, in effect, bust the cache!

#### Touching

In order for this technique to work properly, it's vital that we have some mechanism to alert parent relationships (and subsequently bust parent caches) each time a model is updated. Here's a basic workflow:

1. Model is updated in the database.
2. Its `updated_at` timestamp is refreshed, triggering a new cache key for the instance.
3. The model "touches" (or pings) its parent.
4. The parent's `updated_at` timestamp, too, is updated, which busts its associated cache.
5. Only the affected fragments re-render. All other cached items remain untouched.

Luckily, Laravel offers this "touch" functionality out of the box. Consider a `Note` object that needs to alert its parent `Card` relationship each time an update occurs.

```php
<?php

namespace App;

use Laracasts\Matryoshka\Cacheable;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use Cacheable;

    protected $touches = ['card'];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
```

Notice the `$touches = ['card']` portion. This instructs Laravel to ping the `card` relationship's timestamps each time the note is updated.

Now, everything is in place. You might render your view, like so:

**resources/views/cards/_card.blade.php**

```html
@cache($card)
    <article class="Card">
        <h2>{{ $card->title }}</h2>

        <ul>
            @foreach ($card->notes as $note)
                @include ('cards/_note')
            @endforeach
        </ul>
    </article>
@endcache
```

**resources/views/cards/_note.blade.php**

```html
@cache($note)
    <li>{{ $note->body }}</li>
@endcache
```

Notice the Russian-Doll style cascading for our caches; that's the key. If any note is updated, its individual cache will clear - along with its parent - but any  siblings will remain untouched.

### Caching Collections

You won't always want to cache model instances; you may wish to cache a Laravel collection as well! No problem.

```html
@cache($posts)
    @foreach ($posts as $post)
        @include ('post')
    @endforeach
@endcache
```

Now, as long as the `$posts` collection contents does not change, that `@foreach` section will never run. Instead, as always, we'll pull from the cache.

Behind the scenes, this package will detect that you've passed a Laravel collection to the `cache` directive, and will subsequently generate a unique cache key for the collection.

## FAQ

**1. Is there any way to override the cache key for a model instance?**

Yes. Let's say you have:

```html
@cache($post)
    <div>view here</div>
@endcache
```

Behind the scenes, we'll look for a `getCacheKey` method on the model. Now, as mentioned above, you can use the `Laracasts\Matryoshka\Cacheable` trait to instantly import this functionality. Alternatively, you may pass a second argument to the `@cache` directive, like this:

```html
@cache($post, 'my-custom-key')
    <div>view here</div>
@endcache
```

This instructs the package to use `my-custom-key` for the cache instead. This can be useful for pagination and other related tasks.

**2. Where can I learn more about this approach to caching?**

Give these two articles a read:

- https://signalvnoise.com/posts/3112-how-basecamp-next-got-to-be-so-damn-fast-without-using-much-client-side-ui
- https://signalvnoise.com/posts/3113-how-key-based-cache-expiration-works

And, if you enjoy Laracasts, [watch the creation of this package from scratch here.](https://laracasts.com/series/russian-doll-caching-in-laravel)

```
 _______                 __              
|   _   |.-----.---.-.--|  |.-----.-----.
|       ||__ --|  _  |  _  ||  _  |  _  |
|___|___||_____|___._|_____||_____|_____|
```

Asadoo - An experimental lightweight (one file, ~500 lines of code) PHP framework/router.

Inspired by [Sinatra](http://www.sinatrarb.com/ "Sinatra - Ruby") / [Express](http://expressjs.com/ "Express - NodeJS") / [Silex](http://silex.sensiolabs.org/ "Silex PHP").

Requires PHP 5.3+

<h3>
 <a name="routing"></a>
 <a href="#routing">Routing</a>
</h3>

Using multiple rules

```php
<?php
asadoo()
    ->on('/view/:id')
    ->on('/view')
    ->on(function($request, $response, $dependences) {
        return $request->isGET() && $request->has('view');
    })
    ->handle(function($request, $response, $dependences) {
        $id = $request->value('id', 'ID not found!');

        $response->end($id);
    });

asadoo()->start();
```

Capturing by POST / GET / PUT / DELETE

```php
<?php
asadoo()
    ->get('/user', function($request, $response, $dependences) {
        // ...
    });

asadoo()
    ->post('/user', function($request, $response, $dependences) {
        // ...
    });

asadoo()
    ->put('/user', function($request, $response, $dependences) {
        // ...
    });

asadoo()
    ->delete('/user', function($request, $response, $dependences) {
        // ...
    });

asadoo()->start();
```

Explicit routing (asadoo\Request#forward)

```php
<?php
// Named handlers
asadoo()
    ->name('handler-1')
    ->handle(function($request, $response, $dependences) {
        $response->write('handler-1</br>');
    });

asadoo()
    ->name('handler-2')
    ->handle(function($request, $response, $dependences) {
        $response->write('handler-2</br>');
    });

// Our catch-all handler
asadoo()
    ->on('*')
    ->handle(function($request, $response, $dependences) {
        // Forward the request to another handler
        $request->forward('handler-' . rand(1, 2));
    });

asadoo()->start();
```

<h3>
 <a name="sanitize"></a>
 <a href="#sanitize">Optional input sanitize</a>
</h3>

You can define an annonymus function to sanitize GET/POST and values for all handlers. (ie to help prevent SQL Injection)

````php
<?php
asadoo()->setSanitizer(function($value, $type, $dependences) {
    return preg_replace('/[^a-z\d]/', '', $value);
});

asadoo()
    ->on('/inject/get/')
    ->handle(function($request, $response, $dependences) {
        $response->end(
            $request->get('value') // gets the sanitized value
        );
    });

asadoo()->start();
```

<h3>
 <a name="mixin"></a>
 <a href="#mixin">Mixin</a>
</h3>

You can augment base classes at runtime

```php
<?php
// Our extension
class ResponseExtension {
    // Extensions methods always have as its first argument a
    // reference to the actual object
    public function helloWorld($responseInstance) {
        $responseInstance->end('Hello World!');
    }
}

// Mix it!
asadoo\Response::mix(new ResponseExtension());

asadoo()->get('*', function($request, $response, $dependences) {
    // Using the new method
    $response->helloWorld();
});

asadoo()->start();
```

<h3>
 <a name="mixmethodsin"></a>
 <a href="#methods">Methods</a>
</h3>

```
asadoo\Facade
    after(Callable $fn)
    before(Callable $fn)
    delete(Callable $handler)
    get(Callable $handler)
    name(string $name)
    post(Callable $handler)
    put(Callable $handler)
    sanitizer(Callable $fn)
    start()
    version()

```

```
asadoo\Request
    agent([ string $matches ])
    domain()
    forward(string $handlerName)
    get(string $key)
    baseURL()
    has(string $match)
    ip()
    isHTTPS()
    isDELETE()
    isGET()
    isPOST()
    isPUT()
    method(string $method)
    path()
    port()
    post(string $key)
    scheme()
    segment(int $index)
    set(string $key, mixed $value)
    setSanitizer($fn)
    url()
    value()
```

```
asadoo\Response
    code(int $code)
    header($key, $value)
    write(string $content [, string $content [, ... ] ] )
    end(string $content)
```

Copyright (c) 2011 [Valentin Starck](http://aijoona.com/)

May be freely distributed under the MIT license. See the MIT-LICENSE file.
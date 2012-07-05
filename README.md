```
 _______                 __              
|   _   |.-----.---.-.--|  |.-----.-----.
|       ||__ --|  _  |  _  ||  _  |  _  |
|___|___||_____|___._|_____||_____|_____|
```

Asadoo - An experimental lightweight (one file, ~600 lines of code) PHP framework/router.

Inspired by [Sinatra](http://www.sinatrarb.com/ "Sinatra - Ruby") / [Express](http://expressjs.com/ "Express - NodeJS") / [Silex](http://silex.sensiolabs.org/ "Silex PHP").

Requires PHP 5.4+

Goals: 
    *Provide a succint (no classes, no namespaces) and very functional API for small apps/websites.    
    *Provide a queue-like API for more complex usages.
    *Let me play with anonymous functions and traits :)

<h3>
 <a name="routing"></a>
 <a href="#routing">Routing</a>
</h3>

Basic rules

```php
<?php
asadoo()
    ->on('/home')
    ->handle(function($memo) {
        // ...
    });

asadoo()->start();
```

Using multiple rules

```php
<?php
asadoo()
    ->on('/view')
    ->on('/view/:id')
    ->on(function($memo) {
        return $this->req->isGET() && $this->req->has('view');
    })
    ->handle(function($memo, $id = 0) {
        $this->res->write('ID: ', $id);
    });

asadoo()->start();
```

Capturing by POST / GET / PUT / DELETE

```php
<?php

asadoo()
    ->get('/user/logout', function($memo) {

    });

// Multiple actions
asadoo()
    // Routes
    ->on('/data/:entity')
    ->on('/data/:entity/:id')

    // Actions
    ->get(function($memo, $entity, $id) {
        // ...
    })
    ->post(function($memo, $entity) {
        // ...
    })
    ->put(function($memo, $entity, $id) {
        // ...
    })
    ->delete(function($memo, $entity, $id) {
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
    ->handle(function($memo) {
        $this->res->write('handler-1</br>');
    });

asadoo()
    ->name('handler-2')
    ->handle(function($memo) {
        $this->res->write('handler-2</br>');
    });

// Our catch-all handler
asadoo()
    ->on('*')
    ->handle(function($memo) {
        // Forward the request to another handler
        $req->forward('handler-' . rand(1, 2));
    });

asadoo()->start();
```
<h3>
 <a name="chaining"></a>
 <a href="#chaining">Result chaining</a>
</h3>
```php
<?php
asadoo()
    ->on('*')
    ->handle(function($memo) {
        return 'Hello ';
    });

asadoo()
    ->on('*')
    ->handle(function($memo) {
        return $memo . 'World';
    });

asadoo()
    ->on('*')
    ->handle(function($memo) {
        $res->end($memo . '!');
    });

asadoo()->start(); // Outputs 'Hello World!'
```

<h3>
 <a name="sanitize"></a>
 <a href="#sanitize">Optional input sanitize</a>
</h3>

You can define an anonymous function to sanitize GET/POST and values for all handlers. (ie to help prevent SQL Injection)

````php
<?php
asadoo()->sanitizer(function($value, $type) {
    return preg_replace('/[^a-z\d]/', '', $value);
});

asadoo()
    ->on('/inject/get/')
    ->handle(function($memo) {
        $this->res->end(
            $this->req->get('value') // gets the sanitized value
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

asadoo()->get('*', function($memo) {
    // Using the new method
    $this->response->helloWorld();
});

asadoo()->start();
```


###API###

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
asadoo\ExecutionContext (aka functional handlers)
    request
    response
    core
```

```
asadoo\Request
    agent([ string $matches ])
    domain()
    forward(string $handlerName[, mixed $memo])
    get(string $key)
    baseURL()
    ip()
    isHTTPS()
    isDELETE()
    isGET()
    isPOST()
    isPUT()
    matches(string $match)
    method(string $method)
    path()
    port()
    post(string $key)
    scheme()
    segment(int $index)
    set(string $key, mixed $value)
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
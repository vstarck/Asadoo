```
 _______                 __              
|   _   |.-----.---.-.--|  |.-----.-----.
|       ||__ --|  _  |  _  ||  _  |  _  |
|___|___||_____|___._|_____||_____|_____|
```

Asadoo - An experimental lightweight (one file, less than 500 loc) PHP framework/router.

Inspired by [Sinatra](http://www.sinatrarb.com/ "Sinatra - Ruby") / [Express](http://expressjs.com/ "Express - NodeJS") / [Silex](http://silex.sensiolabs.org/ "Silex PHP").

Requires PHP 5.3+

### Routing

Using multiple rules

```php
<?php
asadoo()
    ->on('/view/:id')
    ->on('/view')
    ->on(function($request, $response, $dependences) {
        return $request->has('view');
    })
    ->handle(function($request, $response, $dependences) {
        $id = $request->value('id', 'ID not found!');

        $response->end($id);
    });

asadoo()->start();
```

Capturing by POST / GET

```php
<?php
asadoo()
    ->get('/form', function($request, $response, $dependences) {
        // ...
    });

asadoo()
    ->post('/register', function($request, $response, $dependences) {
        // ...
    });

asadoo()->start();
```

### Optional input sanitize

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
            $request->get('value')
        );
    });

asadoo()->start();
```

### Mixin

You can augment base classes at runtime

```php
<?php
// Our extension
class RequestExtended {
    public function ip($asadooRequestInstance) {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Mix!
AsadooRequest::mix(new RequestExtended());

asadoo()->get('*', function($request, $response, $dependences) {
    $response->write(
        // Using the new method
        $request->ip()
    );
});

asadoo()->start();
```

Copyright (c) 2011 [Valentin Starck](http://aijoona.com/)

May be freely distributed under the MIT license. See the MIT-LICENSE file.
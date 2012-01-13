```
 _______                 __              
|   _   |.-----.---.-.--|  |.-----.-----.
|       ||__ --|  _  |  _  ||  _  |  _  |
|___|___||_____|___._|_____||_____|_____|
```

Asadoo - An experimental lightweight PHP framework/router

Requires PHP 5.3+

```php
<?php
asadoo()
    ->on('/view/:id')
    ->on('/view')
    ->on(function($request, $response, $dependences) {
        return $request->has('view');
    })
    ->handle(function($request, $response, $dependences) {
        $id = $request->get('id', 'ID not found!');

        $response->send($id);

        $response->end();
    });
```

Copyright (c) 2011 Valentin Starck

May be freely distributed under the MIT license. See the MIT-LICENSE file.
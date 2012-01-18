<?php

include '../../dist/asadoo.php';
include 'Mustache.php';
include 'Query.php';

asadoo()->setBasePath('/examples/blog_example');

asadoo()->dependences()->register('query', function() {
    return new Query();
});

class View {
    public function render($asadooResponseInstance, $path, $vars = array()) {
        $template = file_get_contents($path);
        $mustache = new Mustache();

        $asadooResponseInstance->send(
            $mustache->render($template, $vars)
        );
    }
}

AsadooResponse::mix(new View());

// Home
asadoo()
        ->on('/')
        ->on('/home/')
        ->handle(function($request, $response, $dependences) {
            $posts = $dependences->query->from('post')->get();

            $posts = array_reverse($posts);

            $response->render('views/home.html', array(
                'title' => 'Blog Home',
                'posts' => $posts,
                'base' => AsadooCore::getInstance()->getBasePath()
            ));

            $response->end();
        });

// Entries
asadoo()
        ->on(function($request, $response, $dependences) {
            $result = $dependences->query
                    ->from('post')
                    ->where('url', $request->path())
                    ->get();

            // Save the result
            $request->set('post', $result);

            // Handle valid entries only
            return count($result);
        })
        ->handle(function($request, $response, $dependences) {
            $posts = $request->value('post');

            $response->render('views/post.html', $posts[0]);

            $response->end();
        });

// Everything else, 404
asadoo()
        ->on('*')
        ->handle(function($request, $response, $dependences) {
            $response->code(404);
            $response->send('404 - Not found!');
        });

asadoo()->start();
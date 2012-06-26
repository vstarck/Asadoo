<?php
namespace asadoo;

final class ExecutionContext extends \Pimple {
    use Mixable;

    public function __construct($core, $req, $res) {
        $this->core = $core;
        $this->req = $this->request = $req;
        $this->res = $this->response = $res;
    }

    private function matches($test) {
        return $this->core->matches($test);
    }
}

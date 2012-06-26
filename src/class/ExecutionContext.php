<?php
namespace asadoo;

final class ExecutionContext extends \Pimple {
    use Mixable;

    public function __construct($req, $res) {
        $this->req = $this->request = $req;
        $this->res = $this->response = $res;
    }
}

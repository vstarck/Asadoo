<?php
final class AsadooResponse extends AsadooMixin {
    private $core;
    private $code = 200;
    private $formatters = array();
    private $output = null;

    private $codes = array(
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '307' => 'Temporary Redirect',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported'
    );

    public function __construct($core) {
        $this->core = $core;
        ob_start();
    }

    private function sendResponseCode($code) {
        if (isset($this->codes[$code])) {
            $this->header('HTTP/1.0', $code . ' ' . $this->codes[$code]);
            return true;
        }

        return false;
    }

    public function code($code) {
        $this->code = $code;
        return $this;
    }

    public function header($key, $value) {
        header($key . ' ' . $value);
    }

    public function write() {
        $arguments = func_get_args();

        foreach ($arguments as $arg) {
            echo $arg;
        }
    }

    public function end() {
        $arguments = func_get_args();

        if(count($arguments)) {
            call_user_func_array(array($this, 'write'), $arguments);
        }

        AsadooCore::getInstance()->end();

        $this->sendResponseCode($this->code);

        $this->output = ob_get_clean();

        foreach ($this->formatters as $formatter) {
            if (is_callable($formatter)) {
                $this->output = $formatter($this->output);
            }
        }

        echo $this->output;
    }

    public function format($formatter) {
        $this->formatters[] = $formatter;
    }
}
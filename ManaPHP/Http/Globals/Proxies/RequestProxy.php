<?php
namespace ManaPHP\Http\Globals\Proxies;

use ArrayAccess;

class RequestProxy implements ArrayAccess
{
    /**
     * @var \ManaPHP\Http\Request
     */
    protected $_request;

    /**
     * RequestContext constructor.
     *
     * @param \ManaPHP\Http\Request $request
     */
    public function __construct($request)
    {
        $this->_request = $request;
    }

    public function offsetExists($offset)
    {
        return isset($this->_request->_context->_REQUEST[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_request->_context->_REQUEST[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->_request->_context->_REQUEST[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_request->_context->_REQUEST[$offset]);
    }

    public function __debugInfo()
    {
        return $this->_request->_context->_REQUEST;
    }
}
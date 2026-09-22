<?php

namespace DashaMail;

/**
 * Wraps the `data` payload of a successful API call together with its
 * `meta` (pagination info) and the human-readable `msg.text` DashaMail sent.
 *
 * Behaves like an array/list so most callers never need to know it exists:
 *
 *   $lists = $dashamail->lists->all();
 *   foreach ($lists as $list) { ... }
 *   echo $lists[0]['name'];
 *
 * Paginated endpoints (get_members and friends) additionally expose
 * hasMore()/getLimit() taken from the `meta` object DashaMail returns
 * instead of a total count.
 */
class Response implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /** @var mixed */
    private $data;

    /** @var array */
    private $meta;

    /** @var string|null */
    private $message;

    public function __construct($data, array $meta = [], $message = null)
    {
        $this->data = $data;
        $this->meta = $meta;
        $this->message = $message;
    }

    /** The raw `data` payload: an array, an associative array, a scalar, or null. */
    public function getData()
    {
        return $this->data;
    }

    /** The full `meta` object returned alongside `data`, if any. */
    public function getMeta()
    {
        return $this->meta;
    }

    /** `msg.text` from the envelope, e.g. "OK" or a human-readable confirmation. */
    public function getMessage()
    {
        return $this->message;
    }

    /** True when a paginated listing has more rows beyond the returned page. */
    public function hasMore()
    {
        return !empty($this->meta['has_more']);
    }

    /** The effective page size DashaMail used to answer a paginated listing. */
    public function getLimit()
    {
        return isset($this->meta['limit']) ? (int)$this->meta['limit'] : null;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return is_array($this->data) && array_key_exists($offset, $this->data);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return is_array($this->data) ? $this->data[$offset] : null;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (!is_array($this->data)) {
            $this->data = [];
        }
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        if (is_array($this->data)) {
            unset($this->data[$offset]);
        }
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator(is_array($this->data) ? $this->data : []);
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return is_array($this->data) ? count($this->data) : 0;
    }
}

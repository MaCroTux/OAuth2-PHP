<?php

namespace App\Application\Actions;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /** @var string */
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function __toString()
    {
        return $this->content;
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function detach()
    {
        // TODO: Implement detach() method.
    }

    public function getSize()
    {
        return strlen($this->content);
    }

    public function tell()
    {
        // TODO: Implement tell() method.
    }

    public function eof()
    {
        return false;
    }

    public function isSeekable()
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        // TODO: Implement seek() method.
    }

    public function rewind()
    {
        // TODO: Implement rewind() method.
    }

    public function isWritable()
    {
        return true;
    }

    public function write($string)
    {
        $this->content .= $string;

        return $this->getSize();
    }

    public function isReadable()
    {
        return true;
    }

    public function read($length)
    {
        return $this->content;
    }

    public function getContents()
    {
        return $this->content;
    }

    public function getMetadata($key = null)
    {
        return [];
    }
}
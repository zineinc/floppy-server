<?php

namespace ZineInc\Storage\Common;

final class FileId
{
    private $id;
    private $attributes;

    public function __construct($id, array $attributes = array())
    {
        $this->id = (string)$id;
        $this->attributes = new AttributesBag($attributes);
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return AttributesBag
     */
    public function attributes()
    {
        return $this->attributes;
    }

    public function isVariant()
    {
        return count($this->attributes->all()) > 0;
    }

    public function original()
    {
        return new self($this->id);
    }
}
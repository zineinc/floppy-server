<?php

namespace ZineInc\Storage\Common;

final class FileId
{
    private $id;
    private $attributes;
    private $filename;

    public function __construct($id, array $attributes = array(), $filename = null)
    {
        $this->id = (string)$id;
        $this->attributes = new AttributesBag($attributes);
        $this->filename = $filename;
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

    public function filename()
    {
        return $this->filename ?: $this->id;
    }

    public function isVariant()
    {
        return count($this->attributes->all()) > 0 || $this->filename() !== $this->id;
    }

    public function original()
    {
        return new self($this->id);
    }
}
<?php

namespace ZineInc\Storage\Server;

interface FileId
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return AttributesBag
     */
    public function getAttributes();
}
<?php


namespace Floppy\Server\FileHandler;


use Floppy\Common\ErrorCodes;
use Floppy\Common\FileSource;

class FileHandlerProvider
{
    /**
     * @var FileHandler[]
     */
    private $fileHandlers;

    public function __construct(array $fileHandlers)
    {
        $this->fileHandlers = $fileHandlers;
    }

    /**
     * @param $path
     * @return string File handler name
     * @throws FileHandlerNotFoundException
     */
    public function findFileHandlerNameMatches($path)
    {
        foreach ($this->fileHandlers as $name => $handler) {
            if ($handler->matches($path)) {
                return $name;
            }
        }

        throw new FileHandlerNotFoundException('file not found', ErrorCodes::FILE_NOT_FOUND);
    }

    /**
     * @return string
     * @throws FileHandlerNotFoundException
     */
    public function findFileHandlerName(FileSource $fileSource)
    {
        foreach ($this->fileHandlers as $name => $fileHandler) {
            if ($fileHandler->supports($fileSource->fileType())) {
                return $name;
            }
        }

        throw new FileHandlerNotFoundException(sprintf('File type "%s" is unsupported', $fileSource->fileType()->mimeType()));
    }

    /**
     * @param string $name File handler name
     * @return FileHandler
     * @throws \InvalidArgumentException When file handler doesn't exist
     */
    public function getFileHandler($name)
    {
        if(!isset($this->fileHandlers[$name])) {
            throw new \InvalidArgumentException(sprintf('File handler "%s" doesn\'t exist.', $name));
        }

        return $this->fileHandlers[$name];
    }
} 
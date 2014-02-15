<?php

namespace ZineInc\Storage\Tests\Server\Storage;

use PHPUnit_Framework_TestCase;
use ZineInc\Storage\Server\FileId;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\Storage\FilepathChoosingStrategy;
use ZineInc\Storage\Server\Storage\FilesystemStorage;
use ZineInc\Storage\Server\Storage\IdFactoryImpl;
use ZineInc\Storage\Server\Stream\StringStream;

class FilesystemStorageTest extends PHPUnit_Framework_TestCase
{
    const FILEPATH = 'some/file.txt';
    const FILESOURCE = 'abc';

    private $storage;
    private $filesystem;
    private $storageDir;
    private $filepath;

    protected function setUp()
    {
        $this->storageDir = __DIR__.'/../../Resources/storage/';
        $this->filepath = $this->storageDir.self::FILEPATH;
        $this->storage = new FilesystemStorage($this->storageDir, new FilesystemStorageTest_FilepathChoosingStrategy(self::FILEPATH), new IdFactoryImpl());
    }
    
    /**
     * @test
     */
    public function shouldStoreFileInValidLocation()
    {
        //given

        $fileSource = new FileSource(new StringStream(self::FILESOURCE), new FileType('text/plain', 'txt'));

        //when

        $id = $this->storage->store($fileSource);

        //then

        $this->assertNotNull($id);
        $this->assertTrue(file_exists($this->filepath));
        $this->assertEquals(self::FILESOURCE, file_get_contents($this->filepath));
    }

    protected function tearDown()
    {
        @unlink($this->storageDir.'/'.self::FILEPATH);
    }
}

class FilesystemStorageTest_FilepathChoosingStrategy implements FilepathChoosingStrategy
{
    private $filepath;

    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    public function filepath(FileId $fileId)
    {
        return $this->filepath;
    }
}
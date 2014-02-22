<?php

namespace ZineInc\Storage\Tests\Server\Storage;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Filesystem\Filesystem;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\Storage\FilepathChoosingStrategy;
use ZineInc\Storage\Server\Storage\FilesystemStorage;
use ZineInc\Storage\Server\Storage\IdFactoryImpl;
use ZineInc\Storage\Server\Storage\StoreException;
use ZineInc\Storage\Server\Stream\StringInputStream;

class FilesystemStorageTest extends PHPUnit_Framework_TestCase
{
    const FILEPATH = 'some/file.txt';
    const FILESOURCE = 'abc';
    const STORAGE_RELATIVE_DIR = '/../../Resources/storage/';

    private $storage;
    private $storageDir;
    private $filepath;

    protected function setUp()
    {
        $this->storageDir = __DIR__.self::STORAGE_RELATIVE_DIR;
        $this->filepath = $this->storageDir.self::FILEPATH;
        $this->storage = new FilesystemStorage($this->storageDir, new FilesystemStorageTest_FilepathChoosingStrategy(self::FILEPATH), new IdFactoryImpl());
    }
    
    /**
     * @test
     * @dataProvider filepathProvider
     */
    public function shouldStoreFileInCorrectLocation($actualFilepath, $expectedFilepath)
    {
        //given

        $fileSource = $this->createFileSource();

        //when

        $id = $this->storage->store($fileSource, $actualFilepath);

        //then

        $this->assertNotNull($id);
        $this->assertTrue(file_exists($expectedFilepath));
        $this->assertEquals(self::FILESOURCE, file_get_contents($expectedFilepath));
    }

    public function filepathProvider()
    {
        return array(
            array(null, __DIR__.self::STORAGE_RELATIVE_DIR.self::FILEPATH),
            array('some/extra/filepath/file', __DIR__.self::STORAGE_RELATIVE_DIR.'/some/extra/filepath/file'),
        );
    }
    
    private function createFileSource()
    {
        return new FileSource(new StringInputStream(self::FILESOURCE), new FileType('text/plain', 'txt'));
    }

    /**
     * @test
     * @expectedException ZineInc\Storage\Server\Storage\StoreException
     */
    public function store_givenFilepathIsNotSubdirectoryOfStorageRoot_throwEx()
    {
        //given

        $fileSource = $this->createFileSource();
        $invalidFilepath = '../another/file.txt';

        //when

        $this->storage->store($fileSource, $invalidFilepath);
    }

    /**
     * @test
     * @expectedException ZineInc\Storage\Server\Storage\StoreException
     */
    public function filesystemExceptionOnStore_wrapEx()
    {
        //given

        $fileSource = $this->createFileSource();
        
        $filesystem = $this->getMock('Symfony\Component\Filesystem\Filesystem');
        $this->storage->setFilesystem($filesystem);

        $filesystem->expects($this->once())
                    ->method('dumpFile')
                    ->will($this->throwException(new \Symfony\Component\Filesystem\Exception\IOException('')));

        //when

        $this->storage->store($fileSource);
    }

    protected function tearDown()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->storageDir.'/some');
        $filesystem->remove($this->storageDir.'/../another');
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
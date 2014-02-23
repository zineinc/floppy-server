<?php

namespace ZineInc\Storage\Tests\Server\Storage;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Filesystem\Filesystem;
use ZineInc\Storage\Common\FileId;
use ZineInc\Storage\Server\FileSource;
use ZineInc\Storage\Server\FileType;
use ZineInc\Storage\Server\Storage\FilepathChoosingStrategy;
use ZineInc\Storage\Server\Storage\FilesystemStorage;
use ZineInc\Storage\Server\Storage\IdFactory;
use ZineInc\Storage\Server\Storage\IdFactoryImpl;
use ZineInc\Storage\Server\Storage\StoreException;
use ZineInc\Storage\Server\Stream\StringInputStream;

class FilesystemStorageTest extends PHPUnit_Framework_TestCase
{
    const FILESOURCE = 'abc';
    const STORAGE_RELATIVE_DIR = '/../../Resources/storage/';

    const ID = 'abcdefghijk.jpg';
    const FILEPATH_FOR_ID = 'some';

    const DIFFERENT_ID = 'differentid.jpg';
    const FILEPATH_FOR_DIFFERENT_ID = 'another';

    private $storage;
    private $storageDir;
    private $filepath;

    protected function setUp()
    {
        $this->storageDir = __DIR__.self::STORAGE_RELATIVE_DIR;
        $this->filepath = $this->storageDir.self::FILEPATH_FOR_ID;
        $this->storage = new FilesystemStorage(
            $this->storageDir,
            new FilesystemStorageTest_FilepathChoosingStrategy(array(
                self::ID => self::FILEPATH_FOR_ID,
                self::DIFFERENT_ID => self::FILEPATH_FOR_DIFFERENT_ID,
            )),
            new FilesystemStorageTest_IdFactory(self::ID)
        );
    }
    
    /**
     * @test
     * @dataProvider filepathProvider
     */
    public function shouldStoreFileInCorrectLocation($fileId, $actualFilepath, $expectedFilepath)
    {
        //given

        $fileSource = $this->createFileSource();

        //when

        $id = $this->storage->store($fileSource, $fileId, $actualFilepath);

        //then

        $expectedId = $fileId ?: self::ID;
        $this->assertEquals($expectedId, $id);
        $this->assertTrue(file_exists($expectedFilepath));
        $this->assertEquals(self::FILESOURCE, file_get_contents($expectedFilepath));
    }

    public function filepathProvider()
    {
        $filepath = __DIR__.self::STORAGE_RELATIVE_DIR.self::FILEPATH_FOR_ID;
        return array(
            array(null, null, $filepath.'/'.self::ID),
            array(null, 'file-variant.file', $filepath.'/file-variant.file'),
            array(self::DIFFERENT_ID, 'file-variant.file', __DIR__.self::STORAGE_RELATIVE_DIR.self::FILEPATH_FOR_DIFFERENT_ID.'/file-variant.file'),
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

        $this->storage->store($fileSource, self::ID, $invalidFilepath);
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


    /**
     * @test
     */
    public function getSource_fileExists_returnFileSource()
    {
        //given

        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->filepath.'/'.self::ID, self::FILESOURCE);

        //when

        $fileSource = $this->storage->getSource(new FileId(self::ID));

        //then

        $this->assertNotNull($fileSource);
        $this->assertEquals(self::FILESOURCE, $fileSource->content());
    }

    /**
     * @test
     * @expectedException ZineInc\Storage\Server\Storage\FileSourceNotFoundException
     */
    public function getSource_fileDoesntExist_throwEx()
    {
        $this->storage->getSource(new FileId(self::ID));
    }

    protected function tearDown()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->storageDir.'/some');
        $filesystem->remove($this->storageDir.'/another');
        $filesystem->remove($this->storageDir.'/../another');
    }
}

class FilesystemStorageTest_FilepathChoosingStrategy implements FilepathChoosingStrategy
{
    private $filepaths;

    public function __construct($filepaths)
    {
        $this->filepaths = $filepaths;
    }

    public function filepath(FileId $fileId)
    {
        return $this->filepaths[$fileId->id()];
    }
}

class FilesystemStorageTest_IdFactory implements IdFactory
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }


    public function id(FileSource $fileSource)
    {
        return $this->id;
    }
}
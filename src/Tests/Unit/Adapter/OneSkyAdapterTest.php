<?php

namespace Partnermarketing\TranslationBundle\Tests\Unit\Adapter;

use Partnermarketing\TranslationBundle\Adapter\OneSkyAdapter;
use Partnermarketing\TranslationBundle\Tests\Application\AppKernel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Test the OneSkyAdapter service.
 */
class OneSkyAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $kernel;
    protected $container;
    /** @var  \Partnermarketing\TranslationBundle\Adapter\OneSkyAdapter $adapter */
    protected $adapter;
    private $baseTranslationsDir;

    public function setUp()
    {
        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();

        $this->adapter = $this->container->get('partnermarketing_translation.one_sky_adapter');

        $this->baseTranslationsDir = $this->kernel->getRootDir() . '/Resources/base-translations';

        parent::setUp();
    }


    public function tearDown()
    {
        $translationsDirectory = realpath($this->baseTranslationsDir.'/../translations/');
        $filesystem = new Filesystem();
        $filesystem->remove($translationsDirectory);

        $this->kernel->shutdown();
        parent::tearDown();
    }

    public function testCreateClient()
    {
        $oneSkyClient = $this->adapter->getClient();
        $this->assertInstanceOf('OneSky\Api\Client', $oneSkyClient);
    }

    public function testPushBaseTranslations()
    {
        $oneSkyMockClient = $this->getMockBuilder('Onesky\Api\Client')
            ->disableOriginalConstructor()
        ->getMock();

        $methodParams = [
            'project_id' => 111,
            'file' => $this->baseTranslationsDir . '/books.yml',
            'file_format' => 'YML',
            'locale' => 'en_GB'
        ];

        $oneSkyMockClient->expects($this->at(0))
            ->method('__call')
            ->with($this->equalTo('files'), $this->equalTo(['upload', $methodParams]))
            ->willReturn(json_encode(["meta" => ["status" => 201]]));

        // Test second file is uploaded.
        $methodParams['file'] = $this->baseTranslationsDir . '/pages/movies.yml';

        $oneSkyMockClient->expects($this->at(1))
                         ->method('__call')
                         ->with($this->equalTo('files'), $this->equalTo(['upload', $methodParams]))
                         ->willReturn(json_encode(["meta" => ["status" => 201]]));

        $this->adapter->setClient($oneSkyMockClient);
        $this->adapter->pushBaseTranslations();
    }

    public function testGetTranslationFile()
    {
        $oneSkyMockClient = $this->getMockBuilder('Onesky\Api\Client')
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $methodParams = [
            'project_id' => 111,
            'locale' => 'en_GB',
            'source_file_name' => 'movies.yml'
        ];

        $oneSkyMockClient->expects($this->at(0))
                         ->method('__call')
                         ->with($this->equalTo('translations'), $this->equalTo(['export', $methodParams]))
                         ->willReturn('---
page_title: "10 Best Movies"');


        $this->adapter->setClient($oneSkyMockClient);
        $fileContent = $this->adapter->getTranslationFile('en_GB', 'movies.yml');

        $this->assertContains('page_title: "10 Best Movies"',$fileContent);
    }


    public function testGetAllTranslationFiles()
    {

    }

    public function testDumpAllTranslationsIntoYmlFiles(){
        $oneSkyMockClient = $this->getMockBuilder('Onesky\Api\Client')
                                 ->disableOriginalConstructor()
                                 ->getMock();


        $methodParams = [
            'project_id' => 111,
            'locale' => 'en_GB',
            'source_file_name' => 'books.yml'
        ];

        $oneSkyMockClient->expects($this->at(0))
                         ->method('__call')
                         ->with($this->equalTo('translations'), $this->equalTo(['export', $methodParams]))
                         ->willReturn('---
book_1:
    title: Bunnies for Dummies

book_2:
    title: Teddy Bear Stories');

        $methodParams['source_file_name'] = 'movies.yml';
        $oneSkyMockClient->expects($this->at(1))
                         ->method('__call')
                         ->with($this->equalTo('translations'), $this->equalTo(['export', $methodParams]))
                         ->willReturn('---
page_title: "10 Best Movies"');

        $this->adapter->setSupportedLanguages(['en_GB']);
        $this->adapter->setClient($oneSkyMockClient);
        $this->adapter->dumpAllTranslationsToYamlFiles();
    }

    public function testDumpAllTranslationsIntoYmlFilesWithMoreThenOneSupportedLanguage(){
        $oneSkyMockClient = $this->getMockBuilder('Onesky\Api\Client')
                                 ->disableOriginalConstructor()
                                 ->getMock();


        $methodParams = [
            'project_id' => 111,
            'locale' => 'en_GB',
            'source_file_name' => 'books.yml'
        ];

        $oneSkyMockClient->expects($this->at(0))
                         ->method('__call')
                         ->with($this->equalTo('translations'), $this->equalTo(['export', $methodParams]))
                         ->willReturn('---
book_1:
    title: Bunnies for Dummies

book_2:
    title: Teddy Bear Stories');

        $methodParams['source_file_name'] = 'movies.yml';
        $oneSkyMockClient->expects($this->at(1))
                         ->method('__call')
                         ->with($this->equalTo('translations'), $this->equalTo(['export', $methodParams]))
                         ->willReturn('---
page_title: "10 Best Movies"');

        $methodParams['locale'] = 'pt_PT';
        $methodParams['source_file_name'] = 'books.yml';
        $oneSkyMockClient->expects($this->at(2))
                         ->method('__call')
                         ->with($this->equalTo('translations'), $this->equalTo(['export', $methodParams]))
                         ->willReturn('---
book_1:
    title: Coelhos para totos

book_2:
    title: Historias do ursinho');

        $methodParams['source_file_name'] = 'movies.yml';
        $oneSkyMockClient->expects($this->at(3))
                         ->method('__call')
                         ->with($this->equalTo('translations'), $this->equalTo(['export', $methodParams]))
                         ->willReturn('---
page_title: "10 Melhores filmes"');


        $this->adapter->setSupportedLanguages(['en_GB', 'pt_PT']);
        $this->adapter->setClient($oneSkyMockClient);
        $this->adapter->dumpAllTranslationsToYamlFiles();

        // Ensure files were created.
        $this->assertFileExists($this->baseTranslationsDir.'/../translations/books.en_GB.yml');
        $this->assertFileExists($this->baseTranslationsDir.'/../translations/books.pt_PT.yml');
        $this->assertFileExists($this->baseTranslationsDir.'/../translations/pages/movies.en_GB.yml');
        $this->assertFileExists($this->baseTranslationsDir.'/../translations/pages/movies.pt_PT.yml');
    }


    /**
     * Test to ensure we can display what file has invalid format to be parsed.
     *
     * @expectedException \Partnermarketing\TranslationBundle\Exception\YMLParseException
     */
    public function testDumpAllTranslationsIntoYmlFilesInvalidYMLFile(){
        $oneSkyMockClient = $this->getMockBuilder('Onesky\Api\Client')
                                 ->disableOriginalConstructor()
                                 ->getMock();


        $methodParams = [
            'project_id' => 111,
            'locale' => 'en_GB',
            'source_file_name' => 'books.yml'
        ];

        $oneSkyMockClient->expects($this->at(0))
                         ->method('__call')
                         ->with($this->equalTo('translations'), $this->equalTo(['export', $methodParams]))
                         ->willReturn('---

? "true"
  : "Yes"
  ? "false"
  : "No"
');
        $this->adapter->setSupportedLanguages(['en_GB']);
        $this->adapter->setClient($oneSkyMockClient);
        $this->adapter->dumpAllTranslationsToYamlFiles();
    }


    public function testGetBaseTranslationFiles()
    {
        $files = $this->adapter->getBaseTranslationFiles();

        $this->assertCount(2, $files);

        sort($files);
        $this->assertStringEndsWith('Resources/base-translations/books.yml', $files[0]);
        $this->assertStringEndsWith('Resources/base-translations/pages/movies.yml', $files[1]);
    }


    public function testGetPhraseCollectionKeyFromFilename()
    {
        $this->assertEquals('reporting/partner', $this->adapter->getPhraseCollectionKeyFromFilename($this->baseTranslationsDir . '/reporting/partner.yml'));
        $this->assertEquals('leads', $this->adapter->getPhraseCollectionKeyFromFilename($this->baseTranslationsDir . '/leads.yml'));
    }

    public function testGetPhrasesFromFilename()
    {
        $phrases = $this->adapter->getPhrasesFromFilename($this->baseTranslationsDir . '/books.yml');

        $this->assertCount(2, $phrases);
        $this->assertEquals('Bunnies for Dummies', $phrases['book_1.title']['string']);
    }

    public function testGetPhraseCollectionsFromFilenames()
    {
        $files = $this->adapter->getBaseTranslationFiles();
        $phraseCollections = $this->adapter->getPhraseCollectionsFromFilenames($files);

        $this->assertCount(2, $phraseCollections);
        $this->assertEquals('Bunnies for Dummies', $phraseCollections['books']['book_1.title']['string']);
        $this->assertEquals('10 Best Movies', $phraseCollections['pages/movies']['page_title']['string']);
    }

    public function testConvertToSymfonyLanguageTag()
    {
        $languageTag = 'pt-PT';
        $symfonyLanguageTag = $this->adapter->convertToSymfonyLanguageTag($languageTag);
        $this->assertEquals('pt_PT', $symfonyLanguageTag);

        // Ensure it works with simple language tags.
        $languageTag = 'pt';
        $symfonyLanguageTag = $this->adapter->convertToSymfonyLanguageTag($languageTag);
        $this->assertEquals('pt', $symfonyLanguageTag);
    }

    public function testSupportedLanguagesArePassed()
    {
        $supportedLanguages = $this->adapter->getSupportedLanguages();
        $this->assertEquals(['en_GB', 'pt_PT'], $supportedLanguages);
    }
}

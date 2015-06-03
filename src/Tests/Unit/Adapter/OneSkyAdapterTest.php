<?php

namespace Partnermarketing\TranslationBundle\Tests\Unit\Adapter;

use Partnermarketing\TranslationBundle\Adapter\OneSkyAdapter;
use Partnermarketing\TranslationBundle\Tests\Application\AppKernel;

/**
 * Test the OneSkyAdapter service.
 */
class OneSkyAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $kernel;
    protected $container;
    /** @var  \Partnermarketing\TranslationBundle\Adapter\OneSkyAdapter $adapter */
    protected $adapter;

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
            'file_format' => 'YML'
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



    public function testGetBaseTranslationFiles()
    {
        $files = $this->adapter->getBaseTranslationFiles();

        $this->assertCount(2, $files);

        sort($files);
        $this->assertStringEndsWith('Resources/base-translations/books.yml', $files[0]);
        $this->assertStringEndsWith('Resources/base-translations/pages/movies.yml', $files[1]);
    }

    public function testListPhraseCollections()
    {
        $phraseCollections = $this->adapter->listPhraseCollections();

        $this->assertCount(2, $phraseCollections);
        $this->assertContains('books', $phraseCollections);
        $this->assertContains('pages/movies', $phraseCollections);
    }

    public function testIsPhraseCollection()
    {
        $phraseCollections = $this->adapter->listPhraseCollections();

        $this->assertTrue($this->adapter->isPhraseCollection('books'));
        $this->assertTrue($this->adapter->isPhraseCollection('pages/movies'));

        $this->assertFalse($this->adapter->isPhraseCollection('books.yml'));
        $this->assertFalse($this->adapter->isPhraseCollection('pages/books'));
        $this->assertFalse($this->adapter->isPhraseCollection('pages/movies.yml'));
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
}

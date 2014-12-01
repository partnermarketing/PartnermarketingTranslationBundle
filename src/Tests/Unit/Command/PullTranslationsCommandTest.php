<?php

namespace Partnermarketing\TranslationBundle\Tests\Unit\Command;

use Partnermarketing\TranslationBundle\Command\PullTranslationsCommand;
use Partnermarketing\TranslationBundle\Tests\Application\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PullTranslationsCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();

        $application = new Application($this->kernel);
        $application->add(new PullTranslationsCommand());

        $this->command = $application->find('partnermarketing:translations:pull_translations');

        $this->fakeAdapter = $this->getMock('Partnermarketing\TranslationBundle\Adapter\TranslationAdapter');
        $this->kernel->getContainer()->set('partnermarketing_translation.one_sky_adapter', $this->fakeAdapter);

        parent::setUp();
    }

    public function testExecute_pullAll()
    {
        $this->fakeAdapter->expects($this->once())
            ->method('dumpAllPhraseCollectionsToYamlFiles')
            ->with();

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command' => $this->command->getName(),
        ]);

        $display = $commandTester->getDisplay();
        $this->assertContains('Pulled translations for all phrase collections', $display);
    }

    public function testExecute_pullOne()
    {
        $this->fakeAdapter->expects($this->once())
            ->method('dumpPhraseCollectionToYamlFile')
            ->with('tours');

        $this->fakeAdapter->expects($this->never())
            ->method('dumpAllPhraseCollectionsToYamlFiles');

        $this->fakeAdapter->expects($this->once())
            ->method('isPhraseCollection')
            ->with('tours')
            ->will($this->returnValue(true));

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command' => $this->command->getName(),
            'phrase_collection_key' => 'tours',
        ]);

        $display = $commandTester->getDisplay();
        $this->assertContains('Pulled translations for the phrase collection: tours', $display);
    }

    public function testExecute_invalidPhraseCollectionKey()
    {
        $this->fakeAdapter->expects($this->never())
            ->method('dumpPhraseCollectionsToYamlFile');

        $this->fakeAdapter->expects($this->never())
            ->method('dumpAllPhraseCollectionsToYamlFiles');

        $this->fakeAdapter->expects($this->once())
            ->method('isPhraseCollection')
            ->with('yadayada')
            ->will($this->returnValue(false));

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command' => $this->command->getName(),
            'phrase_collection_key' => 'yadayada',
        ]);

        $display = $commandTester->getDisplay();
        $this->assertContains('This is not a valid phrase collection key: yadayada', $display);
    }
}

<?php

namespace Partnermarketing\TranslationBundle\Tests\Unit\Command;

use Partnermarketing\TranslationBundle\Command\GetPhraseCollectionCommand;
use Partnermarketing\TranslationBundle\Tests\Application\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GetPhraseCollectionCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();

        $application = new Application($this->kernel);
        $application->add(new GetPhraseCollectionCommand());

        $this->command = $application->find('partnermarketing:translations:get_phrase_collection');

        $this->fakeAdapter = $this->getMock('Partnermarketing\TranslationBundle\Adapter\TranslationAdapter');
        $this->kernel->getContainer()->set('partnermarketing_translation.one_sky_adapter', $this->fakeAdapter);

        parent::setUp();
    }

    public function testExecute_apiSuccess()
    {
        $this->fakeAdapter->expects($this->once())
            ->method('isPhraseCollection')
            ->with('tours')
            ->will($this->returnValue(true));

        $this->fakeAdapter->expects($this->once())
            ->method('getPhraseCollection')
            ->will($this->returnValue([
                'meta' => ['status' => 200],
                'data' => ['translations' => ['de' => ['edit_link' => '(German)']]]
            ]));

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command' => $this->command->getName(),
            'phrase_collection_key' => 'tours',
        ]);

        $display = $commandTester->getDisplay();
        $this->assertContains('[edit_link] => (German)', $display);
    }

    public function testExecute_invalidPhraseCollectionKey()
    {
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

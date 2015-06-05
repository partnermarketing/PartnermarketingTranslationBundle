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
            ->method('dumpAllTranslationsToYamlFiles')
            ->with();

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command' => $this->command->getName(),
        ]);

        $display = $commandTester->getDisplay();
        $this->assertContains('Pulled translations for all phrase collections', $display);
    }

}

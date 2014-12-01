<?php

namespace Partnermarketing\TranslationBundle\Tests\Unit\Command;

use Partnermarketing\TranslationBundle\Command\PushBaseTranslationsCommand;
use Partnermarketing\TranslationBundle\Tests\Application\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PushBaseTranslationsCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();

        $application = new Application($this->kernel);
        $application->add(new PushBaseTranslationsCommand());

        $this->command = $application->find('partnermarketing:translations:push_base_translations');

        $this->fakeAdapter = $this->getMock('Partnermarketing\TranslationBundle\Adapter\TranslationAdapter');
        $this->kernel->getContainer()->set('partnermarketing_translation.one_sky_adapter', $this->fakeAdapter);

        parent::setUp();
    }

    public function testExecute_apiSuccess()
    {
        $this->fakeAdapter->expects($this->once())
            ->method('pushBaseTranslations')
            ->will($this->returnValue([
                'meta' => ['status' => 201]
            ]));

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command' => $this->command->getName(),
        ]);

        $display = $commandTester->getDisplay();
        $this->assertContains('Pushed base translations', $display);
    }

    public function testExecute_apiError()
    {
        $this->fakeAdapter->expects($this->once())
            ->method('pushBaseTranslations')
            ->will($this->returnValue([
                'meta' => ['status' => 500]
            ]));

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            'command' => $this->command->getName(),
        ]);

        $display = $commandTester->getDisplay();
        $this->assertContains('An error occured', $display);
    }
}

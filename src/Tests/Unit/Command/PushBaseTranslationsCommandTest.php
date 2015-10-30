<?php

namespace Partnermarketing\TranslationBundle\Tests\Unit\Command;

use Partnermarketing\TranslationBundle\Command\PushBaseTranslationsCommand;
use Partnermarketing\TranslationBundle\Tests\Application\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PushBaseTranslationsCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Partnermarketing\TranslationBundle\Tests\Application\AppKernel $kernel
     * @var \Symfony\Component\Console\Command\Command $command push command
     * @var \Partnermarketing\TranslationBundle\Adapter\TranslationAdapter $fakeAdapter translation adapter mock
     */
    protected $kernel,
        $command,
        $fakeAdapter;

    public function setUp()
    {
        $this->kernel = new AppKernel('test', true);
        $this->kernel->boot();

        $pullCommandMock = $this->getMockBuilder('\Partnermarketing\TranslationBundle\Command\PullTranslationsCommand')
            ->setMethods(['execute'])
            ->getMock();
        $pullCommandMock
            ->expects($this->once())
            ->method('execute');

        $application = new Application($this->kernel);
        $application->addCommands([
            new PushBaseTranslationsCommand(),
            $pullCommandMock
        ]);

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

<?php

namespace Partnermarketing\TranslationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushBaseTranslationsCommand extends ContainerAwareCommand
{
    protected $container,
        $output,
        $input;

    protected function configure()
    {
        $this->setName('partnermarketing:translations:push_base_translations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adapter = $this->getContainer()->get('partnermarketing_translation.adapter');

        $result = $adapter->pushBaseTranslations();

        if ($result['meta']['status'] === 201) {
            $output->writeln('Pushed base translations');
        } else {
            $output->writeln('An error occured: ' . print_r($result, true));
        }
    }
}

<?php

namespace Partnermarketing\TranslationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class PullTranslationsCommand extends ContainerAwareCommand
{
    protected $container,
        $output,
        $input;

    protected function configure()
    {
        $this->setName('partnermarketing:translations:pull_translations')
            ->addArgument('phrase_collection_key', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adapter = $this->getContainer()->get('partnermarketing_translation.adapter');

        $adapter->dumpAllTranslationsToYamlFiles();
        $output->writeln('Pulled translations for all phrase collections');
    }
}

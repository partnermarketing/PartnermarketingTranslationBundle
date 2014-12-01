<?php

namespace Partnermarketing\TranslationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class GetPhraseCollectionCommand extends ContainerAwareCommand
{
    protected $container,
        $output,
        $input;

    protected function configure()
    {
        $this->setName('partnermarketing:translations:get_phrase_collection')
            ->addArgument('phrase_collection_key', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adapter = $this->getContainer()->get('partnermarketing_translation.adapter');
        $phraseCollectionKey = $input->getArgument('phrase_collection_key');

        if (!$adapter->isPhraseCollection($phraseCollectionKey)) {
            $output->writeln('This is not a valid phrase collection key: ' . $phraseCollectionKey);

            return;
        }

        $result = $adapter->getPhraseCollection($phraseCollectionKey);

        $output->writeln(print_r($result['data'], true));
    }
}

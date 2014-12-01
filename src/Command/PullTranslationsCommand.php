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
        $phraseCollectionKey = $input->getArgument('phrase_collection_key');

        if ($phraseCollectionKey) {
            if (!$adapter->isPhraseCollection($phraseCollectionKey)) {
                $output->writeln('This is not a valid phrase collection key: ' . $phraseCollectionKey);

                return;
            }

            $adapter->dumpPhraseCollectionToYamlFile($phraseCollectionKey);
            $output->writeln('Pulled translations for the phrase collection: ' . $phraseCollectionKey);
        } else {
            $adapter->dumpAllPhraseCollectionsToYamlFiles();
            $output->writeln('Pulled translations for all phrase collections');
        }
    }
}

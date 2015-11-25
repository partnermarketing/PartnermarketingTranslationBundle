<?php

namespace Partnermarketing\TranslationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PushBaseTranslationsCommand extends ContainerAwareCommand
{
    const OPTION_FORCE = 'force';

    protected $container;
    protected $output;
    protected $input;

    protected function configure()
    {
        $this->setName('partnermarketing:translations:push_base_translations')
            ->addOption(
                self::OPTION_FORCE,
                null,
                InputOption::VALUE_NONE,
                'If set, it will not pull before push.'
            );;
    }

    /**
     * This command push translations.
     * It pull translations before pushing new ones.
     *
     * Note:
     * When `--force` option is set, it will not pull translations before push new ones.
     *
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adapter = $this->getContainer()->get('partnermarketing_translation.adapter');

        if (!$input->getOption(self::OPTION_FORCE)) {
            $pullCommandName = 'partnermarketing:translations:pull_translations';
            $app = $this->getApplication()->find($pullCommandName);
            $input = new ArrayInput([
                'command' => $pullCommandName
            ]);
            $app->run($input, $output);
        }

        $result = $adapter->pushBaseTranslations();

        if ($result['meta']['status'] === 201) {
            $output->writeln('Pushed base translations');
        } else {
            $output->writeln('An error occured: ' . print_r($result, true));
        }
    }
}

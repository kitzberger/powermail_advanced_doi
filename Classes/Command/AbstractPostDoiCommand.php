<?php

namespace Kitzberger\PowermailAdvancedDoi\Command;

use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Domain\Repository\MailRepository;
use Kitzberger\PowermailAdvancedDoi\Service\PostDoiActionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractPostDoiCommand extends Command
{
    protected const DESCRIPTION = 't.b.c.';
    protected const TYPE = null;

    /**
     * @var SymfonyStyle
     */
    protected $io = null;

    /**
     * @var []
     */
    protected $conf = null;

    protected function configure()
    {
        $this->setDescription(static::DESCRIPTION);

        $this->addOption(
            'type',
            't',
            InputOption::VALUE_REQUIRED,
            'Handle only records of this type',
            static::TYPE
        );

        $this->addOption(
            'pids',
            'p',
            InputOption::VALUE_REQUIRED,
            'Handle only records within these pids'
        );
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        if ($output->isVerbose()) {
            $this->io->title($this->getDescription());
        }

        $this->initializeConf($input);

        $postDoiActions = $this->fetchPostDoiActions(
            $this->conf['type'],
            $this->conf['pids']
        );

        $this->handlePostDoiActions($postDoiActions);

        return self::SUCCESS;
    }

    protected function initializeConf(InputInterface $input)
    {
        $this->conf = $input->getArguments();
        $this->conf['type'] = $input->getOption('type');
        $this->conf['pids'] = $input->getOption('pids');
    }

    protected function fetchPostDoiActions(?string $type = null, ?string $pids = null): array
    {
        $postDoiActionService = GeneralUtility::makeInstance(PostDoiActionService::class);
        $postDoiActions = $postDoiActionService->fetchPostDoiActions($type, $pids, true);

        if (empty($postDoiActions)) {
            $this->outputLine('No post DOI actions of type "' . ($type ?? '*') . '" found.');
        } else {
            if ($this->io->isVerbose()) {
                $this->io->table(
                    array_keys($postDoiActions[0]),
                    $postDoiActions
                );
            }
        }

        return $postDoiActions;
    }

    protected function handlePostDoiActions(array $postDoiActions)
    {
        $mailRepository = GeneralUtility::makeInstance(MailRepository::class);
        foreach ($postDoiActions as $postDoiAction) {
            if ($mail = $mailRepository->findByUid($postDoiAction['mail'])) {
                $this->handlePostDoiAction($postDoiAction, $mail);
            } else {
                $this->io->warning('No mail record found: ' . $postDoiAction['mail']);
            }
        }
    }

    protected function handlePostDoiAction(array $postDoiAction, Mail $mail)
    {
        $this->io->writeln('To be implemented!');
    }

    protected function updatePostDoiAction(int $uid, int $doneAt, string $notice)
    {
        $postDoiActionService = GeneralUtility::makeInstance(PostDoiActionService::class);
        $postDoiActionService->updatePostDoiAction($uid, $doneAt, $notice);
    }

    /**
     * Outputs specified text to the console window and appends a line break
     *
     * @param  string $string Text to output
     * @param  array  $arguments Optional arguments to use for sprintf
     * @return void
     */
    protected function outputLine(string $string, $arguments = [])
    {
        if ($this->io) {
            $this->io->text(sprintf($string, ...$arguments));
        }
    }
}

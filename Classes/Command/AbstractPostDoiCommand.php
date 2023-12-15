<?php

namespace Kitzberger\PowermailAdvancedDoi\Command;

use Doctrine\DBAL\Connection;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Domain\Repository\MailRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->conf = $input->getArguments();
        $this->conf['type'] = $input->getOption('type');
        $this->conf['pids'] = $input->getOption('pids');

        if ($output->isVerbose()) {
            $this->io->title($this->getDescription());
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_powermailadvanceddoi_postdoiaction');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $constraints = [];
        $constraints[] = $queryBuilder->expr()->eq('done_at', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT));

        if ($this->conf['type'] ?? false) {
            $constraints[] = $queryBuilder->expr()->eq('type', $queryBuilder->createNamedParameter($this->conf['type'], \PDO::PARAM_STR));
        }

        if ($this->conf['pids'] ?? false) {
            $pids = GeneralUtility::intExplode(',', $this->conf['pids'], true);
            $constraints[] = $queryBuilder->expr()->in('pid', $queryBuilder->createNamedParameter($pids, Connection::PARAM_INT_ARRAY));
        }

        $postDoiActions = $queryBuilder
            ->select('*')
            ->from('tx_powermailadvanceddoi_postdoiaction')
            ->where($queryBuilder->expr()->andX(...$constraints))
            ->executeQuery()->fetchAll();

        $mailRepository = GeneralUtility::makeInstance(MailRepository::class);

        if (!empty($postDoiActions)) {
            if ($output->isVerbose()) {
                $this->io->table(
                    array_keys($postDoiActions[0]),
                    $postDoiActions
                );
            }

            foreach ($postDoiActions as $postDoiAction) {
                if ($mail = $mailRepository->findByUid($postDoiAction['mail'])) {
                    $this->handlePostDoiAction($postDoiAction, $mail);
                } else {
                    $this->io->warning('No mail record found: ' . $postDoiAction['mail']);
                }
            }
        }

        return self::SUCCESS;
    }

    protected function handlePostDoiAction(array $postDoiAction, Mail $mail)
    {
        $this->io->writeln('To be implemented!');
    }

    protected function updatePostDoiAction(int $uid, int $doneAt, string $notice)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_powermailadvanceddoi_postdoiaction');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder
            ->update('tx_powermailadvanceddoi_postdoiaction')
            ->set('done_at', $doneAt)
            ->set('notice', $notice)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)))
            ->execute();
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
            $this->io->text(sprintf($string, $arguments));
        }
    }
}

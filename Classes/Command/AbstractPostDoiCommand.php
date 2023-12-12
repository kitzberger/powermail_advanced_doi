<?php

namespace Kitzberger\PowermailAdvancedDoi\Command;

use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Domain\Repository\MailRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractPostDoiCommand extends Command
{
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
        $this->setDescription('t.b.d.');
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

        if ($output->isVerbose()) {
            $this->io->title($this->getDescription());
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_powermailadvanceddoi_postdoiaction');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $postDoiActions = $queryBuilder
            ->select('*')
            ->from('tx_powermailadvanceddoi_postdoiaction')
            ->where($queryBuilder->expr()->eq('done_at', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)))
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

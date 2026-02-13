<?php

declare(strict_types=1);

namespace Kitzberger\PowermailAdvancedDoi\Service;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service for fetching and managing post DOI actions
 */
class PostDoiActionService
{
    public const TABLE_POST_DOI_ACTION = 'tx_powermailadvanceddoi_postdoiaction';

    /**
     * Fetch post DOI actions based on type and pids
     *
     * @param string|null $type Filter by action type
     * @param string|null $pids Comma-separated list of page IDs to filter by
     * @param bool $onlyPending If true, only fetch actions that haven't been processed yet
     * @return array
     */
    public function fetchPostDoiActions(
        string $type,
        ?string $pids = null,
        bool $onlyPending = true
    ): array {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_POST_DOI_ACTION);

        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $constraints = [];

        // Filter by type
        if ($type !== null) {
            $constraints[] = $queryBuilder->expr()->eq(
                'postdoiaction.type',
                $queryBuilder->createNamedParameter($type, Connection::PARAM_STR)
            );
        }

        // Filter by PIDs
        if (!empty($pids)) {
            $pidArray = GeneralUtility::intExplode(',', $pids, true);
            $constraints[] = $queryBuilder->expr()->in(
                'postdoiaction.pid',
                $queryBuilder->createNamedParameter($pidArray, Connection::PARAM_INT_ARRAY)
            );
        }

        // Only fetch pending actions if requested
        if ($onlyPending) {
            $constraints[] = $queryBuilder->expr()->eq(
                'postdoiaction.done_at',
                $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
            );
        }

        $result = $queryBuilder
            ->select('postdoiaction.*')
            ->from(self::TABLE_POST_DOI_ACTION, 'postdoiaction')
            ->join(
                'postdoiaction',
                'tx_powermail_domain_model_mail',
                'mail',
                $queryBuilder->expr()->eq(
                    'mail.uid',
                    $queryBuilder->quoteIdentifier('postdoiaction.mail')
                )
            )
            ->where($queryBuilder->expr()->and(...$constraints))
            ->orderBy('postdoiaction.tstamp', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return $result;
    }

    /**
     * Update a post DOI action to mark it as processed
     *
     * @param int $uid The UID of the post DOI action
     * @param int $doneAt Timestamp when the action was completed
     * @param string $notice Optional notice/message about the action
     * @return void
     */
    public function updatePostDoiAction(int $uid, int $doneAt, string $notice = ''): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_POST_DOI_ACTION);

        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $queryBuilder
            ->update(self::TABLE_POST_DOI_ACTION)
            ->set('done_at', $doneAt)
            ->set('notice', $notice)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }

    /**
     * Get mail UIDs from post DOI actions
     *
     * @param array $postDoiActions Array of post DOI action records
     * @return array Array of mail UIDs
     */
    public function getMailUidsFromActions(array $postDoiActions): array
    {
        return array_map(
            fn($action) => (int)$action['mail'],
            $postDoiActions
        );
    }
}

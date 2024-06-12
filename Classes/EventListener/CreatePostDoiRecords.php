<?php

namespace Kitzberger\PowermailAdvancedDoi\EventListener;

use In2code\Powermail\Events\FormControllerOptinConfirmActionBeforeRenderViewEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class CreatePostDoiRecords implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const FIELD_TYPE_CHECK = 'check_post_doi_actions';
    private const FIELD_TYPE_HIDDEN = 'hidden_post_doi_action';

    /**
     * Executed after DOI confirmation.
     */
    public function __invoke(FormControllerOptinConfirmActionBeforeRenderViewEvent $event): void
    {
        $mail = $event->getMail();
        $controller = $event->getFormController();

        $postDoiActions = [];
        foreach ($mail->getAnswers() as $answer) {
            if ($answer->getField()) {
                switch ($answer->getField()->getType()) {
                    case self::FIELD_TYPE_CHECK:
                        $postDoiActions = array_merge($postDoiActions, $answer->getValue());
                        break;
                    case self::FIELD_TYPE_HIDDEN:
                        $postDoiActions[] = $answer->getValue();
                        break;
                }
            }
        }

        $this->logger->debug('Handling post DOI actions: ' . print_r($postDoiActions, true));

        $settings = $controller->getSettings()['optin'];

        $fromMail = $settings['fromMail'] ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
        $fromName = $settings['fromName'] ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];
        $adminMail = $settings['adminMail'] ?? false;

        $table = 'tx_powermailadvanceddoi_postdoiaction';
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

        $counter = 0;
        foreach ($postDoiActions as $postDoiAction) {
            // if (isset($settings['postConfirmationActions'][$postDoiAction])) {
                $queryBuilder
                    ->insert($table)
                    ->values([
                        'pid' => $mail->getPid(),
                        'mail' => $mail->getUid(),
                        'type' => $postDoiAction,
                        'crdate' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
                        'tstamp' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
                    ])
                    ->executeStatement();
                if ($queryBuilder->getConnection()->lastInsertId()) {
                    $counter++;
                }
            // } else {
            //     if ($adminMail) {
            //         $this->sendMail(
            //             $fromMail,
            //             $fromName,
            //             $adminMail,
            //             'EXT:powermail_doi :: Post-Confirmation missing!',
            //             'This post-confirmation action "' . $postDoiAction . '" has not been implemented yet!'
            //         );
            //     }
            // }
        }

        if ($counter) {
            $table = 'tx_powermail_domain_model_mail';
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
            $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $queryBuilder
                ->update($table)
                ->set('tx_powermailadvanceddoi_postdoiactions', $counter)
                ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($mail->getUid(), Connection::PARAM_INT)))
                ->executeStatement();
        }
    }

    private function sendMail(string $fromMail, string $fromName, string $toMail, string $subject, string $body)
    {
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail
           ->from(new Address($fromMail, $fromName))
           ->to(new Address($toMail, $toMail))
           ->subject($subject)
           ->text($body)
           ->html($body)
           ->send();
    }
}

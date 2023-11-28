<?php

namespace Kitzberger\PowermailAdvancedDoi\Controller;

use In2code\Powermail\Controller\FormController;
use In2code\Powermail\Domain\Model\Mail;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DoiController
{
    private const FIELD_TYPE = 'check_post_doi_actions';

    /**
     * Executed before DOI is being sent.
     *
     * If any of the DOI checkboxes has been checked by the user then we sent a
     * DOI mail no matter what the flexform/typoscript says.
     *
     * @param  Mail           $mail
     * @param  string         $hash
     * @param  FormController $controller
     */
    public function createActionBeforeRenderView(Mail $mail, string $hash, FormController $controller)
    {
        $doi = false;
        foreach ($mail->getAnswers() as $answer) {
            if ($answer->getField()->getType() === self::FIELD_TYPE) {
                $doi = true;
            }
        }

        if ($doi) {
            // Make sure DOI mail is sent no matter what!

            $settings = $controller->getSettings();
            $settings = array_replace_recursive($settings, ['main' => ['optin' => '1']]);
            $controller->setSettings($settings);
        }
    }

    /**
     * Executed after DOI confirmation.
     *
     * @param  Mail           $mail
     * @param  string         $hash
     * @param  FormController $controller
     */
    public function optinConfirmActionAfterPersist(Mail $mail, string $hash, FormController $controller)
    {
        $postDoiActions = [];
        foreach ($mail->getAnswers() as $answer) {
            if ($answer->getField()->getType() === self::FIELD_TYPE) {
                $postDoiActions = array_merge($answer->getValue());
            }
        }

        $settings = $controller->getSettings()['optin'];

        $fromMail = $settings['fromMail'] ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'];
        $fromName = $settings['fromName'] ?? $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'];
        $adminMail = $settings['adminMail'] ?? false;

        foreach ($postDoiActions as $postDoiAction) {
            if (isset($settings['postConfirmationActions'][$postDoiAction])) {
                // todo: implementation
            } else {
                if ($adminMail) {
                    $this->sendMail(
                        $fromMail,
                        $fromName,
                        $adminMail,
                        'EXT:powermail_doi :: Post-Confirmation "' . $postDoiAction . '" missing!',
                        'This post-confirmation action has not been implemented yet!'
                    );
                }
            }
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

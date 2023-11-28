<?php

namespace Kitzberger\PowermailAdvancedDoi\Controller;

use In2code\Powermail\Controller\FormController;
use In2code\Powermail\Domain\Model\Mail;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DoiController
{
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
            if ($answer->getField()->getType() === 'check_post_doi_actions') {
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
            if ($answer->getField()->getType() === 'check_post_doi_actions') {
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
        $email = GeneralUtility::makeInstance(FluidEmail::class);
        $email
            ->from(new Address($fromMail, $fromName))
            ->to($toMail)
            ->subject($subject)
            ->format(FluidEmail::FORMAT_BOTH)
            ->assign('introduction', $subject)
            ->assign('content', $body);
        GeneralUtility::makeInstance(Mailer::class)->send($email);
    }
}

<?php

namespace Kitzberger\PowermailAdvancedDoi\EventListener;

use In2code\Powermail\Events\FormControllerCreateActionBeforeRenderViewEvent;

final class EnforceOptIn
{
    private const FIELD_TYPES = [
        'check_post_doi_actions',
        'hidden_post_doi_action',
    ];

    /**
     * Executed before DOI is being sent.
     *
     * If any of the Post-DOI fields has been checked by the user then we sent a
     * DOI mail no matter what the flexform/typoscript says.
     */
    public function __invoke(FormControllerCreateActionBeforeRenderViewEvent $event): void
    {
        $mail = $event->getMail();
        $controller = $event->getFormController();

        $doi = false;
        foreach ($mail->getAnswers() as $answer) {
            if (in_array($answer->getField()->getType(), self::FIELD_TYPES)) {
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
}

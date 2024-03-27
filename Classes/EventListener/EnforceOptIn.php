<?php

namespace Kitzberger\PowermailAdvancedDoi\EventListener;

use In2code\Powermail\Events\FormControllerCreateActionBeforeRenderViewEvent;

final class EnforceOptIn
{
    private const FIELD_TYPE = 'check_post_doi_actions';

    /**
     * Executed before DOI is being sent.
     *
     * If any of the DOI checkboxes has been checked by the user then we sent a
     * DOI mail no matter what the flexform/typoscript says.
     */
    public function __invoke(FormControllerCreateActionBeforeRenderViewEvent $event): void
    {
        $mail = $event->getMail();
        $controller = $event->getFormController();

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
}

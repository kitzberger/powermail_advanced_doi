<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

defined('TYPO3') || die('Access denied.');

call_user_func(
    function () {
        ExtensionManagementUtility::addPageTSConfig(
            '@import \'EXT:powermail_advanced_doi/Configuration/TsConfig/page.tsconfig\''
        );

        /** @var Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->connect(
            'In2code\Powermail\Controller\FormController',
            'createActionBeforeRenderView',
            'Kitzberger\PowermailAdvancedDoi\Controller\DoiController',
            'createActionBeforeRenderView',
            FALSE
        );
        $signalSlotDispatcher->connect(
            'In2code\Powermail\Controller\FormController',
            'optinConfirmActionAfterPersist',
            'Kitzberger\PowermailAdvancedDoi\Controller\DoiController',
            'optinConfirmActionAfterPersist',
            FALSE
        );
    }
);

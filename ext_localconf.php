<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

call_user_func(
    function () {
        ExtensionManagementUtility::addPageTSConfig(
            '@import \'EXT:powermail_advanced_doi/Configuration/TsConfig/page.tsconfig\''
        );
    }
);

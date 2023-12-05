<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$GLOBALS['TCA']['tx_powermail_domain_model_mail']['columns']['tx_powermailadvanceddoi_postdoiactions'] = [
    'exclude' => false,
    'label' => 'LLL:EXT:powermail_advanced_doi/Resources/Private/Language/locallang_db.xlf:tx_powermail_domain_model_mail.tx_powermailadvanceddoi_postdoiactions',
    'config' => [
        'type' => 'inline',
        'foreign_table' => 'tx_powermailadvanceddoi_postdoiaction',
        'foreign_field' => 'mail',
        'maxitems' => 1000,
        'appearance' => [
            'collapse' => 1,
            'levelLinksPosition' => 'top',
            'showNewRecordLink' => 0,
            'showSynchronizationLink' => 0,
            'showPossibleLocalizationRecords' => 0,
            'showAllLocalizationLink' => 0,
        ],
    ],
];

ExtensionManagementUtility::addToAllTCAtypes(
    'tx_powermail_domain_model_mail',
    'tx_powermailadvanceddoi_postdoiactions',
    '',
    'after:crdate'
);

<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:powermail_advanced_doi/Resources/Private/Language/locallang_db.xlf:tx_powermailadvanceddoi_postdoiaction',
        'label' => 'type',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => false,
        'default_sortby' => 'ORDER BY type ASC',
        'delete' => 'deleted',
        'enablecolumns' => [
            // 'disabled' => 'hidden',
            // 'starttime' => 'starttime',
            // 'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:powermail_advanced_doi/Resources/Public/Icons/Extension.png',
    ],
    'types' => [
        '1' => [
            'showitem' => 'type, crdate, done_at, notice', //, hidden, starttime, endtime',
        ],
    ],
    'columns' => [
        // 'hidden' => [
        //     'exclude' => true,
        //     'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
        //     'config' => [
        //         'type' => 'check',
        //     ],
        // ],
        // 'starttime' => [
        //     'l10n_mode' => 'exclude',
        //     'exclude' => true,
        //     'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
        //     'config' => [
        //         'type' => 'input',
        //         'renderType' => 'inputDateTime',
        //         'eval' => 'datetime,int',
        //         'default' => 0,
        //     ],
        // ],
        // 'endtime' => [
        //     'l10n_mode' => 'exclude',
        //     'exclude' => true,
        //     'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
        //     'config' => [
        //         'type' => 'input',
        //         'renderType' => 'inputDateTime',
        //         'eval' => 'datetime,int',
        //         'default' => 0,
        //     ],
        // ],
        'mail' => [
            'exclude' => false,
            'label' => 'LLL:EXT:powermail_advanced_doi/Resources/Private/Language/locallang_db.xlf:tx_powermailadvanceddoi_postdoiaction.mail',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_powermail_domain_model_mail',
                'size' => 1,
                'maxitems' => 1,
                'multiple' => 0,
                'default' => 0,
            ],
        ],
        'type' => [
            'exclude' => false,
            'label' => 'LLL:EXT:powermail_advanced_doi/Resources/Private/Language/locallang_db.xlf:tx_powermailadvanceddoi_postdoiaction.type',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
            ],
        ],
        'notice' => [
            'exclude' => false,
            'label' => 'LLL:EXT:powermail_advanced_doi/Resources/Private/Language/locallang_db.xlf:tx_powermailadvanceddoi_postdoiaction.notice',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
            ],
        ],
        'crdate' => [
            'exclude' => false,
            'label' => 'LLL:EXT:powermail_advanced_doi/Resources/Private/Language/locallang_db.xlf:tx_powermailadvanceddoi_postdoiaction.crdate',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'done_at' => [
            'exclude' => false,
            'label' => 'LLL:EXT:powermail_advanced_doi/Resources/Private/Language/locallang_db.xlf:tx_powermailadvanceddoi_postdoiaction.done_at',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
    ],
];

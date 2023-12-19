<?php

defined('TYPO3') || die('Access denied.');

call_user_func(
    function () {
        /**
         * Garbage Collector
         */
        $tgct = 'TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask';
        $table = 'tx_powermailadvanceddoi_postdoiaction';
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][$tgct]['options']['tables'][$table] = [
            'dateField' => 'tstamp',
            'expirePeriod' => 30
        ];
    }
);

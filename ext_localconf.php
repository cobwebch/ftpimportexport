<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Register Scheduler task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Cobweb\\Ftpimportexport\\Task\\ImportExport'] = array(
	'extension' => $_EXTKEY,
	'title' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang.xlf:scheduler_task_title',
	'description' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang.xlf:ftpimportexport_configuration',
	'additionalFields'	=> 'Cobweb\\Ftpimportexport\\Task\\ImportExportAdditionalFields'
);

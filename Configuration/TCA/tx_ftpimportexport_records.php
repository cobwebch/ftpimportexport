<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

return array(
	'ctrl' => array(
		'title' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records',
		'label' => 'title',
		'type' => 'transfer_type',
		'requestUpdate' => 'post_processing',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'dividers2tabs' => TRUE,
		'typeicon_column' => 'transfer_type',
		'typeicon_classes' => array(
			'default' => 'extensions-ftpimportexport-default',
			'import' => 'extensions-ftpimportexport-import',
			'export' => 'extensions-ftpimportexport-export'
		),
	),
	'interface' => array(
		'showRecordFieldList' => 'hidden,transfer_type,title,local_path,remote_path,pattern,ftp_host,ftp_user,ftp_password'
	),
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.disable',
			'config'  => array(
				'type'    => 'check',
				'default' => '0'
			)
		),
		'transfer_type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.transfer_type',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.transfer_type.import', 'import'),
					array('LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.transfer_type.export', 'export'),
				),
			)
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.title',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '100'
			)
		),
		'source_path' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.source_path',
			'config' => array(
				'type' => 'input',
				'size' => '50',
				'eval' => 'required,trim',
			)
		),
		'target_path' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.target_path',
			'config' => array(
				'type' => 'input',
				'size' => '50',
				'eval' => 'required,trim',
			)
		),
		'pattern' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.pattern',
			'config' => array(
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
			)
		),
		'ftp_host' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.ftp_host',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'ftp_user' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.ftp_user',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'ftp_password' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.ftp_password',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,password',
			)
		),
		'post_processing' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.post_processing',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.post_processing.nothing', ''),
					array('LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.post_processing.move', 'move'),
					array('LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.post_processing.delete', 'delete'),
				),
			)
		),
		'post_processing_path' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:post_processing:=:move',
			'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.post_processing_path',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '100'
			)
		),
	),
	'types' => array(
		'0' => array(
			'showitem' => '
				hidden, transfer_type, title, source_path;LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.source_path_import, target_path;LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.target_path_import, pattern, ftp_host, ftp_user, ftp_password,
				--div--;LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.tab.post_processing, post_processing, post_processing_path
			'
		),
		'export' => array(
			'showitem' => '
				hidden, transfer_type, title, source_path;LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.source_path_export, target_path;LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.target_path_export, pattern, ftp_host, ftp_user, ftp_password,
				--div--;LLL:EXT:ftpimportexport/Resources/Private/Language/locallang_db.xlf:tx_ftpimportexport_records.tab.post_processing, post_processing, post_processing_path
			'
		)
	)
);

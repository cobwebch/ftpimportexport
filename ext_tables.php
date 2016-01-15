<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

// Add record icons to sprite
$extensionRelativePath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY);
$icons = array(
	'default' => $extensionRelativePath . 'Resources/Public/Images/tx_ftpimportexport_records.png',
	'import' => $extensionRelativePath . 'Resources/Public/Images/tx_ftpimportexport_records_import.png',
	'export' => $extensionRelativePath . 'Resources/Public/Images/tx_ftpimportexport_records_export.png'
);
\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons($icons, $_EXTKEY);

// Add context sensitive help (csh) for the tx_ftpimportexport_records table
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_ftpimportexport_records',
	'EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_csh.xlf'
);

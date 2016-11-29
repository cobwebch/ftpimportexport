<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

// Add record icons to sprite
/** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
        'tx_ftpimportexport-default',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:ftpimportexport/Resources/Public/Images/DefaultIcon.svg'
        ]
);
$iconRegistry->registerIcon(
        'tx_ftpimportexport-export',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:ftpimportexport/Resources/Public/Images/ExportIcon.svg'
        ]
);
$iconRegistry->registerIcon(
        'tx_ftpimportexport-import',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:ftpimportexport/Resources/Public/Images/ImportIcon.svg'
        ]
);

// Add context sensitive help (csh) for the tx_ftpimportexport_records table
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_ftpimportexport_records',
	'EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_csh.xlf'
);

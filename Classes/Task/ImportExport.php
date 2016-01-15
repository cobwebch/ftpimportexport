<?php
namespace Cobweb\Ftpimportexport\Task;

/***************************************************************
*  Copyright notice
*
*  (c) 2013-2016 Francois Suter <typo3@cobweb.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

use Cobweb\Ftpimportexport\Domain\Repository\ImportConfigurationRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Scheduler tasks to run defined FTP import/export configurations at regular intervals.
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package	TYPO3
 * @subpackage tx_ftpimportexport
 */
class ImportExport extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	public $configuration;

	/**
	 * Main method called by the Scheduler
	 *
	 * @return boolean Returns TRUE on successful execution, FALSE on error
	 */
	public function execute() {
		// Get the list of configurations to run
		/** @var ImportConfigurationRepository $configurationRepository */
		$configurationRepository = GeneralUtility::makeInstance('Cobweb\\Ftpimportexport\\Domain\\Repository\\ImportConfigurationRepository');
		if ($this->configuration == 'all') {
			$configurations = $configurationRepository->findAll();
		} else {
			$singleConfiguration = $configurationRepository->findByUid($this->configuration);
			if (empty($singleConfiguration)) {
				$configurations = array();
			} else {
				$configurations = array(
					$singleConfiguration
				);
			}
		}
		$result = TRUE;
		// Loop on all selected configurations and run them
		if (count($configurations) > 0) {
			/** @var \Cobweb\Ftpimportexport\Controller\ImportExport $importExportController */
			$importExportController = GeneralUtility::makeInstance('Cobweb\\Ftpimportexport\\Controller\\ImportExport');
			foreach ($configurations as $aConfiguration) {
				$result &= $importExportController->run($aConfiguration);
			}
		}
		return $result;
	}

	/**
	 * This method returns the synchronized table and index as additional information
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation() {
		if ($this->configuration == 'all') {
			$info = $this->getLanguageObject()->sL('LLL:EXT:ftpimportexport/Resources/Private/Language/locallang.xlf:all_configurations');
		} else {
			/** @var ImportConfigurationRepository $configurationRepository */
			$configurationRepository = GeneralUtility::makeInstance('Cobweb\\Ftpimportexport\\Domain\\Repository\\ImportConfigurationRepository');
			$configuration = $configurationRepository->findByUid($this->configuration);
			$info = sprintf(
				$this->getLanguageObject()->sL('LLL:EXT:ftpimportexport/Resources/Private/Language/locallang.xlf:selected_configuration'),
				$configuration['title'],
				$configuration['uid']
			);
		}
		return $info;
	}

	/**
	 * Wrapper around the global LANG object.
	 *
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	protected function getLanguageObject() {
		return $GLOBALS['LANG'];
	}
}

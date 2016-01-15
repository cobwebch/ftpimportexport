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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract class which defines a simple file driver (this is much simpler than a FAL driver).
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package	TYPO3
 * @subpackage tx_ftpimportexport
 */
class ImportExportAdditionalFields implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface {
	/**
	 * Gets additional fields to render in the form to add/edit a task
	 *
	 * @param array $taskInfo Values of the fields from the add/edit task form
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task The task object being edited. Null when adding a task!
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
	 * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
	 */
	public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule) {
		/** @var \Cobweb\Ftpimportexport\Domain\Repository\ImportConfigurationRepository $configurationRepository */
		$configurationRepository = GeneralUtility::makeInstance('Cobweb\\Ftpimportexport\\Domain\\Repository\\ImportConfigurationRepository');
		$configurations = $configurationRepository->findAll();

		// Initialize extra field value
		if (empty($taskInfo['ftpimportexport_configuration'])) {
			if ($schedulerModule->CMD == 'add') {
				$taskInfo['ftpimportexport_configuration'] = 'all';
			} elseif ($schedulerModule->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['ftpimportexport_configuration'] = $task->configuration;
			}
		}

		// Write the code for the field
		$fieldID = 'task_ftpimportexport_configuration';
		$fieldCode  = '<select name="tx_scheduler[ftpimportexport_configuration]" id="' . $fieldID . '">';
		// Default options is "all" configurations
		$selected = '';
		if ($taskInfo['ftpimportexport_configuration'] == 'all') {
			$selected = ' selected="selected"';
		}
		$fieldCode .= '<option value="all"' . $selected . '>' . $this->getLanguageObject()->sL('LLL:EXT:ftpimportexport/Resources/Private/Language/locallang.xlf:all') . '</option>';
		// Loop on all configurations to populate the selector
		foreach ($configurations as $configurationRecord) {
			$selected = '';
			if ($taskInfo['ftpimportexport_configuration'] == $configurationRecord['uid']) {
				$selected = ' selected="selected"';
			}
			$fieldCode .= '<option value="' . $configurationRecord['uid'] . '"' . $selected . '>' . $configurationRecord['title'] . '</option>';
		}
		$fieldCode .= '</select>';
		$additionalFields = array();
		$additionalFields[$fieldID] = array(
			'code'     => $fieldCode,
			'label'    => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang.xlf:field.ftpimportexport_configuration',
			'cshKey'   => '_MOD_user_txexternalimportM1',
			'cshLabel' => $fieldID
		);

		return $additionalFields;
	}

	/**
	 * Validates the additional fields' values
	 *
	 * @param array $submittedData An array containing the data submitted by the add/edit task form
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
	 * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule) {
		// Nothing to validate, since all selectable values are valid
		return TRUE;
	}

	/**
	 * Takes care of saving the additional fields' values in the task's object
	 *
	 * @param array $submittedData An array containing the data submitted by the add/edit task form
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task Reference to the scheduler backend module
	 * @return void
	 */
	public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
		$task->configuration = $submittedData['ftpimportexport_configuration'];
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

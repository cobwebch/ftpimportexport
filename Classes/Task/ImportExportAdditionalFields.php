<?php
namespace Cobweb\Ftpimportexport\Task;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Cobweb\Ftpimportexport\Domain\Repository\ImportConfigurationRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Additional fields provider for the Scheduler task.
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_ftpimportexport
 */
class ImportExportAdditionalFields implements AdditionalFieldProviderInterface
{
    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param array $taskInfo Values of the fields from the add/edit task form
     * @param AbstractTask $task The task object being edited. Null when adding a task!
     * @param SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        /** @var ImportConfigurationRepository $configurationRepository */
        $configurationRepository = GeneralUtility::makeInstance(ImportConfigurationRepository::class);
        $configurations = $configurationRepository->findAll();

        // Initialize extra field value
        if (empty($taskInfo['ftpimportexport_configuration'])) {
            if ($schedulerModule->CMD === 'add') {
                $taskInfo['ftpimportexport_configuration'] = 'all';
            } elseif ($schedulerModule->CMD === 'edit') {
                // In case of edit, set to internal value if no data was submitted already
                $taskInfo['ftpimportexport_configuration'] = $task->configuration;
            }
        }

        // Write the code for the field
        $fieldID = 'task_ftpimportexport_configuration';
        $fieldCode = '<select name="tx_scheduler[ftpimportexport_configuration]" id="' . $fieldID . '">';
        // Default options is "all" configurations
        $selected = '';
        if ($taskInfo['ftpimportexport_configuration'] === 'all') {
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
                'code' => $fieldCode,
                'label' => 'LLL:EXT:ftpimportexport/Resources/Private/Language/locallang.xlf:field.ftpimportexport_configuration',
                'cshKey' => '_MOD_user_txexternalimportM1',
                'cshLabel' => $fieldID
        );

        return $additionalFields;
    }

    /**
     * Validates the additional fields' values
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        // Nothing to validate, since all selectable values are valid
        return true;
    }

    /**
     * Takes care of saving the additional fields' values in the task's object
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param AbstractTask $task Reference to the scheduler backend module
     * @return void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->configuration = $submittedData['ftpimportexport_configuration'];
    }

    /**
     * Wrapper around the global LANG object.
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageObject()
    {
        return $GLOBALS['LANG'];
    }
}

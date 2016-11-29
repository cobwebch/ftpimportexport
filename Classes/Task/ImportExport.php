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
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Scheduler tasks to run defined FTP import/export configurations at regular intervals.
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package    TYPO3
 * @subpackage tx_ftpimportexport
 */
class ImportExport extends AbstractTask
{
    public $configuration;

    /**
     * Main method called by the Scheduler
     *
     * @return boolean Returns TRUE on successful execution, FALSE on error
     */
    public function execute()
    {
        // Get the list of configurations to run
        /** @var ImportConfigurationRepository $configurationRepository */
        $configurationRepository = GeneralUtility::makeInstance(ImportConfigurationRepository::class);
        if ($this->configuration === 'all') {
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
        $result = true;
        // Loop on all selected configurations and run them
        if (count($configurations) > 0) {
            /** @var \Cobweb\Ftpimportexport\Controller\ImportExport $importExportController */
            $importExportController = GeneralUtility::makeInstance(\Cobweb\Ftpimportexport\Controller\ImportExport::class);
            foreach ($configurations as $aConfiguration) {
                $result &= $importExportController->run($aConfiguration);
            }
        }
        return $result;
    }

    /**
     * This method returns the synchronized table and index as additional information
     *
     * @return    string    Information to display
     */
    public function getAdditionalInformation()
    {
        if ($this->configuration === 'all') {
            $info = $this->getLanguageObject()->sL('LLL:EXT:ftpimportexport/Resources/Private/Language/locallang.xlf:all_configurations');
        } else {
            /** @var ImportConfigurationRepository $configurationRepository */
            $configurationRepository = GeneralUtility::makeInstance(ImportConfigurationRepository::class);
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
    protected function getLanguageObject()
    {
        return $GLOBALS['LANG'];
    }
}

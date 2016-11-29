<?php
namespace Cobweb\Ftpimportexport\Domain\Repository;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Repository (not Extbase-style) for fetching (only) DB records of FTP configurations.
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package    TYPO3
 * @subpackage tx_ftpimportexport
 */
class ImportConfigurationRepository
{
    /**
     * Returns all available FTP configurations.
     *
     * @return array|NULL
     */
    public function findAll()
    {
        try {
            return $this->getDatabaseConnection()->exec_SELECTgetRows(
                    '*',
                    'tx_ftpimportexport_records',
                    // Currently this is only ever called in a BE context
                    '1 = 1' . BackendUtility::deleteClause('tx_ftpimportexport_records') . BackendUtility::BEenableFields('tx_ftpimportexport_records')
            );
        } catch (\Exception $e) {
            return array();
        }
    }

    /**
     * Fetches a given FTP configuration.
     *
     * @param integer $id Primary key of the FTP configuration to retrieve
     * @return array
     */
    public function findByUid($id)
    {
        $recordId = (int)$id;
        return $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                '*',
                'tx_ftpimportexport_records',
                'uid = ' . $recordId
        );
    }

    /**
     * Wrapper for IDE type hinting
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}

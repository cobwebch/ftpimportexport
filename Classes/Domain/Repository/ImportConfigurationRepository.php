<?php
namespace Cobweb\Ftpimportexport\Domain\Repository;

/***************************************************************
*  Copyright notice
*
*  (c) 2013 Francois Suter <typo3@cobweb.ch>
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

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Repository (not Extbase-style) for fetching (only) DB records of FTP configurations.
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package	TYPO3
 * @subpackage tx_ftpimportexport
 */

class ImportConfigurationRepository {
	/**
	 * Returns all available FTP configurations.
	 *
	 * @return array|NULL
	 */
	public function findAll() {
		return $this->getDatabaseConnection()->exec_SELECTgetRows(
			'*',
			'tx_ftpimportexport_records',
			// Currently this is only ever called in a BE context
			'1 = 1' . BackendUtility::deleteClause('tx_ftpimportexport_records') . BackendUtility::BEenableFields('tx_ftpimportexport_records')
		);
	}

	/**
	 * Fetches a given FTP configuration.
	 *
	 * @param integer $id Primary key of the FTP configuration to retrieve
	 * @return array
	 */
	public function findByUid($id) {
		$recordId = intval($id);
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
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}
?>
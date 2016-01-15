<?php
namespace Cobweb\Ftpimportexport\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2012-2013 Matthieu Di Blasio, Francois Suter <typo3@cobweb.ch>
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

use Cobweb\Ftpimportexport\Exception\ImportExportException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Execute FTP import or export depending on given data
 *
 * Note: this is not an Extbase controller
 *
 * @author Matthieu Di Blasio, Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_ftpimportexport
 */
class ImportExport {
	/**
	 * @var array Global extension configuration
	 */
	protected $extensionConfiguration = array();

	/**
	 * @var array List of directories from which it is allowed to export
	 */
	protected $allowedDirectoryToExportFrom = array('fileadmin', 'typo3temp', 'uploads');

	/**
	 * @var array List of file extensions which are okay to be processed
	 */
	protected $validFilesExtensions = array();

	public function __construct(){
		$this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ftpimportexport']);
	}

	/**
	 * Dispatches calls to the proper (import or export) method.
	 *
	 * @param array $ftp Import/export configuration (from DB record)
	 * @return bool Result of the run
	 * @throws ImportExportException
	 */
	public function run($ftp) {
		$this->setValidFileExtensions($ftp['pattern']);
		if ($ftp['transfer_type'] === 'export') {
			$result = $this->exportAction($ftp);
		} else {
			$result = $this->importAction($ftp);
		}
		return $result;
	}

	/**
	 * Performs an import of files according to the given FTP configuration.
	 *
	 * @param array $ftp Import/export configuration (from DB record)
	 * @return bool Result of the action
	 * @throws ImportExportException
	 */
	public function importAction($ftp) {
		// Create the necessary drivers
		/** @var \Cobweb\Ftpimportexport\Driver\FtpDriver $fromDriver */
		$fromDriver = GeneralUtility::makeInstance('Cobweb\\Ftpimportexport\\Driver\\FtpDriver');
		$fromDriver->connect($ftp);
		/** @var \Cobweb\Ftpimportexport\Driver\LocalDriver $toDriver */
		$toDriver = GeneralUtility::makeInstance('Cobweb\\Ftpimportexport\\Driver\\LocalDriver');

		// Validate target path. This may throw an exception, but we let it bubble up.
		$targetPath = $toDriver->validatePath($ftp['target_path']);

		// Create target directory
		$toDriver->createDirectory($targetPath);

		if ($fromDriver->changeDirectory($ftp['source_path'])) {
			$files = $fromDriver->fileList('');
			$transferredFiles = array();
			if ($this->extensionConfiguration['debug']) {
				GeneralUtility::devLog('Files to handle', 'ftpimportexport', 0, $files);
			}
			foreach ($files as $aFile) {
				// TODO: check what to do with directories (they are fetched, but with a warning). Maybe handle recursively (with a flag).
				if ($this->isValidFile($aFile)) {
					$targetFilename = $targetPath . $aFile;
					$result = $fromDriver->get($aFile, $targetFilename);
					if ($result) {
						// Keep list of transferred files for post-processing
						$transferredFiles[] = $aFile;
					} else {
						if ($this->extensionConfiguration['debug']) {
							GeneralUtility::devLog('Could not get file: ' . $aFile, 'ftpimportexport', 2);
						}
					}
				}
			}
			// Apply post-processing, if relevant
			if (!empty($ftp['post_processing']) && count($transferredFiles) > 0) {
				$this->postProcessAction($fromDriver, $transferredFiles, $ftp);
			}
		} else {
			if ($this->extensionConfiguration['debug']) {
				$message = 'Could not change to directory: ' . $ftp['source_path'];
				GeneralUtility::devLog($message, 'ftpimportexport', 3);
				throw new ImportExportException(
					$message,
					1387272483
				);
			}
		}
		return TRUE;
	}

	/**
	 * Performs an export of files according to the given FTP configuration.
	 *
	 * @param array $ftp Import/export configuration (from DB record)
	 * @throws ImportExportException
	 * @return bool Result of the action
	 */
	public function exportAction($ftp) {
		// Create the necessary drivers
		/** @var \Cobweb\Ftpimportexport\Driver\LocalDriver $fromDriver */
		$fromDriver = GeneralUtility::makeInstance('Cobweb\\Ftpimportexport\\Driver\\LocalDriver');
		/** @var \Cobweb\Ftpimportexport\Driver\FtpDriver $toDriver */
		$toDriver = GeneralUtility::makeInstance('Cobweb\\Ftpimportexport\\Driver\\FtpDriver');
		$toDriver->connect($ftp);

		// Validate source path. This may throw an exception, but we let it bubble up.
		$sourcePath = $fromDriver->validatePath($ftp['source_path']);
		// Check that path is allowed
		if (!$this->isValidExportPath($sourcePath)) {
			throw new ImportExportException(
				sprintf('Invalid export path (%s)', $sourcePath),
				1387272483
			);
		}

		// Validate target path and make sure it exists
		$targetPath = $toDriver->validatePath($ftp['target_path']);
		$toDriver->createDirectory($targetPath);

		if ($fromDriver->changeDirectory($sourcePath)) {
			GeneralUtility::devLog('Current directory 1: ' . $fromDriver->getCurrentDirectory(), 'ftpimportrexport', 0);
			$files = $fromDriver->fileList('');
			$transferredFiles = array();
			if ($this->extensionConfiguration['debug']) {
				GeneralUtility::devLog('Files to handle', 'ftpimportexport', 0, $files);
			}
			foreach ($files as $aFile) {
				// TODO: check what to do with directories (they are fetched, but with a warning). Maybe handle recursively (with a flag).
				if ($this->isValidFile($aFile)) {
					$sourceFilename = $sourcePath . $aFile;
					$targetFilename = $targetPath . $aFile;
					$result = $toDriver->put(
						$sourceFilename,
						$targetFilename
					);
					if ($result) {
						// Keep list of transferred files for post-processing
						$transferredFiles[] = $aFile;
					} else {
						if ($this->extensionConfiguration['debug']) {
							GeneralUtility::devLog('Could not put file: ' . $sourceFilename . ' to ' . $targetFilename, 'ftpimportexport', 2);
						}
					}
				}
			}
			// Apply post-processing, if relevant
			if (!empty($ftp['post_processing']) && count($transferredFiles) > 0) {
				GeneralUtility::devLog('Current directory 2: ' . $fromDriver->getCurrentDirectory(), 'ftpimportrexport', 0);
				$this->postProcessAction($fromDriver, $transferredFiles, $ftp);
			}
		} else {
			if ($this->extensionConfiguration['debug']) {
				$message = 'Could not change to directory: ' . $sourcePath;
				GeneralUtility::devLog($message, 'ftpimportexport', 3);
				throw new ImportExportException(
					$message,
					1387272483
				);
			}
		}
		return TRUE;
	}

	/**
	 * Performs the configured post-processing action. This may mean moving or deleting files.
	 *
	 * @param \Cobweb\Ftpimportexport\Driver\AbstractDriver $driver Driver for which the post-processing should happen
	 * @param array $files List of files to act on
	 * @param array $ftp Import/export configuration (from DB record)
	 * @return void
	 */
	public function postProcessAction($driver, $files, $ftp) {
		switch ($ftp['post_processing']) {
			case 'move':
				GeneralUtility::devLog('Current directory 3: ' . $driver->getCurrentDirectory(), 'ftpimportrexport', 0);
				$targetPath = $driver->validatePath($ftp['post_processing_path']);
				// Create target directory
				$driver->createDirectory($targetPath);
				foreach ($files as $aFile) {
					$result = $driver->move(
						$aFile,
						$targetPath . $aFile
					);
					if (!$result && $this->extensionConfiguration['debug']) {
						$message = sprintf(
							'Post-processing: could not move file %s to %s',
							$aFile,
							$targetPath . $aFile
						);
						GeneralUtility::devLog($message, 'ftpimportexport', 2);
					}
				}
				break;
			case 'delete':
				foreach ($files as $aFile) {
					$result = $driver->delete($aFile);
					if (!$result && $this->extensionConfiguration['debug']) {
						$message = sprintf(
							'Post-processing: could not delete file %s',
							$aFile
						);
						GeneralUtility::devLog($message, 'ftpimportexport', 2);
					}
				}
				break;
		}
	}

	/**
	 * Sets the list of accepted file extensions.
	 *
	 * @param string $list Comma-separated list of file extensions
	 * @return void
	 */
	protected function setValidFileExtensions($list) {
		$this->validFilesExtensions = GeneralUtility::trimExplode(',', $list, TRUE);
	}

	/**
	 * Checks that the given filename does not match some special strings and that the
	 * file extension matches the allowed ones.
	 *
	 * @param string $filename Name of the file to check
	 * @return bool
	 */
	protected function isValidFile($filename) {
		if ($filename == '.' || $filename == '..') {
			return FALSE;
		}
		// If no extensions have been specified, accept all files
		if (count($this->validFilesExtensions) == 0) {
			return TRUE;
		} else {
			// Otherwise check if vile has a valid extension
			$fileInformation = pathinfo($filename);
			if (in_array($fileInformation['extension'], $this->validFilesExtensions)) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * Checks that the path is within the list of paths allowed to export from.
	 *
	 * @param string $path Path to validate (absolute path expected)
	 * @return bool
	 */
	protected function isValidExportPath($path) {
		foreach ($this->allowedDirectoryToExportFrom as $directory) {
			$fullPath = GeneralUtility::getFileAbsFileName($directory);
			// As soon as one allowed directory is matched, return TRUE
			if (strpos($path, $fullPath) === 0) {
				return TRUE;
			}
		}
		// If no allowed directory matched, return false
		return FALSE;
	}
}

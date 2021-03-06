<?php
namespace Cobweb\Ftpimportexport\Driver;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract class which defines a simple file driver (this is much simpler than a FAL driver).
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package	TYPO3
 * @subpackage tx_ftpimportexport
 */
abstract class AbstractDriver {
	/**
	 * @var string Stores the current directory. This is necessary to obtain similar behaviors across drivers.
	 */
	protected $currentDirectory = '';

	/**
	 * Connects to the storage system.
	 *
	 * @param array $configuration Configuration parameters needed for connection
	 */
	abstract public function connect($configuration);

	/**
	 * Makes the given path absolute and ensures that it is allowed.
	 *
	 * The meaning of "allowed" will depend on the driver.
	 *
	 * @param string $path The path to check
	 * @return string The modified path
	 * @throws \Cobweb\Ftpimportexport\Exception\ImportExportException
	 */
	abstract public function validatePath($path);

	/**
	 * Checks if the given path is absolute.
	 *
	 * @param string $path Path to check
	 * @return boolean
	 */
	abstract public function isAbsolutePath($path);

	/**
	 * Changes directory to the given path.
	 *
	 * @param string $path Path to change to
	 * @return boolean
	 */
	public function changeDirectory($path) {
		if (GeneralUtility::isAbsPath($path)) {
			$fullPath = $path;
		} else {
			$fullPath = $this->currentDirectory . $path;
		}
		// Make sure the path has a trailing slash
		if (strrpos($fullPath, '/') !== strlen($fullPath) - 1) {
			$fullPath .= '/';
		}
		$this->currentDirectory = $fullPath;
		return true;
	}

	/**
	 * Returns the current directory.
	 *
	 * @return string
	 */
	public function getCurrentDirectory() {
		return $this->currentDirectory;
	}

	/**
	 * Creates directory in the given path.
	 *
	 * Sub-directories will be created recursively as needed.
	 *
	 * @param string $path Path where to create the directory
	 * @return boolean
	 */
	abstract public function createDirectory($path);

	/**
	 * Checks if the given path exists and is a directory
	 *
	 * @param string $path Path to check
	 * @return boolean
	 */
	abstract public function directoryExists($path);

	/**
	 * Moves a file from one path to another.
	 *
	 * @param string $fromPath Original path to the file
	 * @param string $toPath Target path to move the file to
	 * @return boolean
	 */
	abstract public function move($fromPath, $toPath);

	/**
	 * Deletes given file.
	 *
	 * @param string $filename Absolute path to the file to be deleted
	 * @return boolean
	 */
	abstract public function delete($filename);

	/**
	 * Gets a file from the remote system and stores it to the local system.
	 *
	 * @param string $remotePath Path to the file to get
	 * @param string $localPath Path to store the file to
	 * @return boolean
	 */
	abstract public function get($remotePath, $localPath);

	/**
	 * Puts a file from the local system to the remote system.
	 *
	 * @param string $localPath Path to the file to get
	 * @param string $remotePath Path to store the file to
	 * @return boolean
	 */
	abstract public function put($localPath, $remotePath);

	/**
	 * Returns the of files in the given directory.
	 *
	 * @param string $path Path to the directory. Use empty string for current directory.
	 * @return array
	 */
	abstract public function fileList($path);
}

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

use Cobweb\Ftpimportexport\Exception\ImportExportException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Implementation of the abstract driver for the local file system.
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package    TYPO3
 * @subpackage tx_ftpimportexport
 */
class LocalDriver extends AbstractDriver
{
    /**
     * Connects to the local file system. Nothing to do.
     *
     * @param array $configuration Configuration parameters needed for connection
     */
    public function connect($configuration)
    {
    }

    /**
     * Makes the given path absolute and ensures that it is allowed.
     *
     * The validation verifies that the path is under the web root or in any path allowed by
     * $GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath'].
     *
     * @param string $path Path to handle
     * @return string Modified and validated path
     * @throws ImportExportException
     */
    public function validatePath($path)
    {
        // Make path absolute
        $localPath = GeneralUtility::getFileAbsFileName(
                $path,
                false
        );
        // Make sure the path has a trailing slash
        if (strrpos($localPath, '/') !== strlen($localPath) - 1) {
            $localPath .= '/';
        }
        // Remove double slashes due to user's input mistake
        $localPath = str_replace('//', '/', $localPath);
        if (!GeneralUtility::isAllowedAbsPath($localPath)) {
            throw new ImportExportException(
                    sprintf('Path not allowed (%s)', $localPath),
                    1389105498
            );
        }
        return $localPath;
    }

    /**
     * Checks if the given path is absolute.
     *
     * @param string $path Path to check
     * @return boolean
     */
    public function isAbsolutePath($path)
    {
        return GeneralUtility::isAbsPath($path);
    }

    /**
     * Changes directory to the given path.
     *
     * If the path is relative, it is added to the current path.
     *
     * @param string $path Path to change to
     * @return boolean
     */
    public function changeDirectory($path)
    {
        parent::changeDirectory($path);
        return chdir($this->currentDirectory);
    }

    /**
     * Creates directory in the given path.
     *
     * Sub-directories will be created recursively as needed.
     *
     * @param string $path Path where to create the directory
     * @return boolean
     */
    public function createDirectory($path)
    {
        try {
            GeneralUtility::mkdir_deep($path);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Checks if the given path exists and is a directory
     *
     * @param string $path Path to check
     * @return boolean
     */
    public function directoryExists($path)
    {
        return is_dir($path);
    }

    /**
     * Moves a file from one path to another.
     *
     * @param string $fromPath Original path to the file
     * @param string $toPath Target path to move the file to
     * @return bool
     */
    public function move($fromPath, $toPath)
    {
        if (!$this->isAbsolutePath($fromPath)) {
            $fromPath = $this->currentDirectory . $fromPath;
        }
        if (!$this->isAbsolutePath($toPath)) {
            $toPath = $this->currentDirectory . $toPath;
        }
        return rename($fromPath, $toPath);
    }

    /**
     * Deletes given file.
     *
     * @param string $filename Absolute path to the file to be deleted
     * @return mixed
     */
    public function delete($filename)
    {
        if (!$this->isAbsolutePath($filename)) {
            $filename = $this->currentDirectory . $filename;
        }
        return unlink($filename);
    }

    /**
     * Gets a file from the remote system and stores it to the local system.
     *
     * Since this driver actually addresses the local system, this implementation simply does a copy.
     *
     * @param string $remotePath Path to the file to get
     * @param string $localPath Path to store the file to
     * @return boolean
     */
    public function get($remotePath, $localPath)
    {
        return copy($remotePath, $localPath);
    }

    /**
     * Puts a file from the local system to the remote system.
     *
     * Since this driver actually addresses the local system, this implementation simply does a copy.
     *
     * @param string $localPath Path to the file to get
     * @param string $remotePath Path to store the file to
     * @return boolean
     */
    public function put($localPath, $remotePath)
    {
        return copy($localPath, $remotePath);
    }

    /**
     * Returns the of files in the given directory.
     *
     * @param string $path Path to the directory. Use empty string for current directory.
     * @return array
     */
    public function fileList($path)
    {
        $fileList = array();
        if (empty($path)) {
            $path = $this->currentDirectory;
        }
        $directoryHandle = opendir($path);
        if ($directoryHandle !== false) {
            while ($item = readdir($directoryHandle)) {
                $fileList[] = $item;
            }
            closedir($directoryHandle);
        }
        return $fileList;
    }
}

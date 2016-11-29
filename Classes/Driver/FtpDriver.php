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
 * Implementation of the abstract driver for a FTP server.
 *
 * @author Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_ftpimportexport
 */
class FtpDriver extends AbstractDriver
{
    /**
     * @var resource Handle on the FTP server
     */
    protected $handle;

    /**
     * Connects to the FTP server.
     *
     * @param array $configuration Configuration parameters needed for connection
     * @throws \Exception
     * @return void
     */
    public function connect($configuration)
    {
        $this->handle = ftp_connect($configuration['ftp_host']);
        if ($this->handle === false) {
            throw new \Exception('Could not connect to FTP server', 1322489458);
        } else {
            if (@ftp_login($this->handle, $configuration['ftp_user'], $configuration['ftp_password'])) {
                ftp_pasv($this->handle, true);
            } else {
                ftp_close($this->handle);
                throw new \Exception('Could not log into the FTP server', 1322489527);
            }
        }
    }

    /**
     * Makes the given path absolute. All paths are allowed.
     *
     * @param string $path Path to handle
     * @return string Modified and validated path
     * @throws \Cobweb\Ftpimportexport\Exception\ImportExportException
     */
    public function validatePath($path)
    {
        $localPath = $path;
        // Make sure the target path starts with a slash
        if (strpos($localPath, '/') !== 0) {
            $localPath = $this->currentDirectory . $localPath;
        }
        // Make sure the target path has a trailing slash
        if (strrpos($localPath, '/') !== strlen($localPath) - 1) {
            $localPath .= '/';
        }
        // Remove double slashes due to user's input mistake
        $localPath = str_replace('//', '/', $localPath);
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
        return strpos($path, '/') === 0;
    }

    /**
     * Changes directory to the given path.
     *
     * @param string $path Path to change to
     * @return boolean
     */
    public function changeDirectory($path)
    {
        parent::changeDirectory($path);
        return @ftp_chdir($this->handle, $this->currentDirectory);
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
        // First try to change to the given directory.
        if (!$this->directoryExists($path)) {
            $pathParts = GeneralUtility::trimExplode('/', $path, true);
            foreach ($pathParts as $subPath) {
                if (!$this->directoryExists($subPath)) {
                    ftp_mkdir($this->handle, $subPath);
                }
                $this->changeDirectory($subPath);
            }
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
        // Check by trying to change into the given directory
        return @ftp_chdir($this->handle, $path);
    }

    /**
     * Moves a file from one path to another.
     *
     * @param string $fromPath Original path to the file
     * @param string $toPath Target path to move the file to
     * @return boolean
     */
    public function move($fromPath, $toPath)
    {
        if (!$this->isAbsolutePath($fromPath)) {
            $fromPath = $this->currentDirectory . $fromPath;
        }
        if (!$this->isAbsolutePath($toPath)) {
            $toPath = $this->currentDirectory . $toPath;
        }
        return ftp_rename($this->handle, $fromPath, $toPath);
    }

    /**
     * Deletes given file.
     *
     * @param string $filename Absolute path to the file to be deleted
     * @return boolean
     */
    public function delete($filename)
    {
        if (!$this->isAbsolutePath($filename)) {
            $filename = $this->currentDirectory . $filename;
        }
        return ftp_delete($this->handle, $filename);
    }

    /**
     * Gets a file from the remote system and stores it to the local system.
     *
     * @param string $remotePath Path to the file to get
     * @param string $localPath Path to store the file to
     * @return boolean
     */
    public function get($remotePath, $localPath)
    {
        return ftp_get($this->handle, $localPath, $remotePath, FTP_BINARY);
    }

    /**
     * Puts a file from the local system to the remote system.
     *
     * @param string $localPath Path to the file to get
     * @param string $remotePath Path to store the file to
     * @return boolean
     */
    public function put($localPath, $remotePath)
    {
        $directory = dirname($remotePath);
        $file = basename($remotePath);
        // Create the storage directory as needed
        // NOTE: this is changing to the given directory no matter the result
        if (!$this->directoryExists($directory)) {
            $this->createDirectory($directory);
        }
        $result = ftp_put($this->handle, $file, $localPath, FTP_BINARY);
        // Change back to root path
        $this->changeDirectory('/');
        return $result;
    }

    /**
     * Returns the of files in the given directory.
     *
     * @param string $path Path to the directory. Use empty string for current directory.
     * @return array
     */
    public function fileList($path)
    {
        $files = ftp_nlist($this->handle, $path);
        if ($files === false) {
            return array();
        } else {
            return $files;
        }
    }
}

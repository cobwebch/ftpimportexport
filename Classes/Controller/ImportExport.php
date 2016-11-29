<?php
namespace Cobweb\Ftpimportexport\Controller;

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

use Cobweb\Ftpimportexport\Driver\AbstractDriver;
use Cobweb\Ftpimportexport\Driver\FtpDriver;
use Cobweb\Ftpimportexport\Driver\LocalDriver;
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
class ImportExport
{
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

    /**
     * @var AbstractDriver
     */
    protected $toDriver = null;

    /**
     * @var AbstractDriver
     */
    protected $fromDriver = null;

    public function __construct()
    {
        $this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ftpimportexport']);
    }

    /**
     * Dispatches calls to the proper (import or export) method.
     *
     * @param array $ftp Import/export configuration (from DB record)
     * @return bool Result of the run
     * @throws ImportExportException
     */
    public function run($ftp)
    {
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
    public function importAction($ftp)
    {
        // Create the necessary drivers
        $this->fromDriver = GeneralUtility::makeInstance(FtpDriver::class);
        $this->fromDriver->connect($ftp);
        $this->toDriver = GeneralUtility::makeInstance(LocalDriver::class);

        // Validate target path. This may throw an exception, but we let it bubble up.
        $targetPath = $this->toDriver->validatePath($ftp['target_path']);

        // Create target directory
        $this->toDriver->createDirectory($targetPath);

        if ($this->fromDriver->changeDirectory($ftp['source_path'])) {
            $transferredFiles = $this->getAllFiles($ftp['source_path'], $targetPath, $ftp['source_path'],
                    $ftp['recursive']);

            // Apply post-processing, if relevant
            if (!empty($ftp['post_processing']) && count($transferredFiles) > 0) {
                $this->postProcessAction($this->fromDriver, $transferredFiles, $ftp);
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
        return true;
    }

    /**
     * Gets all files from the given folder.
     *
     * @param $path string Path from which to get the files
     * @param $targetPath string Path to save files to
     * @param $sourcePath string Source path on the import server
     * @param $recursive bool Set to true to explore file structure recursively
     * @return array List of transferred files
     */
    public function getAllFiles($path, $targetPath, $sourcePath, $recursive = true)
    {
        $transferredFiles = array();
        // Get all files/folders in the current folder
        $files = $this->fromDriver->fileList($path);
        if ($this->extensionConfiguration['debug'] && count($files) > 2) {
            GeneralUtility::devLog('Files to get',
                    'ftpimportexport',
                    0,
                    $files
            );
        }

        foreach ($files as $aFile) {
            $pathInfo = pathinfo($aFile);
            $isDirectory = $this->fromDriver->directoryExists($aFile);

            // If the current file is not a directory, download it and add it to the list of transferred files
            if (!$isDirectory && $this->isValidFile($aFile)) {
                // Remove the source path from the full file path, so we can get the local path where the file will be stored
                $relativePathInfo = pathinfo(str_replace($sourcePath, '', $aFile));

                /** @var string $relativeFolderPath Relative path to the containing folder, starting from the source folder */
                $relativeFolderPath = !in_array($relativePathInfo['dirname'], array('.', '..'),
                        true) ? $relativePathInfo['dirname'] . '/' : '';

                /** @var string $relativeFilePath Relative path to the file, starting from the source folder */
                $relativeFilePath = $relativeFolderPath . $relativePathInfo['basename'];

                // Create destination folder
                $this->toDriver->createDirectory($targetPath . $relativeFolderPath);
                $targetFilename = $targetPath . $relativeFilePath;
                $result = $this->fromDriver->get($aFile, $targetFilename);

                if ($result) {
                    // Keep list of transferred files for post-processing
                    $transferredFiles[] = $relativeFilePath;
                } else {
                    if ($this->extensionConfiguration['debug']) {
                        GeneralUtility::devLog('Could not get file: ' . $aFile, 'ftpimportexport', 2);
                    }
                }

            // If file is a directory, and not "." or ".." and "recursive" option was checked, get files for this folder
            } elseif ($recursive && $isDirectory && isset($pathInfo['basename']) && !in_array($pathInfo['basename'],
                            array('.', '..'), true)
            ) {
                $subfolderFiles = $this->getAllFiles(
                        $aFile,
                        $targetPath,
                        $sourcePath,
                        $recursive
                );
                $transferredFiles = array_merge(
                        $transferredFiles,
                        $subfolderFiles
                );
            }
        }
        return $transferredFiles;
    }

    /**
     * Performs an export of files according to the given FTP configuration.
     *
     * @param array $ftp Import/export configuration (from DB record)
     * @throws ImportExportException
     * @return bool Result of the action
     */
    public function exportAction($ftp)
    {
        // Create the necessary drivers
        $this->fromDriver = GeneralUtility::makeInstance(LocalDriver::class);
        $this->toDriver = GeneralUtility::makeInstance(FtpDriver::class);
        $this->toDriver->connect($ftp);

        // Validate source path. This may throw an exception, but we let it bubble up.
        $sourcePath = $this->fromDriver->validatePath($ftp['source_path']);
        // Check that path is allowed
        if (!$this->isValidExportPath($sourcePath)) {
            throw new ImportExportException(
                    sprintf('Invalid export path (%s)', $sourcePath),
                    1387272483
            );
        }

        // Validate target path and make sure it exists
        $targetPath = $this->toDriver->validatePath($ftp['target_path']);
        $this->toDriver->createDirectory($targetPath);

        if ($this->fromDriver->changeDirectory($sourcePath)) {
            $transferredFiles = $this->putAllFiles($sourcePath, $targetPath, '', $ftp['recursive']);

            // Apply post-processing, if relevant
            if (!empty($ftp['post_processing']) && count($transferredFiles) > 0) {
                $this->postProcessAction($this->fromDriver, $transferredFiles, $ftp);
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
        return true;
    }

    /**
     * Puts all files from the given folder.
     *
     * @param $path string Path from which to get the files
     * @param $targetPath string Path to save files to
     * @param $sourcePath string Source path on the import server
     * @param $recursive bool Set to true to explore file structure recursively
     * @return array List of transferred files
     */
    public function putAllFiles($path, $targetPath, $sourcePath, $recursive = true)
    {
        $transferredFiles = array();
        // Get all files/folders in the current folder
        $files = $this->fromDriver->fileList($path);
        if ($this->extensionConfiguration['debug'] && count($files) > 2) {
            GeneralUtility::devLog(
                    'Files to put',
                    'ftpimportexport',
                    0,
                    $files
            );
        }

        foreach ($files as $aFile) {
            $pathInfo = pathinfo($aFile);
            $sourceFilename = $path . $aFile;
            $isDirectory = $this->fromDriver->directoryExists($sourceFilename);

            // If the current file is not a directory, upload it and add it to the list of transferred files
            if (!$isDirectory && $this->isValidFile($aFile)) {
                $targetFilename = $targetPath . $sourcePath . $aFile;
                $result = $this->toDriver->put(
                        $sourceFilename,
                        $targetFilename
                );

                if ($result) {
                    // Keep list of transferred files for post-processing
                    $transferredFiles[] = $aFile;
                } else {
                    if ($this->extensionConfiguration['debug']) {
                        GeneralUtility::devLog('Could not put file: ' . $aFile, 'ftpimportexport', 2);
                    }
                }

            // If file is a directory, and not "." or ".." and "recursive" option was checked, get files for this folder
            } elseif ($recursive && $isDirectory && isset($pathInfo['basename']) && !in_array($pathInfo['basename'],
                            array('.', '..'), true)
            ) {
                $subfolderFiles = $this->putAllFiles(
                        $path . $aFile . '/',
                        $targetPath,
                        $sourcePath . $aFile . '/',
                        $recursive
                );
                $transferredFiles = array_merge(
                        $transferredFiles,
                        $subfolderFiles
                );
            }
        }
        return $transferredFiles;
    }

    /**
     * Performs the configured post-processing action. This may mean moving or deleting files.
     *
     * @param AbstractDriver $driver Driver for which the post-processing should happen
     * @param array $files List of files to act on
     * @param array $ftp Import/export configuration (from DB record)
     * @return void
     * @throws ImportExportException
     */
    public function postProcessAction($driver, $files, $ftp)
    {
        switch ($ftp['post_processing']) {
            case 'move':
                GeneralUtility::devLog('Current directory 3: ' . $driver->getCurrentDirectory(), 'ftpimportrexport', 0);
                $targetPath = $driver->validatePath($ftp['post_processing_path']);
                // Create target directory
                $driver->changeDirectory('/');
                $driver->createDirectory($targetPath);
                foreach ($files as $aFile) {
                    $sourceFile = $ftp['source_path'] . $aFile;
                    // Get relative path of the file to move so it keeps its parent folders
                    $relativePathInfo = pathinfo($aFile);
                    $relativeFolderPath = $relativePathInfo['dirname'];
                    $targetFilename = $targetPath . $aFile;

                    // Create destination folder
                    $driver->changeDirectory('/');
                    $driver->createDirectory($targetPath . $relativeFolderPath);

                    $result = $driver->move(
                            $sourceFile,
                            $targetFilename
                    );
                    if (!$result && $this->extensionConfiguration['debug']) {
                        $message = sprintf(
                                'Post-processing: could not move file %s to %s',
                                $sourceFile,
                                $targetPath . $aFile
                        );
                        GeneralUtility::devLog($message, 'ftpimportexport', 2);
                    }
                }
                break;
            case 'delete':
                foreach ($files as $aFile) {
                    $sourceFile = $ftp['source_path'] . $aFile;
                    $result = $driver->delete($sourceFile);
                    if (!$result && $this->extensionConfiguration['debug']) {
                        $message = sprintf(
                                'Post-processing: could not delete file %s',
                                $sourceFile
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
    protected function setValidFileExtensions($list)
    {
        $this->validFilesExtensions = GeneralUtility::trimExplode(',', $list, true);
    }

    /**
     * Checks that the given filename does not match some special strings and that the
     * file extension matches the allowed ones.
     *
     * @param string $filename Name of the file to check
     * @return bool
     */
    protected function isValidFile($filename)
    {
        if ($filename === '.' || $filename === '..') {
            return false;
        }
        // If no extensions have been specified, accept all files
        if (count($this->validFilesExtensions) == 0) {
            return true;
        } else {
            // Otherwise check if vile has a valid extension
            $fileInformation = pathinfo($filename);
            if (in_array($fileInformation['extension'], $this->validFilesExtensions, true)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Checks that the path is within the list of paths allowed to export from.
     *
     * @param string $path Path to validate (absolute path expected)
     * @return bool
     */
    protected function isValidExportPath($path)
    {
        foreach ($this->allowedDirectoryToExportFrom as $directory) {
            $fullPath = GeneralUtility::getFileAbsFileName($directory);
            // As soon as one allowed directory is matched, return TRUE
            if (strpos($path, $fullPath) === 0) {
                return true;
            }
        }
        // If no allowed directory matched, return false
        return false;
    }
}

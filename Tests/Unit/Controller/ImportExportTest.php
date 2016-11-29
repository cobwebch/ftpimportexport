<?php
namespace Cobweb\Ftpimportexport\Tests\Unit\Controller;

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

use Cobweb\Ftpimportexport\Controller\ImportExport;

class ImportExportTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var array List of globals to exclude (contain closures which cannot be serialized)
     */
    protected $backupGlobalsBlacklist = array('TYPO3_LOADED_EXT', 'TYPO3_CONF_VARS');

    /**
     * @var ImportExport|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject = null;

    public function setUp() {
        $this->subject = $this->getMock(
            ImportExport::class,
            array('importAction', 'exportAction')
        );
    }

    public function runDataProvider() {
        return array(
            'import action' => array(
                array(
                    'transfer_type' => 'import'
                ),
                'importAction'
            ),
            'random action name' => array(
                array(
                    'transfer_type' => 'foo'
                ),
                'importAction'
            ),
            'export action' => array(
                array(
                    'transfer_type' => 'export'
                ),
                'exportAction'
            ),
        );
    }

    /**
     * @test
     * @dataProvider runDataProvider
     */
    public function runExecutesCorrectAction($parameters, $action) {
        $this->subject->expects(self::once())
            ->method($action);
        $this->subject->run($parameters);
    }
}
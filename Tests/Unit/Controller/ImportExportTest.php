<?php
namespace Cobweb\Ftpimportexport\Tests\Unit\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2015 Francois Suter <typo3@cobweb.ch>
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


class ImportExportTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \Cobweb\Ftpimportexport\Controller\ImportExport|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject = null;

    public function setUp() {
        $this->subject = $this->getMock(
            'Cobweb\\Ftpimportexport\\Controller\\ImportExport',
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
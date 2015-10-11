<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 日 10/11 22:45:02 2015
 *
 * @File Name: tests/WalleTest.php
 * @Description:
 * *****************************************************************/
namespace tests;

class WalleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp() {
        echo __METHOD__;
    }

    public function tearDown() {
        echo __METHOD__;
    }

    public function testWalle() {
        echo __METHOD__;
        $this->assertEquals('walle-web', 'walle-web');
    }

}

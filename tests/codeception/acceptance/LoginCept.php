<?php

use tests\codeception\_pages\LoginPage;
/* @var $scenario Codeception\Scenario */

$I = new AcceptanceTester($scenario);
$I->wantTo('perform actions and see');
$I->amOnPage('/');
$I->see('Walle');
$I->see('登录');

//$loginPage = LoginPage::openBy($I);
//$I->amGoingTo('try to login with empty credentials');
//$loginPage->login('', '');
//if (method_exists($I, 'wait')) {
//    $I->wait(3); // only for selenium
//}
//$I->expectTo('see validations errors');
//$I->see('Username不能为空');
//$I->see('Password不能为空');
//
//$I->amGoingTo('try to login with wrong credentials');
//$loginPage->login('admin', 'wrong');
//if (method_exists($I, 'wait')) {
//    $I->wait(3); // only for selenium
//}
//$I->expectTo('see validations errors');
//$I->see('Incorrect username or password.');
//
//$I->amGoingTo('try to login with correct credentials');
//$loginPage->login('admin', 'admin');
//if (method_exists($I, 'wait')) {
//    $I->wait(3); // only for selenium
//}
//$I->expectTo('see user info');
//$I->see('Logout (admin)');

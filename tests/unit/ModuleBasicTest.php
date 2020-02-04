<?php
/**
 * Copyright (C) 2017-2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @copyright 2017-2018 thirty bees
 * @license   Academic Free License (AFL 3.0)
 */

class ModuleBasicTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $module;

    protected function _before()
    {
        $this->module = Module::getInstanceByName('nocaptcharecaptcha');
    }

    protected function _after()
    {
    }

    public function testModuleNameExists()
    {
        $this->assertEquals($this->module->name, 'nocaptcharecaptcha');
    }

    public function testBootstrapTrue()
    {
        $this->assertTrue($this->module->bootstrap);
    }

    public function testDisplayNameExists()
    {
        $this->assertNotEmpty($this->module->displayName);
    }

    public function testTab()
    {
        $this->assertNotEmpty($this->module->tab);
    }

    public function testVersionExists()
    {
        $this->assertNotEmpty($this->module->version);
    }

    public function testVersionIsSemver()
    {
        $this->assertTrue((bool)preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', $this->module->version));
    }

    public function testModuleKeyExists()
    {
        $this->assertNotEmpty($this->module->module_key);
    }

    public function testDescriptionExists()
    {
        $this->assertNotEmpty($this->module->description);
    }
}

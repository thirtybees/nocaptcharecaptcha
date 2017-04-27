<?php


class ModuleBasicTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $module;

    protected function _before()
    {
        $this->module = Module::getInstanceByName('NoCaptchaRecaptcha');
    }

    protected function _after()
    {
    }

    public function testModuleNameExists()
    {
        $this->assertEquals($this->module->name, 'NoCaptchaRecaptcha');
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

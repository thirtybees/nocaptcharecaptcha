<?php

class ModuleInstallationTest extends \Codeception\TestCase\Test
{
    protected $tester;

    /**
     * @var Module
     */
    protected $module;

    protected function _before()
    {
        $this->module = Module::getInstanceByName('mpcleanurls');
    }

//    public function testModuleReinstall()
//    {
//        $success = $this->module->uninstall();
//        $success &= $this->module->install();
//
//        $this->assertTrue((bool)$success);
//    }
//
//    public function testIsModuleInstalled()
//    {
//        $sql = new DbQuery();
//        $sql->select('m.`active`');
//        $sql->from('module', 'm');
//        $sql->where('m.`name` = \'mpcleanurls\'');
//
//        $this->assertTrue((bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql));
//    }

    protected function _after()
    {
    }
}
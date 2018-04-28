<?php
class ModuleCest
{
    public function moduleConfiguration(AcceptanceTester $I)
    {
        $I->wantTo('ensure that the module can be installed');
        $I->amOnPage('/admin-dev');
        $I->fillField('#email', getenv('PRESTASHOP_EMAIL'));
        $I->fillField('#passwd', getenv('PRESTASHOP_PASSWORD'));
        $I->click('button[name=submitLogin]');
    }

    /**
     * @param AcceptanceTester $I
     * @depends moduleConfiguration
     */
    public function moduleUsage(AcceptanceTester $I)
    {
        $I->wantTo('ensure that the captcha blocks me');
        $I->amOnPage('/');
        $I->click('.header_user_info > .login');
        $I->fillField('#email', 'test@test.test');
        $I->fillField('#passwd', 'testtest');
        $I->click('#SubmitLogin');
    }
}





<?php

class ButtonServiceTest extends PHPUnit_Framework_TestCase
{

    use \mojDashButton\Test\Integration\Services\Helper\ButtonCodeGenerator;

    /**
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @var \mojDashButton\Services\DashButton\DbRegisterService
     */
    private $register;

    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    private $container;

    private $buttons;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->container = Shopware()->Container();

        $this->db = $this->container->get('db');
        $this->register = $this->container->get('moj_dash_button.services.dash_button.db_register_service');

    }

    protected function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub

        if (isset($this->buttons)) {
            $this->removeButtons($this->buttons);
        }
    }

    public function testGetProductForButton()
    {
        $button = $this->createButton($this->getButtonCode());

        $token = $this->getAuthService()->generateToken($button->getButtonCode());

        $productData = $this->getButtonService()->getProduct($token);

        $this->assertNotEmpty($productData);
        $this->assertNotEmpty($productData);
        $this->assertNotEmpty($productData);

        return [$token, $button];

    }

    /**
     * @depends testGetProductForButton
     * @param $data
     * @return \mojDashButton\Models\DashButton
     */
    public function testTriggerClickForButton($data)
    {
        $token = $data[0];
        $button = $data[1];

        $this->assertTrue($this->getButtonService()->triggerClick($token));

        return $button;
    }


    /**
     * @depends testTriggerClickForButton
     * @param $oldButton
     */
    public function testGetNoProductForUnconfiguredButton($oldButton)
    {
        $this->expectException(\Exception::class);

        $button = $this->createButton($this->getButtonCode(), '');
        $this->buttons = [$oldButton, $button];

        $token = $this->getAuthService()->generateToken($button->getButtonCode());

        $this->getButtonService()->getProduct($token);
    }

    private function removeButtons($buttons)
    {
        foreach ($buttons as $button) {
            $this->db->delete('moj_basket_details', 'button_id = ' . $button->getId());
            $this->db->delete('moj_dash_log', 'button_id = ' . $button->getId());
            $this->db->delete('moj_dash_button', 'id = ' . $button->getId());
        }
    }

    private function createButton($buttoncode, $product = 'SW10009.10')
    {
        $em = $this->container->get('models');

        $button = $this->register->registerButton($buttoncode);

        $button->setOrdernumber($product);
        $button->setUserId(1);
        $button->setUser($em->getRepository(\Shopware\Models\Customer\Customer::class)->find(1));

        $em->persist($button);
        $em->flush($button);

        return $button;
    }

    /**
     * @return mixed|\mojDashButton\Services\DashButton\ButtonService
     */
    private function getButtonService()
    {
        $buttonservice = $this->container->get('moj_dash_button.services.dash_button.button_service');
        return $buttonservice;
    }

    /**
     * @return mixed|\mojDashButton\Services\Api\AuthenticationService
     */
    private function getAuthService()
    {
        $authService = $this->container->get('moj_dash_button.services.api.authentication_service');
        return $authService;
    }
}
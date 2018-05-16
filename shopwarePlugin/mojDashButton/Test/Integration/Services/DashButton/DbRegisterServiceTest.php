<?php

use mojDashButton\Services\DashButton\DbRegisterService;

class DbRegisterServiceTest extends PHPUnit_Framework_TestCase
{

    use \mojDashButton\Test\Integration\Services\Helper\ButtonCodeGenerator;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $em;

    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    private $container;

    /**
     * @var \mojDashButton\Models\DashButton
     */
    private $button;

    protected function setUp()
    {
        parent::setUp();

        $this->container = Shopware()->Container();
        $this->em = $this->container->get('models');
    }

    protected function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub

        if(isset($this->button)){
            $this->removeButton($this->button);
        }
    }


    public function testRegisterButton()
    {
        $buttonCode = $this->getButtonCode();

        $button = $this->getRegisterService()->registerButton($buttonCode);

        $this->assertInstanceOf(\mojDashButton\Models\DashButton::class, $button);

        return $button;
    }

    /**
     * @depends testRegisterButton
     * @param $button
     */
    public function testButtonAlreadyRegistered(\mojDashButton\Models\DashButton $button)
    {
        $this->expectException(\Exception::class);
        $this->button = $button;

        $this->getRegisterService()->registerButton($button->getButtonCode());
    }

    private function getRegisterService()
    {
        $loggerMock = $this->getMockBuilder(\mojDashButton\Services\Core\Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();

        return new DbRegisterService(
            $this->container->get('moj_dash_button.services.dash_button.db_collector'),
            $this->em,
            $loggerMock
        );
    }

    private function removeButton($button)
    {
        $this->em->remove($button);
        $this->em->flush($button);
    }
}

<?php
namespace Pointspay\Pointspay\Test\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\TestCase;
use Pointspay\Pointspay\Block\System\Config\DownloadCertificate;

class DownloadCertificateTest extends TestCase
{
    /**
     * @var DownloadCertificate
     */
    private $collect;
    private $contextMock;
    private $elementMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->contextMock = $this->createMock(Context::class);
        $filterManager = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->addMethods(['translitUrl'])
            ->getMock();

        $filterManager->expects($this->any())->method('translitUrl')->willReturn('translited_url');
        $this->contextMock
            ->expects($this->any())
            ->method('getFilterManager')
            ->willReturn($filterManager);

        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->expects($this->any())->method('getUrl')->willReturn('https://example.com/');

        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($urlBuilder);
        $layout = $this->createMock(Layout::class);
        $buttonBlock = $this->createMock(Button::class);
        $buttonBlock->expects($this->any())->method('setData')->willReturnSelf();
        $buttonBlock->expects($this->any())->method('toHtml')->willReturn('html_content');
        $layout->expects($this->any())->method('createBlock')
            ->willReturn($buttonBlock);
        $this->contextMock
            ->expects($this->any())
            ->method('getLayout')
            ->willReturn($layout);
        $evenManagerMock = $this->createMock(\Magento\Framework\Event\Manager::class);
        $evenManagerMock->expects($this->any())->method('dispatch')->willReturnSelf();
        $this->contextMock
            ->expects($this->any())
            ->method('getEventManager')
            ->willReturn($evenManagerMock);
        $this->elementMock = $this->createMock(AbstractElement::class);
        $resolverMock = $this->createMock(\Magento\Framework\View\Element\Template\File\Resolver::class);
        $pathForTemplateFile = realpath(__DIR__);
        $pathForTemplateFile = $pathForTemplateFile . '/_files/redirect.phtml';
        $resolverMock->expects($this->any())->method('getTemplateFileName')->willReturn($pathForTemplateFile);
        $this->contextMock->expects($this->any())->method('getResolver')->willReturn($resolverMock);
        //\Magento\Framework\Filesystem\Directory\Read
        $directoryRead = $this->createMock(\Magento\Framework\Filesystem\Directory\Read::class);
        $filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($directoryRead);
        $this->contextMock->expects($this->any())->method('getFilesystem')->willReturn($filesystemMock);
        $validatorMock = $this->createMock(\Magento\Framework\View\Element\Template\File\Validator::class);
        $validatorMock->expects($this->any())->method('isValid')->willReturn(true);
        $this->contextMock->expects($this->any())->method('getValidator')->willReturn($validatorMock);
        $enginePoolMock = $this->createMock(\Magento\Framework\View\TemplateEnginePool::class);
        $phtmlEngineMock = $this->createMock(\Magento\Framework\View\TemplateEngine\Php::class);
        $enginePoolMock->expects($this->any())->method('get')->willReturn($phtmlEngineMock);
        $this->contextMock->expects($this->any())->method('getEnginePool')->willReturn($enginePoolMock);
        $this->collect = $objectManager->getObject(
            DownloadCertificate::class,
            [
                'context' => $this->contextMock,
                'actionPath' => 'admin/*/*',
                'returnPath' => 'adminhtml/system_config/edit/section/payment',
                'buttonName' => 'Button',
                'template' => 'Pointspay_Pointspay::system/config/getCertificateButton.phtml',
                'data' => ['area' => 'adminhtml']
            ]
        );
    }

    public function testRenderRemovesScopeAndReturnsParentRender()
    {
        $objectManager = new ObjectManager($this);
        $elementMock = $objectManager->getObject(
            DownloadCertificateTestSubject::class,
            [
                'context' => $this->contextMock,
                'actionPath' => 'admin/*/*',
                'returnPath' => 'adminhtml/system_config/edit/section/payment',
                'buttonName' => 'Button',
                'template' => 'Pointspay_Pointspay::system/config/getCertificateButton.phtml'
            ]
        );

        $result = $this->collect->render($elementMock);
        $this->assertIsString($result);
        $this->stringContains('<tr id="row_pointspay_access_settings_merchant_certificate"><td class="label"><label for="pointspay_access_settings_merchant_certificate"><span></span></label></td><td class="value"></td><td class=""></td></tr>');
    }

    public function testGetButtonHtmlReturnsButtonHtml()
    {
        $result = $this->collect->getButtonHtml();
        $this->assertIsString($result);
        $this->stringContains('html_content');
    }
    public function testGetVirtualCode()
    {
        $result = $this->collect->getVirtualMethodCode();
        $this->assertNull($result);
    }
    public function testGetElementScopeId()
    {
        $result = $this->collect->getElementScopeId();
        $this->assertNull($result);
    }

    public function testGetActionUrlReturnsUrl()
    {
        $result = $this->collect->getActionUrl();
        $this->stringContains('https://example.com/');
        $this->assertIsString($result);
    }

}

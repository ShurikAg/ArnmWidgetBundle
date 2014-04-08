<?php
namespace Arnm\WidgetBundle\Tests\Manager;

use Arnm\WidgetBundle\Entity\Widget;

use Arnm\PagesBundle\Entity\Area;
use Arnm\WidgetBundle\Manager\WidgetsManager;
use Arnm\PagesBundle\Entity\Page;
/**
 * WidgetsManager test case.
 */
class WidgetsManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Creates mocked page object to support the testing
     *
     * @return Page
     */
    protected function preparMockedPage()
    {
        $area1 = new Area();
        $area1->setCode('area1');
        $area1->setId(1);
        $area2 = new Area();
        $area2->setCode('area2');
        $area2->setId(2);
        $areas = new \Doctrine\Common\Collections\ArrayCollection();
        $areas->add($area1);
        $areas->add($area2);
        $template = $this->getMock('Arnm\PagesBundle\Entity\Template', array(
            'getAreas'
        ));
        $template->expects($this->any())
            ->method('getAreas')
            ->will($this->returnValue($areas));
        $page = $this->getMock('Arnm\PagesBundle\Entity\Page', array(
            'getTemplate',
            'getId'
        ));
        $page->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue($template));
        $page->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(321));

        return $page;
    }

    /**
     * Tests if the data mocking is done right
     */
    public function testDataMocking()
    {
        $page = $this->preparMockedPage();

        $this->assertEquals(321, $page->getId());
        $areasFromPage = $page->getTemplate()->getAreas();
        $this->assertTrue($areasFromPage instanceof \Doctrine\Common\Collections\ArrayCollection);
        $this->assertEquals(2, $areasFromPage->count());
        $this->assertEquals('area1', $areasFromPage[0]->getCode());
        $this->assertEquals('area2', $areasFromPage[1]->getCode());
    }

    /**
     * Tests WidgetManager->addNewWidgetToPage()
     *
     * @depends testDataMocking
     */
    public function testAddNewWidgetToPage()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManager', array(
            'flush',
            'persist',
            'getConnection'
        ), array(), '', false, true, true);
        $doctrine = $this->getMock('Doctrine\Bundle\DoctrineBundle\Registry', array(
            'getManager'
        ), array(), '', false, true, true);
        $connection = $this->getMock('Doctrine\DBAL\Connection', array(
            'beginTransaction',
            'commit'
        ), array(), '', false, true, true);

        $connection->expects($this->once())
            ->method('beginTransaction');
        $connection->expects($this->once())
            ->method('commit');
        $em->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));
        $em->expects($this->once())
            ->method('flush');
        $em->expects($this->once())
            ->method('persist');
        $doctrine->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($em));

        $mgr = new WidgetsManager($doctrine);

        $page = $this->preparMockedPage();

        $area = 'area123';
        $bundle = 'TheBundleName';
        $controller = 'TheController';
        $index = 0;

        //test exception
        try {
            $mgr->addNewWidgetToPage($page, 'title', $bundle, $controller, $area, $index);
        } catch (\InvalidArgumentException $e) {

        }

        $area = 'area1';

        $widget = $mgr->addNewWidgetToPage($page, 'title', $bundle, $controller, $area, $index);
        $this->assertTrue($widget instanceof Widget);
        $this->assertEquals($area, $widget->getAreaCode());
        $this->assertEquals($bundle, $widget->getBundle());
        $this->assertEquals($controller, $widget->getController());
        $this->assertEquals($index, $widget->getSequence());
    }

}


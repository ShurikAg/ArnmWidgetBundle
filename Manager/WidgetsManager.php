<?php
namespace Arnm\WidgetBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Arnm\WidgetBundle\Entity\WidgetRepository;
use Arnm\WidgetBundle\Entity\Widget;
use Arnm\PagesBundle\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Registry;
/**
 * Widgets manager responsible for collecting and distributing the information regarding widgets
 *
 * @author Alex Agulyansky <alex@iibspro.com>
 */
class WidgetsManager
{
    /**
     * Doctrine registry entity
     *
     * @var Registry
     */
    private $doctrine;

    /**
     * Widgets repository object instance
     *
     * @var WidgetRepository
     */
    private $widgetRepository;

    /**
     * Temp caching for pages widgets
     *
     * @var array
     */
    private $widgetsLists = array();

    /**
     * Constructor
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Gets organized list of all available widgets
     *
     * @return array
     */
    public function getAvailableWidgets()
    {
        return array(
            array(
                'title' => "Text Widget",
                "id" => "",
                "bundle" => "ArnmContent",
                "controller" => "Text"
            ),
            array(
                'title' => "Html Widget",
                "id" => "",
                "bundle" => "ArnmContent",
                "controller" => "Html"
            ),
            array(
                'title' => "Menu Widget",
                "id" => "",
                "bundle" => "ArnmMenu",
                "controller" => "PlainMenu"
            ),
            array(
                'title' => "Slide Show Widget",
                "id" => "",
                "bundle" => "MediaContent",
                "controller" => "SlideShow"
            ),
            array(
                'title' => "Small Slide Show Widget",
                "id" => "",
                "bundle" => "MediaContent",
                "controller" => "SmallSlideShow"
            ),
            array(
                'title' => "Filmstrip Widget",
                "id" => "",
                "bundle" => "MediaContent",
                "controller" => "FilmStrip"
            ),
            array(
                'title' => "Showcase Collections List Widget",
                "id" => "",
                "bundle" => "ArnmCatalog",
                "controller" => "CollectionsList"
            ),
            array(
                'title' => "Showcase Container Widget",
                "id" => "",
                "bundle" => "ArnmCatalog",
                "controller" => "ShowcaseContainer"
            )
        );
    }

    /**
     * Gets the whole structure of widget for given page
     *
     * @param Page $page
     *
     * @return ArrayCollection
     */
    public function findAllWidgetForPage(Page $page)
    {
        $pageId = $page->getId();
        if (! isset($this->widgetsLists[$pageId])) {
            $this->widgetsLists[$pageId] = $this->getWidgetRepository()->findAllByPageId($page->getId());
        }

        return $this->widgetsLists[$pageId];
    }

    /**
     * Gets an subset of widgets our of given collection for area code
     *
     * @param array $widgets
     * @param string $areaCode
     *
     * @return array
     */
    public function filterWidgetsByArea(array $widgets, $areaCode)
    {
        $filtered = array();
        foreach ($widgets as $widget) {
            if ($widget->getAreaCode() == $areaCode) {
                $filtered[] = $widget;
            }
        }

        return $filtered;
    }

    /**
     * Create new widget instance and adds it to the given page under required area.
     *
     * @param Page   $page       Page objct to which we need to add the widget
     * @param string $title      Widget's title
     * @param string $bundle     Widget's bundle name
     * @param string $controller Widget's controller name
     * @param string $areaCode   Area code, corresponds to areas div ID in CSS
     * @param int    $index      Index of the widget within the area it was placed.
     *
     * @throws \Exception If the operation failed
     *
     * @return Widget Newly create widget object
     */
    public function addNewWidgetToPage(Page $page, $title, $bundle, $controller, $areaCode, $index)
    {
        if (! $this->isAreaAssignedToPage($page, $areaCode)) {
            throw new \InvalidArgumentException("Area code '" . $areaCode . "' is not assigned to the page!");
        }
        $em = $this->getEntityManager();
        //will do it in transaction
        $em->getConnection()->beginTransaction();
        try {
            //create the widget object
            $widget = new Widget();
            $widget->setTitle($title);
            $widget->setBundle($bundle);
            $widget->setController($controller);
            $widget->setAreaCode($areaCode);
            $widget->setPage($page);
            //deal with the sequesnce
            $this->placeWidgetInArea($page, $widget, $areaCode, $index);
            $em->persist($widget);
            $em->flush();
            $em->getConnection()->commit();
            return $widget;
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }
    }
    /**
     * Moves the widget from one area to another if needed or just reorganizes
     *
     * @param Page   $page
     * @param int    $widgetId
     * @param string $targetArea
     * @param int    $targetIndex
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return Widget The same widget with new data
     */
    public function moveWidget(Page $page, $widgetId, $targetArea, $targetIndex)
    {
        //find the widget and validate that the widget is in fact related to this page
        $widget = $this->findWidgetById($widgetId);
        if (! ($widget instanceof Widget)) {
            throw new \RuntimeException("Could not find widget with ID: '" . $widgetId . "'!");
        }
        //validate that the widget that we found is actual related to this page.
        //we can't move widget between the pages
        if ($widget->getPage()->getId() !== $page->getId()) {
            throw new \RuntimeException("Moving widgets between pages is not supported");
        }
        //make sure that the target area is also defined in the page
        if (! $this->isAreaAssignedToPage($page, $targetArea)) {
            throw new \InvalidArgumentException("Area code '" . $targetArea . "' is not assigned to the page!");
        }
        //now we can start performing the move
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            //Reorganize all the sequesnces in the old area
            $this->removeWidgetFromArea($widget);
            $widget->setAreaCode($targetArea);
            //add the widget into the new area
            //deal with the sequesnce
            $this->placeWidgetInArea($page, $widget, $targetArea, $targetIndex);
            $em->persist($widget);
            $em->flush();
            $em->getConnection()->commit();
            return $widget;
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }
    }
    /**
     * Finds the right value for widget's sequence within the area.
     * Decision made based on requested index and current status of the area
     *
     * @param Page   $page     Operated page
     * @param Widget $widget   Widget object that needed to be placed
     * @param string $areaCode Area code that the widget needed to be placed in
     * @param int    $index    Index in wi=hich the widget needs to be placed
     */
    protected function placeWidgetInArea(Page $page, Widget $widget, $areaCode, $index)
    {
        //check if there are any widgets for this page and area
        $widgets = $page->getWidgets();
        //find how many widgets already in the given area
        $widgetsInArea = 0;
        if ($widgets->count() > 0) {
            foreach ($widgets->toArray() as $wgt) {
                if ($wgt->getAreaCode() == $areaCode) {
                    $widgetsInArea ++;
                }
            }
        }
        $sequence = 0;
        if ($widgetsInArea === 0) {
            //this is the only widget in this are, so sequense going to be 0 in any case
            $sequence = 0;
        } elseif ($widgetsInArea <= $index) {
            //if the new index as a number of existing widgets or greater, the the new widget going to be the last one
            $sequence = $widgetsInArea;
        } else {
            //here we need to set the index as been asked and promote all the other widgets (with higher sequence) by 1
            $sequence = $index;
            $this->promoteWidgetsInAreaByIndex($areaCode, $index);
        }
        $widget->setSequence($sequence);
    }
    /**
     * Increses the sequence of all widgets with sequence equals or higher than $index in the given area by 1.
     *
     * @param string  $areaCode Area code
     * @param integer $index    Sequence of interest
     */
    protected function promoteWidgetsInAreaByIndex($areaCode, $index)
    {
        $em = $this->getEntityManager();
        $q = $em->createQuery("UPDATE ArnmWidgetBundle:Widget w SET w.sequence = (w.sequence + 1) WHERE w.sequence >= :sequence AND w.area_code = :code");
        $q->setParameter('sequence', $index);
        $q->setParameter('code', $areaCode);
        $q->execute();
    }
    /**
     * Removes the widget from an area
     *
     * @param Widget $widget
     */
    protected function removeWidgetFromArea(Widget $widget)
    {
        $em = $this->getEntityManager();
        $q = $em->createQuery(
            "UPDATE ArnmWidgetBundle:Widget w SET w.sequence = (w.sequence - 1) WHERE w.sequence > :sequence AND w.area_code = :code  AND w.id <> :id");
        $q->setParameter('sequence', $widget->getSequence());
        $q->setParameter('code', $widget->getAreaCode());
        $q->setParameter('id', $widget->getId());
        $q->execute();
        //not required, but just to make sure that we not doing something stupid
        $widget->setAreaCode(null);
    }
    /**
     * Determines if such an area code actually exists and attached to the given page
     *
     * @param Page   $page     An entoty of the page
     * @param string $areaCode Code of the area of an interest
     *
     * @return boolean
     */
    protected function isAreaAssignedToPage(Page $page, $areaCode)
    {
        $areas = $page->getTemplate()->getAreas();
        foreach ($areas as $area) {
            if ($area->getCode() == $areaCode) {
                return true;
            }
        }
        return false;
    }
    /**
     * Gets entity manager
     *
     * @return Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }
    /**
     * Gets doctrin service object
     *
     * @return Registry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }
    /**
     * Gets an instance of widget repository
     *
     * @return Arnm\WidgetBundle\Entity\WidgetRepository
     */
    public function getWidgetRepository()
    {
        if (is_null($this->widgetRepository)) {
            $this->widgetRepository = $this->getEntityManager()->getRepository('ArnmWidgetBundle:Widget');
        }
        return $this->widgetRepository;
    }
    /**
     * Gets a single widget instance by ID
     *
     * @param int $id
     *
     * @return Widget
     */
    public function findWidgetById($id)
    {
        return $this->getWidgetRepository()->findOneBy(array(
            'id' => $id
        ));
    }
}

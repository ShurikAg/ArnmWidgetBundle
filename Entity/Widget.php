<?php
namespace Arnm\WidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Arnm\PagesBundle\Entity\Page;
use Arnm\WidgetBundle\Entity\Param;
/**
 * Arnm\WidgetBundle\Entity\Widget
 *
 * @ORM\Table(name="widget")
 * @ORM\Entity(repositoryClass="Arnm\WidgetBundle\Entity\WidgetRepository")
 */
class Widget
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=100)
     */
    private $title;
    
    /**
     * @var string $bundle
     *
     * @ORM\Column(name="bundle", type="string", length=100)
     */
    private $bundle;
    
    /**
     * @var string $controller
     *
     * @ORM\Column(name="controller", type="string", length=100)
     */
    private $controller;
    
    /**
     * @var string $action
     *
     * @ORM\Column(name="action", type="string", length=100, nullable=true)
     */
    private $action;
    
    /**
     * @var smallint $sequence
     *
     * @ORM\Column(name="sequence", type="smallint")
     */
    private $sequence;
    
    /**
     * @var string $area_code
     *
     * @ORM\Column(name="area_code", type="string", length=50)
     */
    private $area_code;
    
    /**
     * @var integer $pageId
     *
     * @ORM\Column(name="page_id", type="integer")
     */
    private $pageId;
    
    /**
     * @ORM\ManyToOne(targetEntity="Arnm\PagesBundle\Entity\Page", inversedBy="widgets")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     */
    private $page;
    
    /**
     * @ORM\OneToMany(targetEntity="Param", mappedBy="widget")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $params;
    
    public function __construct()
    {
        $this->params = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set bundle
     *
     * @param string $bundle
     * @return Widget
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
        return $this;
    }
    
    /**
     * Get bundle
     *
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }
    
    /**
     * Set controller
     *
     * @param string $controller
     * @return Widget
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }
    
    /**
     * Get controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }
    
    /**
     * Set action
     *
     * @param string $action
     * @return Widget
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }
    
    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * Set sequence
     *
     * @param smallint $sequence
     * @return Widget
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
        return $this;
    }
    
    /**
     * Get sequence
     *
     * @return smallint
     */
    public function getSequence()
    {
        return $this->sequence;
    }
    
    /**
     * Set area_code
     *
     * @param string $areaCode
     * @return Widget
     */
    public function setAreaCode($areaCode)
    {
        $this->area_code = $areaCode;
        return $this;
    }
    
    /**
     * Get area_code
     *
     * @return string
     */
    public function getAreaCode()
    {
        return $this->area_code;
    }
    
    /**
     * @return the $title
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
     * Set page_id
     *
     * @param integer $pageId
     * @return Widget
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
        return $this;
    }
    
    /**
     * Get page_id
     *
     * @return integer
     */
    public function getPageId()
    {
        return $this->pageId;
    }
    
    /**
     * Set page
     *
     * @param Arnm\PagesBundle\Entity\Page $page
     * @return Widget
     */
    public function setPage(Page $page = null)
    {
        $this->page = $page;
        return $this;
    }
    
    /**
     * Get page
     *
     * @return Arnm\WidgetBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }
    
    /**
     * Add params
     *
     * @param Arnm\WidgetBundle\Entity\Param $params
     * @return Widget
     */
    public function addParam(Arnm\WidgetBundle\Entity\Param $params)
    {
        $this->params[] = $params;
        return $this;
    }
    
    /**
     * Get params
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Finds parameter from the list of params for this widget by it's name
     *
     * @param string $name
     * 
     * @return Param
     */
    public function getParamByName($name)
    {
        if(count($this->getParams()) == 0) {
            return null;
        }
        
        foreach ($this->getParams() as $param) {
            if($param->getName() == $name) {
                return $param;
            }
        }
        
        return null;
    }
}
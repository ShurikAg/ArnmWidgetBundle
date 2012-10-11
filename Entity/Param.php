<?php

namespace Arnm\WidgetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Arnm\WidgetBundle\Entity\Param
 *
 * @ORM\Table(name="param")
 * @ORM\Entity
 */
class Param
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var text $value
     *
     * @ORM\Column(name="value", type="text")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Widget", inversedBy="params")
     * @ORM\JoinColumn(name="widget_id", referencedColumnName="id")
     */
    private $widget;

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
     * Set name
     *
     * @param string $name
     * @return Param
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value
     *
     * @param text $value
     * @return Param
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @return text
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set widget
     *
     * @param Arnm\WidgetBundle\Entity\Widget $widget
     * @return Param
     */
    public function setWidget(Widget $widget = null)
    {
        $this->widget = $widget;
        return $this;
    }

    /**
     * Get widget
     *
     * @return Arnm\WidgetBundle\Entity\Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }
}
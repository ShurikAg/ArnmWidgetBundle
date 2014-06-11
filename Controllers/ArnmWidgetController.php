<?php
namespace Arnm\WidgetBundle\Controllers;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;
use Arnm\WidgetBundle\Entity\Param;
use Arnm\WidgetBundle\Entity\Widget;
use Symfony\Component\HttpFoundation\Response;
use Arnm\CoreBundle\Controllers\ArnmAjaxController;
/**
 *
 * Base class for any widget cotroller
 *
 * @author Alex Agulyansky <alex@iibspro.com>
 */
abstract class ArnmWidgetController extends ArnmAjaxController
{
    /**
     * Renders the widget
     * This method is used to render to widget on the actual site
     *
     * @param Widget $widget
     *
     * @return Response
     */
    abstract public function renderAction(Widget $widget);

    /**
     * Renders widget config form template
     *
     * @return Response
     */
    abstract public function editAction();

    /**
     * Renders widget's data in json format
     *
     * @param int $id
     */
    public function dataAction($id)
    {
        $widgetsMgr = $this->getWidgetManager();
        $widget = $widgetsMgr->findWidgetById($id);
        if (!($widget instanceof Widget)) {
            throw $this->createNotFoundException("Widget with id: '" . $id . "' not found!");
        }

        $data = array();
        foreach ($this->getConfigFields() as $field) {
            $param = $widget->getParamByName($field);
            $data[$field] = ( ($param instanceof Param) ? $param->getValue() : '' );
        }

        return $this->createResponse($data);
    }

    /**
     * Gets config fields that must be returned in the model with data
     *
     * @return array
     */
    public function getConfigFields()
    {
        return array();
    }

    /**
     * Handles widget edit form submittion
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    abstract public function updateAction($id, Request $request);

    /**
     * Gets an instance of widget manager
     *
     * @return Arnm\WidgetBundle\Manager\WidgetsManager
     */
    protected function getWidgetManager()
    {
        return $this->get('arnm_widget.manager');
    }

    /**
     * Extracts an array based on content passed in request as json
     *
     * @throws BadRequestHttpException
     *
     * @return array
     */
    protected function extractArrayFromRequest(Request $request)
    {
        $content = $request->getContent();
        if (empty($content)) {
            throw new BadRequestHttpException("Empty payload!");
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            throw new BadRequestHttpException("Payload is not parsable!");
        }

        return $data;
    }
}

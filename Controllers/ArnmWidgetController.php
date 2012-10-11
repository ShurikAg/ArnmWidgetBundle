<?php
namespace Arnm\WidgetBundle\Controllers;

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
     * Responds to an ajax request for edit form for a widget
     *
     * @param int $id
     *
     * @return Response
     */
    abstract public function editAction($id);

    /**
     * Handles widget edit form submittion
     *
     * @param int $id
     *
     * @return Response
     */
    abstract public function updateAction($id);

    /**
     * Handles widget deletion request
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $this->validateRequest();

        $reply = array();
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $widget = $this->getWidgetManager()->findWidgetById($id);
            if (! ($widget instanceof Widget)) {
                throw new \InvalidArgumentException("Widget was not found by ID.");
            }

            foreach ($widget->getParams() as $param) {
                $em->remove($param);
            }
            $em->remove($widget);

            $reply['status'] = 'OK';
            $reply['id'] = $id;
            //flush
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $reply['sratus'] = 'FAILED';
            $reply['reson'] = $e->getMessage();
        }

        return $this->createResponse($reply);
    }

    /**
     * Gets an instance of widget manager
     *
     * @return Arnm\WidgetBundle\Manager\WidgetsManager
     */
    protected function getWidgetManager()
    {
        return $this->get('arnm_widget.manager');
    }
}

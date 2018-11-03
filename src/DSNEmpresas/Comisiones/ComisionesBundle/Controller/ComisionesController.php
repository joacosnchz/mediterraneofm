<?php

namespace DSNEmpresas\Comisiones\ComisionesBundle\Controller;

# Kernel
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
# Formularios
use DSNEmpresas\Comisiones\ComisionesBundle\Form\InsertComisionesType;
use DSNEmpresas\Comisiones\ComisionesBundle\Form\EditComisionesType;
# Entidades
use DSNEmpresas\Comisiones\ComisionesBundle\Entity\Comisiones;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;
use MakerLabs\PagerBundle\Adapter\DoctrineOrmAdapter;

class ComisionesController extends Controller {

    public function insertAction(Request $request) {
        $comisiones = new Comisiones();
        $formType = new InsertComisionesType();
        $form = $this->createForm($formType, $comisiones);

        $form->handleRequest($request);
        if($request->getMethod() == 'POST'):
	        $validator = $this->get('validator');
	        $errors = $validator->validate($comisiones);
	        if($form->isValid()):
	        	$em = $this->getDoctrine()->getManager();
	        	$em->persist($comisiones);
	        	$em->flush();
	        	$this->get('session')->getFlashBag()->add(
	                    'notice', 'La comision ' . $comisiones->getId() . ' ha sido agregada con éxito.'
	                    );

	        	return $this->redirect($this->generateUrl('showComisiones'));
	    	else:
                    return $this->render('MediterraneoFMBundle::validation.html.twig', array(
                            'errors' => $errors,
                        ));
	    	endif;
    	endif;

        return $this->render('ComisionesBundle::insertComisiones.html.twig', array('form' => $form->createView()));
    }

    public function showAction($page) {
        $comisiones = $this->getDoctrine()
                        ->getRepository('ComisionesBundle:Comisiones')
                        ->findBy(array(), array('valor' => 'ASC'));

    	if(!$comisiones):
    		return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Comisiones', 'Nuevo' => $this->generateUrl('insertComisiones')));
    	endif;

    	$session = $this->get('session');

    	if(isset($_REQUEST['Comisiones'])):
            $session->set('Comisiones', $_REQUEST['Comisiones']);
        endif;

    	$adapter = new ArrayAdapter($comisiones);

    	$pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('Comisiones')));

    	return $this->render('ComisionesBundle::showComisiones.html.twig', array('comisiones' => $comisiones, 'pager' => $pager));
    }

    public function deleteAction($id) {
    	$id2 = explode(',', $id);
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $comisiones = $em->getRepository('ComisionesBundle:Comisiones')->find($id2);

                $em->remove($comisiones);
                try {
                    $em->flush();
                
                    $this->get('session')->getFlashBag()->add(
                        'notice', 'La comisión "' . $comisiones->getDescripcion() . '" ha sido borrada con éxito.'
                    );
                } catch (\Doctrine\DBAL\DBALException $ex) { // Si tiene agencias asociadas -> violacion de integridad
                    $this->get('session')->getFlashBag()->add(
                        'error', 'La comisión "' . $comisiones->getDescripcion() . '" no ha podido ser borrada. Para poder hacerlo, por favor elimine todas las agencias con esta comisión.'
                    );
                }
            endif;
        endforeach;

        return $this->redirect($this->generateUrl('showComisiones'));
    }

    public function showOneAction($id, $nombre, Request $request) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            if($id2 != 'yes'): // 'yes' es el valor que envía el formulario al seleccionar todo
                $comision = $this->getDoctrine()
                                ->getRepository('ComisionesBundle:Comisiones')
                                ->find($id2);
            endif;
        endforeach;
    	$formType = new EditComisionesType();
    	$form = $this->createForm($formType, $comision);

    	$form->handleRequest($request);
    	if($request->getMethod() == 'POST'):
	        $validator = $this->get('validator');
	        $errors = $validator->validate($comision);
    		if($form->isValid()):
	        	$em = $this->getDoctrine()->getManager();
	        	$em->flush();

	        	$this->get('session')->getFlashBag()->add(
	                    'notice', 'La comision ' . $comision->getId() . ' ha sido editada con éxito.'
                    );

	        	return $this->redirect($this->generateUrl('showComisiones'));
			endif;
		endif;

    	return $this->render('ComisionesBundle::showComision.html.twig', array('form' => $form->createView(), 'comision' => $comision));
    }
}

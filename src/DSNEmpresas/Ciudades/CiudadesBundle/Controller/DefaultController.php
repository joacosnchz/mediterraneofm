<?php

namespace DSNEmpresas\Ciudades\CiudadesBundle\Controller;

# Kernel
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
# Entities
use DSNEmpresas\Ciudades\CiudadesBundle\Entity\Ciudades;
# Form
use DSNEmpresas\Ciudades\CiudadesBundle\Form\CiudadesType;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;
use MakerLabs\PagerBundle\Adapter\DoctrineOrmAdapter;

class DefaultController extends Controller {
    
    public function insertAction(Request $request) {
        # Objeto que modifico
        $ciudades = new Ciudades();
        # Formulario que modifico
        $formType = new CiudadesType();
        $form = $this->createForm($formType, $ciudades);

        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($ciudades);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->persist($ciudades);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La zona ' . $ciudades->getId() . ' ha sido agregada con éxito.'
                        );  
                
                return $this->redirect($this->generateUrl('showCiudades'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
        'errors' => $errors,
    ));
            endif;
        endif;
        
        return $this->render('CiudadesBundle::insertCiudades.html.twig', array('form' => $form->createView()));
    }
    
    public function showAction($page, Request $request) {
        $ciudades = $this->getDoctrine()
                        ->getRepository('CiudadesBundle:Ciudades')
                        ->findAll();
        
        if(!$ciudades):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Ciudades', 'Nuevo' => $this->generateUrl('insertCiudades')));
        endif;
        
        $session = $this->get('session');
        
        if($request->request->get('Ciudades') != null):
            $session->set('Ciudades', $request->request->get('Ciudades'));
        endif;
        
        $adapter = new ArrayAdapter($ciudades);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('Ciudades')));
        
        return $this->render('CiudadesBundle::showCiudades.html.twig', array('pager' => $pager));
    }
    
    public function showOneAction($id, Request $request) {
        $id = urldecode($id);
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            if($id2 != 'yes'):
                $ciudad = $this->getDoctrine()
                            ->getRepository('CiudadesBundle:Ciudades')
                            ->find($id2);
            endif;
        endforeach;
        
        $formType = new CiudadesType();
        $form = $this->createForm($formType, $ciudad);
        
        if($request->isMethod('POST')):
            $form->bind($request);
        
            if($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La zona ' . $ciudad->getId() . ' ha sido editada con éxito.'
                );
                
                return $this->redirect($this->generateUrl('showCiudades'));
            endif;
        endif;
        
        return $this->render('CiudadesBundle::showCiudad.html.twig', array('form' => $form->createView(), 'ciudad' => $ciudad));
    }
    
    public function deleteAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $ciudad = $em->getRepository('CiudadesBundle:Ciudades')->find($id2);
            
                $em->remove($ciudad);
                try {    
                    $em->flush();
                
                    $this->get('session')->getFlashBag()->add(
                            'notice', 'La zona "' . $ciudad->getNombre() . '" ha sido borrada con éxito.'
                    );
                } catch (\Doctrine\DBAL\DBALException $ex) { // Si tiene agencias o emisoras asociadas -> violacion de integridad
                    $this->get('session')->getFlashBag()->add(
                        'error', 'La zona "' . $ciudad->getNombre() . '" no ha podido ser borrada. Para poder hacerlo, por favor elimine todas las agencias y las emisoras de esta zona.'
                    );
                }
            endif;
        endforeach;
        
        return $this->redirect($this->generateUrl('showCiudades'));
    }
}

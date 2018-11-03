<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DSNEmpresas\Emisoras\EmisorasBundle\Controller;

# Funciones kernel de symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;
# Entities
use DSNEmpresas\Emisoras\EmisorasBundle\Entity\Emisoras;
# Types
use DSNEmpresas\Emisoras\EmisorasBundle\Form\InsertEmisorasType;
use DSNEmpresas\Emisoras\EmisorasBundle\Form\EditEmisorasType;

class EmisorasController extends Controller {
    # Alta de Emisoras, insert en tabla Emisoras.

    public function insertAction() {
        $emisoras = new Emisoras();
        $formType = new InsertEmisorasType($this->getDoctrine()->getManager());
        $form = $this->createForm($formType, $emisoras);

        return $this->render('EmisorasBundle::insertEmisoras.html.twig', array('form' => $form->createView()));
    }

    public function insertSuccessAction(Request $request) {
        # Objeto que modifico
        $emisoras = new Emisoras();
        # Formulario que modifico
        $formType = new InsertEmisorasType();
        $form = $this->createForm($formType, $emisoras);

        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($emisoras);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->persist($emisoras);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La emisora ' . $emisoras->getIdEmisora() . ' ha sido agregada con éxito.'
                        );
                return $this->redirect($this->generateUrl('showEmisoras'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
                    'errors' => $errors,
                ));
            endif;
        endif;
    }
    
    /**
     *
     * @Route("/emisoras/show/{page}", defaults={"page"=1}, name="showEmisoras")
     * 
     */
    public function showAction($page, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if(in_array('ROLE_SUPER_ADMIN' ,$user->getRoles())):
            $emisoras = $this->getDoctrine()
                    ->getRepository('EmisorasBundle:Emisoras')
                    ->findAll();
        else:
            $emisoras = $this->getDoctrine()
                    ->getRepository('EmisorasBundle:Emisoras')
                    ->findBy(array('id_ciudad' => $user->getIdAgencia()->getIdCiudad()));
        endif;
        
        $session = $this->get('session');
        
        if($request->request->get('Emisoras') != null):
            $session->set('Emisoras', $request->request->get('Emisoras'));
        endif;
         
        if (!$emisoras):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Emisoras'));
        endif;
        
        $adapter = new ArrayAdapter($emisoras);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('Emisoras')));

        return $this->render('EmisorasBundle::showEmisoras.html.twig', array('emisoras' => $emisoras, 'pager' => $pager));
    }
    
    public function showOneAction($nombre, Request $request) {
        $nombre2 = explode(',', $nombre);
        foreach($nombre2 as $id):
            if($id != 'yes'):
                $emisora = $this->getDoctrine()
                    ->getRepository('EmisorasBundle:Emisoras')
                    ->find($id);
            endif;
        endforeach;
        
        $formType = new EditEmisorasType();
        $form = $this->createForm($formType, $emisora);
        
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($emisora);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La emisora ' . $emisora->getIdEmisora() . ' ha sido editada con éxito.'
                );

                return $this->redirect($this->generateUrl('showEmisoras'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
        'errors' => $errors,
    ));
            endif;
        endif;
        
        return $this->render('EmisorasBundle::showEmisora.html.twig', array('form' => $form->createView(), 'emisora' => $emisora));
    }
    
    public function deleteAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $emisora = $em->getRepository('EmisorasBundle:Emisoras')->find($id2);
                
                $em->remove($emisora);
                try {
                    $em->flush();
                    
                    $this->get('session')->getFlashBag()->add(
                        'notice', 'La emisora "' . $emisora->getNombre() . '" ha sido borrada con éxito.'
                    );
                } catch (\Doctrine\DBAL\DBALException $ex) { // Si tiene tarifas asociadas -> violacion de integridad
                    $this->get('session')->getFlashBag()->add(
                        'error', 'La emisora "' . $emisora->getNombre() . '" no se ha podido borrar. Para poder hacerlo, por favor elimine todas las tarifas y las programaciones de esta emisora.'
                    );
                }
                
                $this->container->get('doctrine')->resetEntityManager();
            endif;
        endforeach;
        
        return $this->redirect($this->generateUrl('showEmisoras'));
    }
}

?>
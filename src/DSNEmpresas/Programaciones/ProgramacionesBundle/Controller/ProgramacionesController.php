<?php

namespace DSNEmpresas\Programaciones\ProgramacionesBundle\Controller;

# Funciones del kernel de Symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
# Entities
use DSNEmpresas\Programaciones\ProgramacionesBundle\Entity\Programaciones;
# Types
use DSNEmpresas\Programaciones\ProgramacionesBundle\Form\InsertProgramacionesType;
use DSNEmpresas\Programaciones\ProgramacionesBundle\Form\EditProgramacionesType;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;

class ProgramacionesController extends Controller {
    
    public function insertAction(Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        # Objeto que modifico
        $programaciones = new Programaciones();
        # Formulario que modifico
        $formType = new InsertProgramacionesType($user->getIdAgencia()->getIdCiudad()->getId(), $user->getRoles());
        $form = $this->createForm($formType, $programaciones);

        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($programaciones);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->persist($programaciones);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'notice', 'La programación ' . $programaciones->getId() . ' ha sido agregada con éxito.'
                );
                
                return $this->redirect($this->generateUrl('showProgramaciones'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
                    'errors' => $errors,
                ));
            endif;
        endif;
        
        return $this->render('ProgramacionesBundle::insertProgramaciones.html.twig', array('form' => $form->createView()));
    }

    public function showAction($page, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if(in_array('ROLE_SUPER_ADMIN' ,$user->getRoles())):
            $programaciones = $this->getDoctrine()
                    ->getRepository('ProgramacionesBundle:Programaciones')
                    ->findAll();
        else:
            $repository = $this->getDoctrine()
                    ->getRepository('ProgramacionesBundle:Programaciones');

            $query = $repository->createQueryBuilder('pr')
                            ->leftJoin('pr.id_emisora', 'e')
                            ->where('e.id_ciudad = :id_ciudad')
                            ->setParameter('id_ciudad', $user->getIdAgencia()->getIdCiudad())
                            ->getQuery();

            $programaciones = $query->getResult();
        endif;
        
        $session = $this->get('session');
        
        if($request->request->get('Programaciones') != null):
            $session->set('Programaciones', $request->request->get('Programaciones'));
        endif;
         
        if (!$programaciones):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Programaciones'));
        endif;
        
        $adapter = new ArrayAdapter($programaciones);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('Programaciones')));

        return $this->render('ProgramacionesBundle::showProgramaciones.html.twig', array('programaciones' => $programaciones, 'pager' => $pager));
    }

    public function showOneAction($id, $nombre, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $id2 = explode(',', $id);
        foreach($id2 as $id3):
            if($id3 != 'yes'):
                $programacion = $this->getDoctrine()
                    ->getRepository('ProgramacionesBundle:Programaciones')
                    ->find($id3);
            endif;
        endforeach;
        
        $formType = new EditProgramacionesType($user->getIdAgencia()->getIdCiudad()->getId(), $user->getRoles());
        $form = $this->createForm($formType, $programacion);
        
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();
            $emisora = $this->getDoctrine()->getRepository('EmisorasBundle:Emisoras')->findOneBy(array('id_emisora' => $data->getIdEmisora()));
            $programacion->setIdEmisora($emisora);
            $validator = $this->get('validator');
            $errors = $validator->validate($programacion);            
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La programacion ' . $programacion->getId() . ' ha sido editada con éxito.'
                );

                return $this->redirect($this->generateUrl('showProgramaciones'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
                    'errors' => $errors,
                ));
            endif;
        endif;
        
        
        return $this->render('ProgramacionesBundle::showProgramacion.html.twig', array('form' => $form->createView(), 'programacion' => $programacion));
    }

    public function deleteAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $programacion = $em->getRepository('ProgramacionesBundle:Programaciones')->find($id2);

                $em->remove($programacion);
                try {
                    $em->flush();
                    $this->get('session')->getFlashBag()->add(
                        'notice', 'La programacion "' . $programacion->getNombre() . '" ha sido borrada con éxito.'
                    );
                } catch (\Doctrine\DBAL\DBALException $ex) {
                    $this->get('session')->getFlashBag()->add(
                        'error', 'La programacion "' . $programacion->getNombre() . '" no ha podido ser borrada. Para poder hacerlo, por favor elimine todos los programas de esta programación.'
                    );
                }
                
                $this->container->get('doctrine')->resetEntityManager();
            endif;
        endforeach;
        
        return $this->redirect($this->generateUrl('showProgramaciones'));
    }
}

?>

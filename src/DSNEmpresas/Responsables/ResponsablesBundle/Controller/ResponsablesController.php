<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DSNEmpresas\Responsables\ResponsablesBundle\Controller;

# Funciones kernel de symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
# Entities
use DSNEmpresas\Responsables\ResponsablesBundle\Entity\Responsables;
# Types
use DSNEmpresas\Responsables\ResponsablesBundle\Form\InsertResponsablesType;
use DSNEmpresas\Responsables\ResponsablesBundle\Form\EditResponsablesType;
use DSNEmpresas\Responsables\ResponsablesBundle\Form\ResponsablesFilterType;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;

class ResponsablesController extends Controller {
        
    # Alta de Emisoras, insert en tabla Emisoras.
    public function insertAction() {
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $responsables = new Responsables();
        $formType = new InsertResponsablesType($user->getRoles(), $user->getIdAgencia());
        $form = $this->createForm($formType, $responsables);
        
        return $this->render('ResponsablesBundle::insertResponsables.html.twig', array('form' => $form->createView()));
    }
    
    public function insertSuccessAction(Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        # Objeto que modifico
        $responsables = new Responsables();
        # Formulario que modifico
        $formType = new InsertResponsablesType($user->getRoles(), $user->getIdAgencia());
        $form = $this->createForm($formType, $responsables);

        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($responsables);
            
            if ($form->isValid()):
                /* Password encoding ! */
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($responsables);
                $contrasena = $encoder->encodePassword($data->getPassword(), $responsables->getSalt());
                $responsables->setPassword($contrasena);
                /* End password encoding */
                $em = $this->getDoctrine()->getManager();
                $em->persist($responsables);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'El responsable ' . $responsables->getId() . ' ha sido agregado con éxito.'
                        );
                
                return $this->redirect($this->generateUrl('showResponsables'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
        'errors' => $errors,
    ));
            endif;
        endif;
    }
    
    /**
     *
     * @Route("/responsables/show/{page}", defaults={"page"=1}, name="showResponsables")
     * 
     */
    public function showAction($page, $clear, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $session = $this->get('session');
        
        if($request->request->get('Responsables') != null):
            $session->set('Responsables', $request->request->get('Responsables'));
        endif;
        
        if($clear):
            $session->set('nombreResp', '');
            $session->set('is_active', '');
            $session->set('id_agencia', '');
        endif;
        
        $defaults = array();
        if($session->get('id_agencia')):
            $defaults['id_agencia'] = $session->get('id_agencia');
        endif;
        if($session->get('is_active')):
            $defaults['is_active'] = $session->get('is_active');
        endif;
        
        $resp = new Responsables();
        $formType = new ResponsablesFilterType($user->getRoles(), $defaults, $this->getDoctrine()->getManager());
        $form = $this->createForm($formType, $resp);
        
        $form->handleRequest($request);
        if($request->isMethod("POST")):
            if($form->get('nombre')->getData()):
                $session->set('nombreResp', '');
            endif;
        endif;
        if($form->get('nombre')->getData()):
            $session->set('nombreResp', $form->get('nombre')->getData());
        endif;
        if($form->get('is_active')->getData()):
            $session->set('is_active', $form->get('is_active')->getData());
        else:
            if($form->get('is_active')->getViewData() == 'null'): // valor enviado por el form al seleccionar empty_value
                $session->set('is_active', '');
            endif;
        endif;
        if($form->get('id_agencia')->getData()):
            $session->set('id_agencia', $form->get('id_agencia')->getData());
        else:
            if($form->get('id_agencia')->getViewData() == 'null'): // valor enviado por el form al seleccionar empty_value
                $session->set('id_agencia', '');
            endif;
        endif;
        
        $repository = $this->getDoctrine()
                    ->getRepository('ResponsablesBundle:Responsables');
        
        // no se completó nada del filtro
        if(!$session->get('nombreResp') && !$session->get('is_active') && !$session->get('id_agencia')):
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $query = $repository->createQueryBuilder('r')
                            ->where('r.username != :username')
                            ->setParameter('username', 'jsanchezdsn') // usuarios de super administradores
                            ->andWhere('r.username != :username2')
                            ->setParameter('username2', 'rdottim')
                            ->getQuery();

                $responsables = $query->getResult();
            else:
                $query = $repository->createQueryBuilder('r')
                            ->where('r.username != :username')
                            ->setParameter('username', 'jsanchezdsn') // usuarios de super administradores
                            ->andWhere('r.username != :username2')
                            ->setParameter('username2', 'rdottim')
                            ->andWhere('r.id_agencia = :idAgencia')
                            ->setParameter('idAgencia', $user->getIdAgencia())
                            ->getQuery();

                $responsables = $query->getResult();
            endif;
        endif;
        
        // si se completo algo del filtro
        if($session->get('nombreResp') || $session->get('is_active') || $session->get('id_agencia')):
            $em = $this->getDoctrine()->getManager();
            $querystring = 'SELECT r FROM ResponsablesBundle:Responsables r WHERE ';
            
            if($session->get('nombreResp')):
                // notar el uso de parentesis para diferenciar esta expresion
                $querystring .= "(CONCAT(r.nombre, ' ', r.apellido) LIKE '%" . $session->get('nombreResp') . "%' ";
                $querystring .= "OR CONCAT(r.apellido, ' ', r.nombre) LIKE '%" . $session->get('nombreResp') . "%') ";
                
                if($session->get('is_active') || $session->get('id_agencia')):
                    $querystring .= 'AND ';
                endif;
            endif;
            
            if($session->get('is_active')):
                if($session->get('is_active') == 'true'):
                    $querystring .= 'r.isActive = ' . 1 . ' ';
                elseif($session->get('is_active') == 'false'):
                    $querystring .= 'r.isActive = ' . 0 . ' ';
                endif;
                
                if($session->get('id_agencia')):
                    $querystring .= 'AND ';
                endif;
            endif;
            
            if($session->get('id_agencia')):
                $querystring .= 'r.id_agencia = ' . $session->get('id_agencia') . ' ';
            endif;
            
            $querystring .= "AND r.username != 'jsanchezdsn' AND r.username != 'rdottim' ";
            
            $query = $em->createQuery($querystring);
            
            $responsables = $query->getResult();
        else:
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $query = $repository->createQueryBuilder('r')
                            ->where('r.username != :username')
                            ->setParameter('username', 'jsanchezdsn') // usuarios de super administradores
                            ->andWhere('r.username != :username2')
                            ->setParameter('username2', 'rdottim')
                            ->getQuery();

                $responsables = $query->getResult();
            else:
                $query = $repository->createQueryBuilder('r')
                            ->where('r.username != :username')
                            ->setParameter('username', 'jsanchezdsn') // usuarios de super administradores
                            ->andWhere('r.username != :username2')
                            ->setParameter('username2', 'rdottim')
                            ->andWhere('r.id_agencia = :idAgencia')
                            ->setParameter('idAgencia', $user->getIdAgencia())
                            ->getQuery();

                $responsables = $query->getResult();
            endif;
        endif;
         
        if (!$responsables):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Responsables', 'Nuevo' => $this->generateUrl('insertResponsables'), 'Restablecer' => $this->generateUrl('showResponsables', array('clear' => true))));
        endif;
        
        $adapter = new ArrayAdapter($responsables);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('Responsables')));

        return $this->render('ResponsablesBundle::showResponsables.html.twig', array('form' => $form->createView(), 'responsables' => $responsables,'pager' => $pager));
    }
    
    public function showOneAction($nombre, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $nombre2 = explode(',', $nombre);
        foreach($nombre2 as $id):
            if($id != 'yes'):
                $responsable = $this->getDoctrine()
                        ->getRepository('ResponsablesBundle:Responsables')
                        ->findOneBy(array('id' => $id));
            endif;
        endforeach;

        $old = $responsable->getPassword();
        
        $formType = new EditResponsablesType($user->getRoles(), $user->getIdAgencia());
        $form = $this->createForm($formType, $responsable);
         
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($responsable);

            $check = $_POST['mediterraneofm_mediterraneofmbundle_editresponsablestype']['oldPassword'];
            
            /* Password encoding ! */
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($responsable);
            $nueva = $encoder->encodePassword($check, $responsable->getSalt());

            /* Confirmacion de contraseña */
            if($nueva != $old):
                return $this->render('ResponsablesBundle::showResponsable.html.twig', array('form' => $form->createView(), 'responsable' => $responsable, 'errors' => array(0 => array('message' => 'No fue posible realizar la modificación, porfavor intente nuevamente.'))));
            endif;
            
            if($form->isValid()):
                if(!empty($data->getPassword())):
                    $contrasena = $encoder->encodePassword($data->getPassword(), $responsable->getSalt());
                    $responsable->setPassword($contrasena);
                else:
                    $responsable->setPassword($old);
                endif;
                /* End password encoding */
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'El responsable ' . $responsable->getId() . ' ha sido editado con éxito.'
                        );

                return $this->redirect($this->generateUrl('showResponsables'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
                        'errors' => $errors,
                    ));
            endif;
        endif;
        
        return $this->render('ResponsablesBundle::showResponsable.html.twig', array('form' => $form->createView(), 'responsable' => $responsable));
    }
    
    public function deleteAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $responsables = $em->getRepository('ResponsablesBundle:Responsables')->find($id2);

                $em->remove($responsables);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'El responsable ' . $id2 . ' ha sido borrado con éxito.'
                        );
            endif;
        endforeach;

        return $this->redirect($this->generateUrl('showResponsables'));
    }
    
    public function desactivarAction($id) {
        $id2 = explode(',', $id);
        $em = $this->getDoctrine()->getManager();
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $responsable = $this->getDoctrine()
                                ->getRepository('ResponsablesBundle:Responsables')
                                ->find($id2);
        
                if($responsable->getIsActive() == 1):
                    $responsable->setIsActive(0);
                else:
                    $responsable->setIsActive(1);
                endif;
                $em->persist($responsable);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'El estado del responsable ' . $id2 . ' ha sido cambiado con éxito.'
                );
            endif;
        endforeach;
        
        return $this->redirect($this->generateUrl('showResponsables'));
    }
}
?>

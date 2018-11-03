<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DSNEmpresas\Clientes\ClientesBundle\Controller;

# Funciones del kernel de Symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;
# Entities
use DSNEmpresas\Clientes\ClientesBundle\Entity\Clientes;
# Types
use DSNEmpresas\Clientes\ClientesBundle\Form\InsertClientesType;
use DSNEmpresas\Clientes\ClientesBundle\Form\EditClientesType;
use DSNEmpresas\Clientes\ClientesBundle\Form\ClientesFilterType;
use DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Controller\CtasCtesClientesController;

class ClientesController extends Controller {
    
    # Alta de Clientes, insert en tabla Clientes.
    public function insertAction() {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $clientes = new Clientes();
        $formType = new InsertClientesType($user->getRoles());
        $form = $this->createForm($formType, $clientes);
        
        return $this->render('ClientesBundle::insertClientes.html.twig', array('form' => $form->createView()));
    }

    # Alta de Clientes, insert en tabla Clientes. VISTA SIN TEMPLATE (PARA POPUP)
    public function insertSTAction() {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $clientes = new Clientes();
        $formType = new InsertClientesType($user->getRoles());
        $form = $this->createForm($formType, $clientes);
        
        return $this->render('ClientesBundle::insertClientesST.html.twig', array('form' => $form->createView()));
    }
    
    public function insertSuccessAction(Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        # Objeto que modifico
        $clientes = new Clientes();
        # Formulario que modifico
        $formType = new InsertClientesType($user->getRoles());
        $form = $this->createForm($formType, $clientes);

        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $validator = $this->get('validator');
            $errors = $validator->validate($clientes);

            if ($form->isValid()):
                die();
                $em = $this->getDoctrine()->getManager();
                
                $clientes->setIdAgencia($user->getIdAgencia());
                $em->persist($clientes);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'El cliente ' . $clientes->getRazonSocial() . ' ha sido agregado con éxito.'
                        );                
                
                return $this->redirect($this->generateUrl('showClientes'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
        'errors' => $errors,
    ));
            endif;
        endif;
    }
    
    /**
     *
     * @Route("/clientes/show/{page}", defaults={"page"=1}, name="showClientes")
     * 
     */
    public function showAction($page, $clear, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
            $clientes = $this->getDoctrine()
                ->getRepository('ClientesBundle:Clientes')
                ->findBy(array(), array('nombre' => 'ASC'));
        else:
            $clientes = $this->getDoctrine()
                ->getRepository('ClientesBundle:Clientes')
                ->findBy(array('id_agencia' => $user->getIdAgencia()), array('nombre' => 'ASC'));
        endif;
        
        $session = $this->get('session');
        
        if($clear):
            $session->set('nombre', '');
            $session->set('razonSocial', '');
            $session->set('isActive', '');
            $session->set('idAgencia', '');
            $session->set('clientes_orden', 'nombre');
        endif;
        
        if(isset($_REQUEST['clientescant'])):
            $session->set('clientescant', $_REQUEST['clientescant']);
        endif;
        
        $defaults = array('isActive' => null, 'idAgencia' => null, 'orden' => null);
        if($session->get('isActive') == 1):
            $defaults['isActive'] = 1;
        elseif($session->get('isActive') == 0):
            $defaults['isActive'] = 0;
        endif;
        if($session->get('idAgencia')):
            $defaults['idAgencia'] = $session->get('idAgencia');
        endif;
        if($session->get('clientes_orden')):
            $defaults['orden'] = $session->get('clientes_orden');
        endif;
        
        $em = $this->getDoctrine()->getManager();
        $clien = new Clientes();
        $formType = new ClientesFilterType($user->getRoles(), $defaults, $em);
        $form = $this->createForm($formType, $clien, array('action' => $this->generateUrl('showClientes')));
        
        $form->handleRequest($request);

        if($request->isMethod('POST')):
            if(!$form->get('nombre')->getData()):
                $session->set('nombre', '');
            endif;
            if(!$form->get('razon_social')->getData()):
                $session->set('razonSocial', '');
            endif;
        endif;
        
        if($form->get('nombre')->getData()):
            $session->set('nombre', $form->get('nombre')->getData());
        endif;
        
        if($form->get('razon_social')->getData()):
            $session->set('razonSocial', $form->get('razon_social')->getData());
        endif;
        
        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
            if($form->get('id_agencia')->getData()):
                $session->set('idAgencia', $form->get('id_agencia')->getData());
            else:
                if($form->get('id_agencia')->getViewData() == 'null'):
                    $session->set('idAgencia', '');
                endif;
            endif;
        endif;
        
        if($form->get('isActive')->getData()):
            $session->set('isActive', $form->get('isActive')->getData());
            /* Ponemos los valores como strings para que no sea tan tedioso 
             * el control de estas variables boolean. En el momento de 
             * filtrar realmente los resultados cambiamos los datos por 
             * los valores boolean correctos.
             */
        else:
            if($form->get('isActive')->getViewData() == 'null'):
                $session->set('isActive', '');
            endif;
        endif;

        if($form->get('orden')->getData()):
            $session->set('clientes_orden', $form->get('orden')->getData());
        else:
            if($form->get('orden')->getViewData() == 'null'):
                $session->set('clientes_orden', '');
            endif;
        endif;
        
        $repository = $this->getDoctrine()->getRepository('ClientesBundle:Clientes');
        
        // Si no hay nada, el orden es por nombre
        if(!$session->get('nombre') && !$session->get('razonSocial') && !$session->get('idAgencia') && !$session->get('isActive')):
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $query = $repository->createQueryBuilder('c')->orderBy('c.nombre')->getQuery();
            else:
                $query = $repository->createQueryBuilder('c')
                            ->where('c.id_agencia = :idAgencia')
                            ->setParameter('idAgencia', $user->getIdAgencia())
                            ->orderBy('c.nombre')
                            ->getQuery();
            endif;
            
            $clientes = $query->getResult();
        endif;
        
        if($session->get('nombre') || $session->get('razonSocial') || $session->get('idAgencia') || $session->get('isActive') || $session->get('clientes_orden')):
            $querystring = 'SELECT c FROM ClientesBundle:Clientes c ';

            if($session->get('nombre') || $session->get('razonSocial') || $session->get('idAgencia') || $session->get('isActive') || !in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $querystring .= 'WHERE ';
            endif;
            
            if($session->get('nombre')):
                // notar el uso de parentesis para diferenciar esta expresion
                $querystring .= "(CONCAT(c.nombre, ' ', c.apellido) LIKE '%" . $session->get('nombre') . "%' ";
                $querystring .= "OR CONCAT(c.apellido, ' ', c.nombre) LIKE '%" . $session->get('nombre') . "%') ";
                
                if($session->get('razonSocial') || $session->get('isActive')):
                    $querystring .= 'AND ';
                endif;
            endif;
            
            if($session->get('razonSocial')):
                /* $querystring .= "(CONCAT(c.razon_social, ' ', c.comercio) LIKE '%" . $session->get('razonSocial') . "%' ";
                $querystring .= "OR CONCAT(c.comercio, ' ', c.razon_social) LIKE '%" . $session->get('razonSocial') . "%') "; */
                $querystring .= "(c.razon_social LIKE '%" . $session->get('razonSocial') . "%' ";
                $querystring .= "OR c.comercio LIKE '%" . $session->get('razonSocial') . "%') ";
                
                if($session->get('isActive')):
                    $querystring .= "AND ";
                endif;
            endif;
            
            if($session->get('isActive')):
                /* Acá cambiamos los valores en string 
                 * por true-false(1-0) según corresponda. */
                if($session->get('isActive') == 'true'):
                    $querystring .= 'c.isActive = ' . 1 . ' ';
                elseif($session->get('isActive') == 'false'):
                    $querystring .= 'c.isActive = ' . 0 . ' ';
                endif;
            endif;
            
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                if($session->get('idAgencia')):
                    if($session->get('nombre') || $session->get('razonSocial') || $session->get('isActive')):
                        $querystring .= "AND ";
                    endif;
                    $querystring .= "c.id_agencia = " . $session->get('idAgencia') . " ";
                endif;
            else:
                if($session->get('nombre') || $session->get('razonSocial') || $session->get('isActive')):
                    $querystring .= "AND ";
                endif;
                $querystring .= "c.id_agencia = " . $user->getIdAgencia() . " ";
            endif;

            if($session->get('clientes_orden') && $session->get('clientes_orden') != 'saldo'):
                $querystring .= "ORDER BY c." . $session->get('clientes_orden') . " ASC ";
            endif;
                
            $query = $em->createQuery($querystring);
            
            $clientes = $query->getResult();
        else: // Si no hay nada el orden es por nombre
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $query = $repository->createQueryBuilder('c')->orderBy('c.nombre')->getQuery();
            else:
                $query = $repository->createQueryBuilder('c')
                            ->where('c.id_agencia = :idAgencia')
                            ->setParameter('idAgencia', $user->getIdAgencia())
                            ->orderBy('c.nombre')
                            ->getQuery();
            endif;
            
            $clientes = $query->getResult();
        endif;
         
        if (!$clientes):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Clientes', 'Restablecer' => $this->generateUrl('showClientes') . '/1/1'));
        endif;

        $ctactecli = new CtasCtesClientesController();
        $hoy = new \DateTime('now');
        foreach($clientes as $cliente):
            $saldo = $this->forward('CtasCtesClientesBundle:CtasCtesClientes:saldoCliente', array(
                'id_cliente' => $cliente->getIdCliente(),
                'fecha_desde' => $hoy->format('d-m-Y'),
                'fecha_hasta' => $hoy->format('d-m-Y')
            ));
            //$saldo = $ctactecli->calculateSaldos($cliente->getIdCliente(), $hoy->format('d-m-Y'), $hoy->format('d-m-Y'));
            $cliente->setSaldo((float)$saldo->getContent());
        endforeach;

        if($session->get('clientes_orden') && $session->get('clientes_orden') == 'saldo'):
            $clientes = $this->orderBySaldo($clientes);
        endif;
        
        $adapter = new ArrayAdapter($clientes);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('clientescant')));

        return $this->render('ClientesBundle::showClientes.html.twig', array('form' => $form->createView(), 'clientes' => $clientes, 'pager' => $pager));
    }

    private function orderBySaldo($clientes) {
        for($c = 0;$c < count($clientes)-1;$c++)
            for($k = $c + 1;$k < count($clientes);$k++)
                if($clientes[$c]->getSaldo() < $clientes[$k]->getSaldo()) {
                    $aux = clone $clientes[$c];
                    $clientes[$c] = $clientes[$k];
                    $clientes[$k] = $aux;
                }

        return $clientes;
    }
    
    public function showOneAction($nombre, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $nombre2 = explode(',', $nombre);
        foreach($nombre2 as $id):
            if($id != 'yes'):
                 $cliente = $this->getDoctrine()
                        ->getRepository('ClientesBundle:Clientes')
                        ->find($id);
            endif;
        endforeach;
        
        $formType = new EditClientesType($user->getRoles());
        $form = $this->createForm($formType, $cliente);
         
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($cliente);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'notice', 'El cliente ' . $cliente->getIdCliente() . ' ha sido editado con éxito.'
                );

                return $this->redirect($this->generateUrl('showClientes'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
        'errors' => $errors,
    ));
            endif;
        endif;
        
        return $this->render('ClientesBundle::showCliente.html.twig', array('form' => $form->createView(), 'cliente' => $cliente));
    }
    
    public function deleteAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $clientes = $em->getRepository('ClientesBundle:Clientes')->find($id2);

                $em->remove($clientes);
                try {
                    $em->flush();
                
                    $this->get('session')->getFlashBag()->add(
                        'notice', 'El cliente "' . $clientes->getComercio() . '" ha sido borrado con éxito.'
                    );
                } catch (\Doctrine\DBAL\DBALException $ex) { // Si tiene ordenes asociadas -> violacion de integridad
                    $this->get('session')->getFlashBag()->add(
                        'error', 'El cliente "' . $clientes->getComercio() . '" no ha podido ser borrado. Para hacerlo, por favor elimine las ordenes de publicidad de este cliente.'
                    );
                }
            endif;
        endforeach;

        return $this->redirect($this->generateUrl('showClientes'));
    }
    
    public function cambiarEstadoAction($id) {
        $id2 = explode(',', $id);
        $em = $this->getDoctrine()->getManager();
        foreach($id2 as $id3):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id3 != 'yes'):
                $cliente = $em->getRepository('ClientesBundle:Clientes')->find($id3);
                
                if($cliente->getIsActive() == 1):
                    $cliente->setIsActive(0);
                else:
                    $cliente->setIsActive(1);
                endif;
                $this->get('session')->getFlashBag()->add(
                    'notice', 'El estado del cliente "' . $cliente->getComercio() . '" ha sido cambiado con éxito.'
                );
                $em->flush();
            endif;
        endforeach;
            
        return $this->redirect($this->generateUrl('showClientes'));
    }
}
?>

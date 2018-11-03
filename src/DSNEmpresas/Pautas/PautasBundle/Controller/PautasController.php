<?php

namespace DSNEmpresas\Pautas\PautasBundle\Controller;

# Funciones kernel de symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;
# Entities
use DSNEmpresas\Pautas\PautasBundle\Entity\Pautas;
# Types
use DSNEmpresas\Pautas\PautasBundle\Form\InsertCostoTarifasType;
use DSNEmpresas\Pautas\PautasBundle\Form\EditCostoTarifasType;
use DSNEmpresas\Pautas\PautasBundle\Form\PautasFilterType;

class PautasController extends Controller {
    
    public function insertAction(Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        # Objeto que modifico
        $costotarifas = new Pautas();
        
        $tarifas = $this->getDoctrine()
                    ->getRepository('TarifasBundle:Tarifas')
                    ->findAll();
        #$mes_actual = date('m');
        $tar = array();
        if($tarifas):
            foreach($tarifas as $t):
                if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                    $tar[$t->getIdTarifa()] = $t->getNombre();
                else:
                    if(strtotime($t->getFechaDesde()->format('d-m-Y')) <= strtotime(date('d-m-Y')) && strtotime($t->getFechaHasta()->format('d-m-Y')) >= strtotime(date('d-m-Y'))):
                        if($t->getIdEmisora()->getIdCiudad() == $user->getIdAgencia()->getIdCiudad()):
                            $tar[$t->getIdTarifa()] = $t->getNombre();
                        endif;
                    endif;
                endif;
            endforeach;
        endif;
        $formType = new InsertCostoTarifasType($tar, $user->getRoles());
        $form = $this->createForm($formType, $costotarifas);
        $validator = $this->get('validator');
        $errors = $validator->validate($costotarifas);

        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();

            $tarifa = $this->getDoctrine()
                        ->getRepository('TarifasBundle:Tarifas')
                        ->findOneBy(array('id_tarifa' => $data->getIdTarifa()));

            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
            	$costotarifas->setIdTarifa($tarifa);
                $em->persist($costotarifas);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La pauta ' . $costotarifas->getIdCostoTarifas() . ' ha sido agregada con éxito.'
                        );
                
                return $this->redirect($this->generateUrl('showPautas'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
        'errors' => $errors,
    ));
            endif;
        endif;
        
        return $this->render('PautasBundle::insertPautas.html.twig', array('form' => $form->createView()));
    }

    public function showAction($page, $cl = false, Request $request) {  
        $user = $this->container->get('security.context')->getToken()->getUser();

        $session = $this->get('session');
        
        if($request->request->get('pautascant') != null):
            $session->set('pautascant', $request->request->get('pautascant'));
        endif;

        if($cl == true):
            $session->set('id_tarifa_pautas', '');
            $session->set('descripcion', '');
        endif;
        
        $defaults = array();
        if($session->get('id_tarifa_pautas')):
            $defaults['id_tarifa_pautas'] = $session->get('id_tarifa_pautas');
        endif;
        if($session->get('descripcion')):
            $defaults['descripcion'] = $session->get('descripcion');
        endif;
        
        $pautas = new Pautas();
        $formType = new PautasFilterType($user->getRoles(), $user->getIdAgencia()->getIdCiudad(), $this->getDoctrine()->getManager(), $defaults);
        $form = $this->createForm($formType, $pautas);

        $repository = $this->getDoctrine()
                        ->getRepository('PautasBundle:Pautas');

        $form->handleRequest($request);
        $data = $form->getData();
        
        if($data->getIdTarifa()):
            $session->set('id_tarifa_pautas', $data->getIdTarifa()->getIdTarifa());
        else:
            if($form->get('id_tarifa')->getViewData() == 'null'): // valor cuando se vacía el select
                $session->set('id_tarifa_pautas', '');
            endif;
        endif;
        if($form->get('descripcion')->getData()):
            $session->set('descripcion', $form->get('descripcion')->getData());
        else:
            $session->set('descripcion', '');
        endif;

        if(!$session->get('id_tarifa_pautas') && !$session->get('descripcion')):
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $query = $repository->createQueryBuilder('p')
                            ->getQuery();

                $pautasD = $query->getResult();
            else:
                $query = $repository->createQueryBuilder('p')
                            ->leftJoin('p.id_tarifa', 't')
                            ->Join('t.id_emisora', 'e')
                            ->where('e.id_ciudad = :id_ciudad')
                            ->setParameter('id_ciudad', $user->getIdAgencia()->getIdCiudad())
                            ->getQuery();

                $pautasD = $query->getResult();
            endif;
        endif;

        if($session->get('id_tarifa_pautas') || $session->get('descripcion')):
            $em = $this->getDoctrine()->getManager();
            $querystring = 'SELECT p FROM PautasBundle:Pautas p ';

            if(!in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $querystring .= 'JOIN p.id_tarifa t LEFT JOIN t.id_emisora e ';
            endif;
            
            if($session->get('descripcion')):
                $querystring .= 'JOIN p.id_tipo_mencion m ';
            endif;

            if($session->get('id_tarifa_pautas') || $session->get('descripcion')):
                $querystring .= 'WHERE ';
            endif;

            if($session->get('id_tarifa_pautas')):
                $querystring .= 'p.id_tarifa = ' . $session->get('id_tarifa_pautas');

                if(!in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || $session->get('descripcion')):
                    $querystring .= 'AND  ';
                endif;
            endif;
            
            if($session->get('descripcion')):
                $descripc = str_replace(' menciones', '', $session->get('descripcion'));
                $descripc2 = str_replace(' de', '', $descripc);
                $descripc3 = str_replace(' segundos', '', $descripc2);
                $descripc4 = str_replace(' por', '', $descripc3);
                $querystring .= "CONCAT(m.nro_menciones, ' ', p.duracion, ' ', p.costo) LIKE '%" . $descripc4 . "%' ";
                
                if(!in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                    $querystring .= 'AND  ';
                endif;
            endif;

            if(!in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $querystring .= 'e.id_ciudad = ' . $user->getIdAgencia()->getIdCiudad()->getId() . ' ';
            endif;

            $query = $em->createQuery($querystring);
            $pautasD = $query->getResult();
        else:
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $query = $repository->createQueryBuilder('p')
                            ->getQuery();

                $pautasD = $query->getResult();
            else:
                $query = $repository->createQueryBuilder('p')
                            ->leftJoin('p.id_tarifa', 't')
                            ->Join('t.id_emisora', 'e')
                            ->where('e.id_ciudad = :id_ciudad')
                            ->setParameter('id_ciudad', $user->getIdAgencia()->getIdCiudad())
                            ->getQuery();

                $pautasD = $query->getResult();
            endif;
        endif;

        if(!$pautasD):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Costo de tarifas', 'Restablecer' => $this->generateUrl('showPautas') . '/1/1'));
        endif;
        
        $adapter = new ArrayAdapter($pautasD);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('pautascant')));

        return $this->render('PautasBundle::showPautas.html.twig', array('form' => $form->createView(), 'costotarifas' => $pautasD, 'pager' => $pager));
    }
    
    public function showOneAction($id, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            if($id2 != 'yes'):
                $pauta = $this->getDoctrine()
                            ->getRepository('PautasBundle:Pautas')
                            ->findOneBy(array('id_costotarifas' => $id2));
            endif;
        endforeach;
        
        $formType = new EditCostoTarifasType($user->getIdAgencia()->getIdCiudad(), $user->getRoles());
        $form = $this->createForm($formType, $pauta);
        
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($pauta);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La pauta ' . $pauta->getIdCostoTarifas() . ' ha sido editada con éxito.'
                        );

                return $this->redirect($this->generateUrl('showPautas'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
                    'errors' => $errors,
                ));
            endif;
        endif;
        
        return $this->render('PautasBundle::showPauta.html.twig', array('form' => $form->createView(), 'pauta' => $pauta));
    }

    public function deleteAction($id) {
    	$id2 = explode(',', $id);
    	foreach($id2 as $id2):
    		// 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $pautas = $em->getRepository('PautasBundle:Pautas')->find($id2);
                
                $em->remove($pautas);
                try {
                    $em->flush();
                
                    $this->get('session')->getFlashBag()->add(
                        'notice', 'La pauta ' . $id2 . ' ha sido borrada con éxito.'
                    );
                } catch (\Doctrine\DBAL\DBALException $ex) {
                    $this->get('session')->getFlashBag()->add(
                        'error', 'La pauta ' . $id2 . ' no ha podido ser borrada. Para poder hacerlo, por favor elimine las ordenes de publicidad de esta pauta.'
                    );
                }
                
                $this->container->get('doctrine')->resetEntityManager();
            endif;
        endforeach;

        return $this->redirect($this->generateUrl('showPautas'));
    }
}

?>
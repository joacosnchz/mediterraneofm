<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DSNEmpresas\Tarifas\TarifasBundle\Controller;

# Funciones kernel de symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
# Entities
use DSNEmpresas\Tarifas\TarifasBundle\Entity\Tarifas;
# Types
use DSNEmpresas\Tarifas\TarifasBundle\Form\InsertTarifasType;
use DSNEmpresas\Tarifas\TarifasBundle\Form\EditTarifasType;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;

class TarifasController extends Controller {
    
    public function insertAction(Request $request) {
        # Objeto que modifico
        $tarifas = new Tarifas();
        $user = $this->container->get('security.context')->getToken()->getUser();
        # Formulario que modifico
        $formType = new InsertTarifasType($user->getIdAgencia()->getIdCiudad(), $user->getRoles());
        $form = $this->createForm($formType, $tarifas);

        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            if(!$this->formValid($form)):
                return $this->render('TarifasBundle::insertTarifas.html.twig', array('form' => $form->createView(), 'errors' => array(0 => array('message' => 'Por favor, complete correctamente las fechas.'))));
            endif;
            $validator = $this->get('validator');
            $errors = $validator->validate($tarifas);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->persist($tarifas);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La tarifa ' . $tarifas->getIdTarifa() . ' ha sido agregada con éxito.'
                        );
                
                return $this->redirect($this->generateUrl('showTarifas'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
        'errors' => $errors,
    ));
            endif;
        endif;
        
        return $this->render('TarifasBundle::insertTarifas.html.twig', array('form' => $form->createView()));
    }
    
    /**
     *
     * @Route("/tarifas/show/{page}", defaults={"page"=1}, name="showTarifas")
     * 
     */
    public function showAction($page, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
            $tarifas = $this->getDoctrine()
                    ->getRepository('TarifasBundle:Tarifas')
                    ->findBy(array(), array('fecha_hasta' => 'ASC'));
        else:
            $repository = $this->getDoctrine()
                    ->getRepository('TarifasBundle:Tarifas');

            $query = $repository->createQueryBuilder('t')
                                ->leftJoin('t.id_emisora', 'e')
                                ->where('e.id_ciudad = :id_ciudad')
                                ->setParameter('id_ciudad', $user->getIdAgencia()->getIdCiudad())
                                ->getQuery();

            $tarifas = $query->getResult();
        endif;
        
        $session = $this->get('session');
        
        if($request->request->get('Tarifas') != null):
            $session->set('Tarifas', $request->request->get('Tarifas'));
        endif;
        
        $emisoras = $this->getDoctrine()
                ->getRepository('EmisorasBundle:Emisoras')
                ->findAll();
         
        if (!$tarifas):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Tarifas'));
        endif;
        
        $adapter = new ArrayAdapter($tarifas);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('Tarifas')));

        return $this->render('TarifasBundle::showTarifas.html.twig', array('tarifas' => $tarifas, 'emisoras' => $emisoras, 'pager' => $pager));
    }
    
    public function showOneAction($nombre, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $nombre2 = explode(',', $nombre);
        foreach($nombre2 as $id):
            if($id != 'yes'):
                $tarifa = $this->getDoctrine()
                        ->getRepository('TarifasBundle:Tarifas')
                        ->find($id);
            endif;
        endforeach;
        
        $formType = new EditTarifasType($user->getIdAgencia()->getIdCiudad(), $user->getRoles());
        $form = $this->createForm($formType, $tarifa);
        /* $validator = $this->get('validator');
        $errors = $validator->validate($tarifa); */        
        
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            if(!$this->formValid($form)):
                return $this->render('TarifasBundle::showTarifa.html.twig', array('form' => $form->createView(), 'tarifa' => $tarifa, 'errors' => array(0 => array('message' => 'Por favor, complete correctamente las fechas.'))));
            endif;
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La tarifa ' . $tarifa->getIdTarifa() . ' ha sido editada con éxito.'
                        );

                return $this->redirect($this->generateUrl('showTarifas'));
            else:            
                return $this->render('TarifasBundle::showTarifa.html.twig', array('form' => $form->createView(), 'tarifa' => $tarifa));
            endif;
        endif;
        
        return $this->render('TarifasBundle::showTarifa.html.twig', array('form' => $form->createView(), 'tarifa' => $tarifa));
    }
    
    private function formValid($fecha) {
        $fechas = array('fecha_desde', 'fecha_hasta');
            
        $datePattern = '/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{1,4}$/';
            
        foreach($fechas as $strfecha):
            if(!$fecha->get($strfecha)->getData()):
                return false;
            endif;
        
            if(!preg_match($datePattern, $fecha->get($strfecha)->getData()->format('d-m-Y'))):
                return false;
            endif;
        endforeach;
        
        return true;
    }
    
    public function deleteAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $tarifas = $em->getRepository('TarifasBundle:Tarifas')->find($id2);

                $em->remove($tarifas);
                try {
                    $em->flush();
                    
                    $this->get('session')->getFlashBag()->add(
                        'notice', 'La tarifa "' . $tarifas->getNombre() . '" ha sido borrada con éxito.'
                    );
                } catch(\Doctrine\DBAL\DBALException $ex) { // Tiene pautas asociadas, violación de integridad
                    $this->get('session')->getFlashBag()->add(
                        'error', 'La tarifa "' . $tarifas->getNombre() . '" no se ha podido borrar. Para hacerlo, por favor elimine todas las pautas de esta tarifa.'
                    );
                }
                
                $this->container->get('doctrine')->resetEntityManager();
            endif;
        endforeach;

        return $this->redirect($this->generateUrl('showTarifas'));
    }
    
    public function showTarifarioPdfAction($id) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $ids_tarifas = explode(',', $id);
        foreach($ids_tarifas as $id_tar):
            $pautas[$id_tar] = $this->getDoctrine()
                            ->getRepository('PautasBundle:Pautas')
                            ->findBy(array('id_tarifa' => $id_tar), array('id_tarifa' => 'ASC'));
        
            $tarifas[] = $this->getDoctrine()
                            ->getRepository('TarifasBundle:Tarifas')
                            ->findOneBy(array('id_tarifa' => $id_tar), array('id_tarifa' => 'ASC'));
        endforeach;
        
        $code = base64_encode($user->getIdAgencia()->getIdAgencia().date('d-m-Y'));
        
        $web_dir = str_replace('web/', 'web', $_SERVER['DOCUMENT_ROOT']);
        
        $html = $this->renderView('TarifasBundle::showTarifarioPdf.html.twig', array('pautas' => $pautas, 'tarifas' => $tarifas, 'membrete' => $user->getIdAgencia()->getPathMembrete(), 'web_dir' => $web_dir));
        
        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => "filename='nocobradas" . $code . ".pdf'"
            )
        );
        
        /* exec("echo '" . $html . "' | php ../vendor/dompdf/dompdf.php - -f 'bundles/tarifas/tarifario" . $code . ".pdf'");
        
        $pdf = $this->get('templating.helper.assets')
            ->getUrl("bundles/tarifas/tarifario$code.pdf");
        
        return $this->redirect($pdf); */
    }
}

?>
<?php

namespace DSNEmpresas\Facturas\FacturasBundle\Controller;

# Funciones del kernel de Symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;
# Entities
use MediterraneoFM\MediterraneoFMBundle\Entity\Facturas;
# Types
use MediterraneoFM\MediterraneoFMBundle\Form\InsertFacturasType;

class FacturasController extends Controller {
    var $p_total = '';
    var $total;
    var $cantarray = array();
    var $descarray = array();
    var $puarray = array();
    var $ptarray = array();
    
    # Alta de Facturas, insert en tabla Facturas.
    public function insertAction(Request $request) {
        $facturas = new Facturas();
        #$programa_mencion = new ProgramaMencion();
        $formType = new InsertFacturasType();
        $form = $this->createForm($formType, $facturas);
        
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();
            $this->calculate($data);
            $this->arrays($data);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $facturas->setPTotal($this->p_total);
                $facturas->setTotal($this->total);
                $em->persist($facturas);
                $em->flush();
                
                return $this->render('FacturasBundle::printFacturas.html.twig', array('data' => $data, 'cantarray' => $this->cantarray, 'descarray' => $this->descarray, 'puarray' => $this->puarray, 'ptarray' => $this->ptarray));
                #return $this->redirect($this->generateUrl('insertFacturas'));
            endif;
        endif;
        
        return $this->render('FacturasBundle::insertFacturas.html.twig', array('form' => $form->createView()));
    }
    
    public function calculate($data) {
        $p_unitario = explode(',', $data->getPUnitario());
        $cantidad = explode(',', $data->getCantidad());
        
        /* echo '<pre>';
        print_r($cantidad);
        echo '<hr>';
        print_r($p_unitario);
        echo '</pre>';
        die(); */
        
        $p_total = array_combine($cantidad, $p_unitario);
        
        foreach($p_total as $key => $value):
                $this->ptarray[] = $key * $value;
                $this->p_total .=  $key * $value . ', ';
        endforeach;
        
        foreach($this->ptarray as $pt) {
            $this->total += $pt;
        }
    }
    
    public function arrays($data) {
        $this->cantarray = explode(',', $data->getCantidad());
        $this->descarray = explode(',', $data->getDescripcion());
        $this->puarray = explode(',', $data->getPUnitario());
    }
    
    /**
     *
     * @Route("/recibos/show/{page}", defaults={"page"=1}, name="showRecibos")
     * 
     */
    public function showAction($page, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
            $facturas = $this->getDoctrine()
                    ->getRepository('MediterraneoFMBundle:Facturas')
                    ->findAll();
        else:
            $facturas = $this->getDoctrine()
                    ->getRepository('MediterraneoFMBundle:Facturas')
                    ->findAll(); # Modificar al reacer el modulo
        endif;
        
        $session = $this->get('session');
        
        if($request->request->get('Facturas') != null):
            $session->set('Facturas', $request->request->get('Facturas'));
        endif;
         
        if (!$facturas):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Facturas'));
        endif;
        
        $adapter = new ArrayAdapter($facturas);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('Facturas')));

        return $this->render('FacturasBundle::showFacturas.html.twig', array('facturas' => $facturas, 'pager' => $pager));
    }
    
    public function showOneAction($nombre) {
        $name = str_replace('-', ' ', $nombre);
        
        $factura = $this->getDoctrine()
                ->getRepository('MediterraneoFMBundle:Facturas')
                ->findOneBy(array('n_factura' => $name));
         
        if (!$factura):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Facturas'));
        endif;
        
        return $this->render('FacturasBundle::showFactura.html.twig', array('factura' => $factura));
    }
    
    public function deleteAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
        	// 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $facturas = $em->getRepository('MediterraneoFMBundle:Facturas')->findOneBy(array('id' => $id2));

                $em->remove($facturas);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La factura ' . $id2 . ' ha sido borrada con éxito.'
                );
            endif;
        endforeach;

        return $this->redirect($this->generateUrl('showFacturas'));
    }
}

?>
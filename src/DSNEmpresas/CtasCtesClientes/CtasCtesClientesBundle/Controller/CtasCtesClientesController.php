<?php
namespace DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Controller;

# Funciones del kernel de Symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
# Entities
use DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenPub;
use DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Entity\CtasCtesClientes;
use DSNEmpresas\Recibos\RecibosBundle\Entity\Recibos;
# Types
use DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Form\ClientesFilterType;
use DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Form\MovCtaCteClienteType;
# Controllers
use MediterraneoFM\MediterraneoFMBundle\Controller\TransformController;

class CtasCtesClientesController extends Controller {

    public function showAction(Request $request, $post = false, $fecha_post_desde, $fecha_post_hasta, $cliente_post) {    	
    	/* Calculo de total a pagar (form jquery) */
    	$ids = $request->request->get('dat');
    	$ids2 = $request->request->get('dat2');
    	$ids3 = $request->request->get('dat3');
    	if($ids):
            $tot = 0;
            if(is_array($ids)):
                foreach($ids as $id):
                    if($id != 'yes'):
                        $tot = $tot + $id;
                    endif;
                endforeach;
            else:
                $ids2 = explode(',', $ids);
                foreach($ids2 as $id):
                    if($id != 'yes'):
                        $tot = $tot + $id;
                    endif;
                endforeach;
            endif;

            $bandera = 0;
            if(is_array($ids2)):
                $strids2 = implode(',', $ids2);
                foreach($ids2 as $i):
                    $ord = $this->getDoctrine()
                                ->getRepository('OrdenPubBundle:OrdenPub')
                                ->find($i);
                    if($ord->getPagado() == 1):
                        $bandera = 1;
                    endif;
                endforeach;
                if($bandera == 1):
                    die('Solo se pueden generar pagos de ordenes de publicidad.');
                endif;
            else:
                $strids2 = $ids2;
            endif;

            if(is_array($ids3)):
                $strids3 = implode(',', $ids3);
            else:
                $strids3 = $ids3;
            endif;

            $tot2 = strval($tot);
            die($tot2 . '-' . $strids2 . '-' . $strids3);
        endif;
        /* FIN Calculo de total a pagar */

        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        $ctactecliente = new CtasCtesClientes();
        $formType = new ClientesFilterType($user->getIdAgencia(), $user->getRoles(), $em, $cliente_post);
        $form = $this->createForm($formType, $ctactecliente);
        
        $tiposdocs = $this->getDoctrine()
                        ->getRepository('MediterraneoFMBundle:TiposDocumentos')
                        ->findAll();
        
        /* Error de fecha no valida */
        $e = isset($_GET['e']) ? $_GET['e'] : '';
        $pattern = '[^([0-9]){1}$]';

        $errors = array();
        if(preg_match($pattern, $e)):
            if(isset($e)):
                if($e == 1):
                    $errors = array(0 => array('message' => 'Por favor, ingrese correctamente la fecha.'));
                endif;	
            endif;
        endif;
        /* EOF fecha no valida */
		
        if($post == true):
            $saldos = $this->calculateSaldos($cliente_post, $fecha_post_desde, $fecha_post_hasta);

            $client = $this->getDoctrine()
                        ->getRepository('ClientesBundle:Clientes')
                        ->find($cliente_post);
            
            if($errors):
                return $this->render('CtasCtesClientesBundle::showCtasCtes.html.twig', array('form' => $form->createView(), 'ctacte' => $saldos['ctacte'], 'saldo' => $saldos['saldo'], 'ctacteR' => $saldos['ctacteR'], 'saldoanterior' => $saldos['saldoanterior'], 'cliente' => $client, 'fecha_desde' => $fecha_post_desde, 'fecha_hasta' => $fecha_post_hasta, 'tiposdocs' => $tiposdocs, 'post' => $post, 'errors' => $errors));
            else:
                return $this->render('CtasCtesClientesBundle::showCtasCtes.html.twig', array('form' => $form->createView(), 'ctacte' => $saldos['ctacte'], 'saldo' => $saldos['saldo'], 'ctacteR' => $saldos['ctacteR'], 'saldoanterior' => $saldos['saldoanterior'], 'cliente' => $client, 'fecha_desde' => $fecha_post_desde, 'fecha_hasta' => $fecha_post_hasta, 'tiposdocs' => $tiposdocs, 'post' => $post));
            endif;
        endif;

        if($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($ctactecliente);
            if(!$this->validDates($form->get('fecha'))):
                return $this->render('CtasCtesClientesBundle::showCtasCtes.html.twig', array('form' => $form->createView(), 'errors' => array(0 => array('message' => 'Por favor, ingrese correctamente las fechas.')), 'ctacte' => array(), 'tiposdocs' => array(), 'post' => $post));
            endif;
            /* $valid = $this->container->get('custom.validation');
            $valid->validate($form); */
            
            if($form->isValid()):
                $cliente = $data->getIdCliente();
                $fecha = $data->getFecha();
                
                $saldos = $this->calculateSaldos($cliente, $fecha['fecha_desde']->format('d-m-Y'), $fecha['fecha_hasta']->format('d-m-Y'));
                               
            	return $this->render('CtasCtesClientesBundle::showCtasCtes.html.twig', array('form' => $form->createView(), 'ctacte' => $saldos['ctacte'], 'ctacteR' => $saldos['ctacteR'], 'saldo' => $saldos['saldo'], 'cliente' => $cliente, 'fecha_desde' => $fecha['fecha_desde'], 'fecha_hasta' => $fecha['fecha_hasta'], 'saldoanterior' => $saldos['saldoanterior'] , 'tiposdocs' => $tiposdocs, 'post' => $post));
            else:
                return $this->render('CtasCtesClientesBundle::showCtasCtes.html.twig', array('form' => $form->createView(), 'fecha_desde' => $fecha['fecha_desde'], 'fecha_hasta' => $fecha['fecha_hasta'], 'tiposdocs' => $tiposdocs, 'post' => $post, 'errors' => $errors, 'ctacte' => array(), 'ctacteR' => array(), 'saldo' => array(), 'cliente' => array()));
            endif;
        endif;
		
        return $this->render('CtasCtesClientesBundle::showCtasCtes.html.twig', array('form' => $form->createView(), 'ctacte' => array(), 'tiposdocs' => array(), 'post' => $post));
    }
    
    private function validDates($fecha) {
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
    
    public function insertAction($id_cliente, $fecha_desde, $fecha_hasta, Request $request) {
    	/* Autocompletado del concepto del pago */
    	$id = $request->request->get('id_tipodoc');
    	if($id):
            $tipodoc = $this->getDoctrine()
                            ->getRepository('MediterraneoFMBundle:TiposDocumentos')
                            ->find($id);

            if($tipodoc):
                die($tipodoc->getDescripcion());
            endif;
    	endif;
    	/* FIN Autocompletado */

    	$user = $this->container->get('security.context')->getToken()->getUser();

    	if($request->request->get('hidden2') != null): // ids de los documentos seleccionados
            $id_ordenes = $request->request->get('hidden2');
            $id_ordenes2 = explode(',', $id_ordenes);
    	endif;
        
        $clte = $this->getDoctrine()
                    ->getRepository('ClientesBundle:Clientes')
                    ->find($id_cliente); /* Cliente del cual se esta viendo la c/c */

        /* En la c/c solo se pueden generar pagos */
        $tipodoc = $this->getDoctrine()
                        ->getRepository('MediterraneoFMBundle:TiposDocumentos')
                        ->findBy(array('tipoMovimiento' => 'H'));
				
    	$ctactecliente = new CtasCtesClientes();
    	$formType = new MovCtaCteClienteType($user->getIdAgencia(), $clte, $tipodoc);
        $form = $this->createForm($formType, $ctactecliente);

        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            if(!$form->get('fecha')->getData()):
                return $this->redirect($this->generateUrl('showCtasCtes', array('post' => true, 'fecha_post_desde' => $fecha_desde, 'fecha_post_hasta' => $fecha_hasta, 'cliente_post' => $clte, 'errors' => array(), 'e' => true)));
            endif;

            if ($form->isValid()):                
                $em = $this->getDoctrine()->getManager();

                foreach($id_ordenes2 as $id2):                
                    $ord_pub = $this->getDoctrine()
                                    ->getRepository('OrdenPubBundle:OrdenPub')
                                    ->find($id2);
                    $ord_pub->setPagado(1);
                    $em->flush();
                    
                    $total = $this->getTotalOrden($ord_pub);
                    
                    $recibos = new Recibos();
                    $recibos->setNroRecibo(1); /* nro 1, despues se modifica una vez que tengo el id del recibo ingresado */
                    $recibos->setFecha(new \DateTime($form->get('fecha')->getData()->format('d-m-Y')));
                    $recibos->setIdOrdenPub($ord_pub);
                    $recibos->setImporte($this->transformLetras($form->get('haber')->getData()));
                    $recibos->setConcepto($form->get('concepto')->getData());
                    #$recibos->setTotal($form->get('haber')->getData());
                    $recibos->setTotal($total);
                    $em->persist($recibos);
                    $em->flush();
                    
                    /* Modificación de nro de recibo ingresado */
                    /* $recibos contiene el objeto del recibo recien ingresado */
                    $recibos->setNroRecibo($user->getIdAgencia() . $recibos->getId());
                    $em->flush();
                endforeach;

                return $this->redirect($this->generateUrl('showCtasCtes', array('post' => true, 'fecha_post_desde' => $fecha_desde, 'fecha_post_hasta' => $fecha_hasta, 'cliente_post' => $clte, 'errors' => array())));
            endif;
        endif;
		
        return $this->render('CtasCtesClientesBundle::insertCtaCte.html.twig', array('form' => $form->createView(), 'id_cliente' => $clte, 'fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta));
    }
    
    private function getTotalOrden($orden) {
        $ordenTarifas = $this->getDoctrine()
                            ->getRepository('OrdenPubBundle:OrdenTarifas')
                            ->findBy(array('id_ordenpub' => $orden->getIdOrdenPub()));
        
        $total = 0;
        foreach($ordenTarifas as $tarifa) {
            $total += $tarifa->getTotal();
        }
        
        return $total;
    }

    public function returnDescripcionAction(Request $request) {
    	$id = $request->request->get('id_tipodoc');

    	if($id):
            $tipodoc = $this->getDoctrine()
                            ->getRepository('MediterraneoFMBundle:TiposDocumentos')
                            ->find($id);

            if($tipodoc):
                    die($tipodoc->getDescripcion());
            endif;
    	endif;
    }

    protected function transformLetras($valor) {
        $transform = new TransformController();
        return $transform->traducir($valor);
    }

    public function saldoClienteAction($id_cliente, $fecha_desde, $fecha_hasta) {
        $saldos = $this->calculateSaldos($id_cliente, $fecha_desde, $fecha_hasta);

        return new Response($saldos['saldo']);
    }
    
    private function calculateSaldos($cliente, $fecha_desde, $fecha_hasta) {
        $saldos = array();
        
        $repository = $this->getDoctrine()
                        ->getRepository('OrdenPubBundle:OrdenPub');
        
        $query = $repository->createQueryBuilder('o')
                    ->where('o.idCliente = :idCliente')
                    ->andWhere('o.estado = :estado')
                    // ->andWhere('o.liquidado = :liquidado')
                    ->andWhere('o.fecha >= :fechaDesde')
                    ->andWhere('o.fecha <= :fechaHasta')
                    ->setParameters(array(
                        'idCliente' => $cliente,
                        'estado' => 1,
                        // 'liquidado' => 0,
                        'fechaDesde' => new \DateTime($fecha_desde),
                        'fechaHasta' => new \DateTime($fecha_hasta)
                    ))
                    ->orderBy('o.fecha', 'ASC')
                    ->getQuery()
                ;
        
        $saldos['ctacte'] = $query->getResult();
        
        $query2 = $repository->createQueryBuilder('o')
                    ->where('o.idCliente = :idCliente')
                    ->andWhere('o.estado = :estado')
                    ->andWhere('o.liquidado = :liquidado')
                    ->andWhere('o.fecha < :fechaDesde')
                    ->setParameters(array(
                        'idCliente' => $cliente,
                        'estado' => 1,
                        'liquidado' => 0,
                        'fechaDesde' => new \DateTime($fecha_desde)
                    ))
                    ->getQuery();
        
        $saldos['ctacte2'] = $query2->getResult();
        
        $repository2 = $this->getDoctrine()
                        ->getRepository('RecibosBundle:Recibos');
        
        $query3 = $repository2->createQueryBuilder('r')
                    ->leftJoin('r.idOrdenPub', 'o')
                    ->where('o.idCliente = :idCliente')
                    ->andWhere('o.estado = :estado')
                    ->andWhere('r.fecha >= :fechaDesde')
                    ->andWhere('r.fecha <= :fechaHasta')
                    ->setParameters(array(
                        'idCliente' => $cliente,
                        'estado' => 1,
                        'fechaDesde' => new \DateTime($fecha_desde),
                        'fechaHasta' => new \DateTime($fecha_hasta)
                    ))
                    ->orderBy('r.fecha', 'ASC')
                    ->getQuery();
        
        $saldos['ctacteR'] = $query3->getResult();
        
        $saldos['saldohoy'] = 0;
        if($saldos['ctacte']):
            foreach($saldos['ctacte'] as $orden):
                if($orden->getPagado() == 0):
                    $saldos['saldohoy'] += $orden->getTotal();
                endif;
            endforeach;
        endif;
        
        $saldos['saldoanterior'] = 0;
        if($saldos['ctacte2']):
            foreach($saldos['ctacte2'] as $orden):
                if($orden->getPagado() == 0):
                    $saldos['saldoanterior'] += $orden->getTotal();
                endif;
            endforeach;
        endif;
        
        foreach($saldos['ctacteR'] as $recibo):
            $saldos['ctacte'][] = $recibo;
        endforeach;
        
        /* Ordenamiento por fecha */
        $long = count($saldos['ctacte']);
        $orden = new OrdenPub();
        $recibo = new Recibos();
        for($c = 0;$c < $long-1;$c++) {
            for($k = $c+1;$k < $long;$k++) {
                $obj1 = $saldos['ctacte'][$c];
                $obj2 = $saldos['ctacte'][$k];
                $fecha = $obj1->getFecha()->format('d-m-Y'); // fecha de emision del primer objeto
                $fecha2 = $obj2->getFecha()->format('d-m-Y'); // fecha de emision del segundo objeto
                if(strtotime($fecha) > strtotime($fecha2)):
                    $saldos['ctacte'][$c] = $obj2;
                    $saldos['ctacte'][$k] = $obj1;
                endif;
            }
        }
        /* EOF ordenamiento por fecha */
        
        /* foreach($saldos['ctacte'] as $movimiento):
            if($movimiento instanceof $orden):
                echo 'orden<br>';
            endif;
            if($movimiento instanceof $recibo):
                echo 'recibo<br>';
            endif;
        endforeach; */
        
        $query4 = $repository2->createQueryBuilder('r')
                    ->leftJoin('r.idOrdenPub', 'o')
                    ->where('o.idCliente = :idCliente')
                    ->andWhere('r.fecha < :fechaDesde')
                    ->setParameters(array(
                        'idCliente' => $cliente,
                        'fechaDesde' => new \DateTime($fecha_desde)
                    ))
                    ->getQuery();
        
        $saldos['ctacte2R'] = $query4->getResult();
        
        /* if($saldos['ctacteR']):
            foreach($saldos['ctacteR'] as $recibo):
                $saldos['saldohoy'] -= $recibo->getTotal();
            endforeach;
        endif; */
        
        /* if($saldos['ctacte2R']):
            foreach($saldos['ctacte2'] as $recibo):
                $saldos['saldoanterior'] -= $recibo->getTotal();
            endforeach;
        endif; */
        
        $saldos['saldo'] = $saldos['saldoanterior'] + $saldos['saldohoy'];
                
        return $saldos;
    }

    public function createPdfAction($fecha_desde, $fecha_hasta, $id_cliente) {	
            $user = $this->container->get('security.context')->getToken()->getUser();

            $saldos = $this->calculateSaldos($id_cliente, $fecha_desde, $fecha_hasta);

            $cliente = $this->getDoctrine()
                            ->getRepository('ClientesBundle:Clientes')
                            ->find($id_cliente);

            $id_cliente = $cliente->getIdCliente();
            $code = base64_encode($id_cliente.date('d-m-Y H:i:s'));

            $web_dir = str_replace('web/', 'web', $_SERVER['DOCUMENT_ROOT']);

            $html = $this->renderView('CtasCtesClientesBundle::showCtasCtesPdf.html.twig', array('ctacte' => $saldos['ctacte'], 'ctacteR' => $saldos['ctacteR'], 'saldo' => $saldos['saldo'], 'cliente' => $cliente, 'fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta, 'saldoanterior' => $saldos['saldoanterior'], 'web_dir' => $web_dir, 'membrete' => $user->getIdAgencia()->getPathMembrete()));

            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => "filename='cta" . $code . ".pdf'"
                )
            );

            /* exec("echo '" . $html . "' | php ../vendor/dompdf/dompdf.php - -f 'bundles/ctasctesclientes/cta" . $code . ".pdf'");

            $pdf = $this->get('templating.helper.assets')
                ->getUrl("bundles/ctasctesclientes/cta$code.pdf");

            return $this->redirect($pdf); */
    }
    
    public function deleteMovAction($id, $fecha_desde, $fecha_hasta, $id_cliente) {
        $em = $this->getDoctrine()->getManager();
        
        $cliente = $this->getDoctrine()
                        ->getRepository('ClientesBundle:Clientes')
                        ->find($id_cliente);
        
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            if($id2 != 'yes'): // yes es el valor que envía el formulario al seleccionar todo
                $recibo = $this->getDoctrine()
                        ->getRepository('RecibosBundle:Recibos')
                        ->find($id2);
            
                if(!$recibo):
                    $this->get('session')->getFlashBag()->add(
                            'error', 'Disculpe, no se ha podido eliminar el movimiento debido a que solo se permiten eliminar pagos.'
                    ); # Los documentos permanentes no se pueden eliminar.
                
                    return $this->redirect($this->generateUrl('showCtasCtes', array('post' => true, 'fecha_post_desde' => $fecha_desde, 'fecha_post_hasta' => $fecha_hasta, 'cliente_post' => $cliente, 'errors' => array())));
                endif;
                
                $orden = $this->getDoctrine()
                            ->getRepository('OrdenPubBundle:OrdenPub')
                            ->find($recibo->getIdOrdenPub());
                $orden->setPagado(0);
                $em->flush();
                
                $em->remove($recibo);
                $em->flush();
                
                $this->get('session')->getFlashBag()->add(
                    'notice', 'El movimiento del cliente: ' . $cliente->getApellido() . ', ' . $cliente->getNombre() . ' ha sido eliminado con éxito. '
                );
            endif;
        endforeach;
        
        return $this->redirect($this->generateUrl('showCtasCtes', array('post' => true, 'fecha_post_desde' => $fecha_desde, 'fecha_post_hasta' => $fecha_hasta, 'cliente_post' => $cliente, 'errors' => array())));
    }

    public function saldosClientesAction() {
        $web_dir = str_replace('web/', 'web', $_SERVER['DOCUMENT_ROOT']);
        $user = $this->container->get('security.context')->getToken()->getUser();

        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
            $clientes = $this->getDoctrine()
                                ->getRepository('ClientesBundle:Clientes')
                                ->findAll();
        else:
            $clientes = $this->getDoctrine()
                                ->getRepository('ClientesBundle:Clientes')
                                ->findBy(array('id_agencia' => $user->getIdAgencia()));
        endif;

        $hoy = new \DateTime('now');
        foreach($clientes as $cliente):
            $saldos = $this->calculateSaldos($cliente->getIdCliente(), '01-' . $hoy->format('m-Y'), $hoy->format('d-m-Y'));
            $saldo = $saldos['saldo'];
            $cliente->setSaldo((float)$saldo);
        endforeach;

        for($i = 0;$i < count($clientes)-1;$i++)
            for($j = $i + 1;$j < count($clientes);$j++)
                if($clientes[$i]->getSaldo() < $clientes[$j]->getSaldo()):
                    $aux = clone $clientes[$i];
                    $clientes[$i] = $clientes[$j];
                    $clientes[$j] = $aux;
                endif;

        $html = $this->renderView('CtasCtesClientesBundle::saldosClientes.html.twig', array('clientes' => $clientes, 'web_dir' => $web_dir, 'membrete' => $user->getIdAgencia()->getPathMembrete()));

        return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => "filename='saldos.pdf'"
                )
            );
    }
}

?>
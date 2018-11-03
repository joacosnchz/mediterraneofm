<?php

namespace DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Controller;

# Funciones del kernel de symfomy
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
# Entidades
use DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Entity\Liquidaciones;
use MediterraneoFM\MediterraneoFMBundle\Entity\liquidacionesMovimientos;
# Formularios
use DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Form\InsertLiquidacionesType;
use DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Form\AgenciasFilterType;
use DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Form\LiquidacionesFilterType;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;

class CtasCtesAgenciasController extends Controller {

    public function insertAction(Request $request) {
    	$liquidaciones = new Liquidaciones();
    	$formType = new InsertLiquidacionesType();
    	$form = $this->createForm($formType, $liquidaciones);

    	$form->handleRequest($request);
        
        $err = $request->query->get('e'); // true si se envía error desde el insert de liquidaciones
        
        if(isset($err)):
            if(!preg_match('/^[0-1]{1}$/', $err)): // validar el $_GET['e']
                return $this->redirect($this->generateUrl('insertCtasCtesAgencias'));
            endif;
        endif;
        
        if($request->isMethod("POST") || $err):
            if(!$this->validDates($form->get('fecha')) || $err):
                return $this->render('CtasCtesAgenciasBundle::insertCtasCtesAgencias.html.twig', array('form' => $form->createView(), 'form2' => array(), 'ordenesp' => array(), 'errors' => array(0 => array('message' => 'Por favor, ingrese correctamente las fechas.'), 1 => array('message' => 'La fecha desde no puede ser mayor que fecha hasta.'))));
            endif;
        endif;

    	if($form->isValid()):
            $data = $form->getData();

            $liquidacionesM = new liquidacionesMovimientos();
            $formType2 = new AgenciasFilterType($liquidacionesM);
            $form2 = $this->createForm($formType2, $liquidaciones);

            if(in_array('OP', $data->getDocs())): # Si ordenes de publicidad está seleccionado en los docs. a incluir
        		$repository = $this->getDoctrine()
                        ->getRepository('OrdenPubBundle:OrdenPub');

                $ordenesp = $this->getOrdenesAPagar($repository, $data->getFecha()['fecha_desde'], $data->getFecha()['fecha_hasta'], $data->getIdAgencia(), $data->getParam());

                $ordenesp2 = $this->getOrdenesNoCobradas($repository, $data->getFecha()['fecha_desde'], $data->getFecha()['fecha_hasta'], $data->getIdAgencia());

                $valores = $this->calculate($data->getIdAgencia(), $ordenesp);

                $valores2 = $this->calculate($data->getIdAgencia(), $ordenesp2);                
            else:
                $ordenesp = array();
                $ordenesp2 = array();
                $valores = array();
                $valores2 = array();
            endif;

            return $this->render('CtasCtesAgenciasBundle::insertCtasCtesAgencias.html.twig', array('form' => $form->createView(), 'form2' => $form2->createView(), 'ordenesp' => $ordenesp, 'ordenesp2' => $ordenesp2, 'valores' => $valores, 'valores2' => $valores2, 'agencia' => $data->getIdAgencia(), 'fecha_desde' => $data->getFecha()['fecha_desde'], 'fecha_hasta' => $data->getFecha()['fecha_hasta'], 'param' => $data->getParam(), 'docs' => $data->getDocs()));
        endif;

        return $this->render('CtasCtesAgenciasBundle::insertCtasCtesAgencias.html.twig', array('form' => $form->createView(), 'form2' => array(), 'ordenesp' => array()));
    }
    
    private function validDates($fecha) {
        $fechas = array('fecha_desde', 'fecha_hasta');
            
        foreach($fechas as $strfecha):
            if(!$this->validDate($fecha->get($strfecha)->getData())):
                return false;
            endif;
        endforeach;
        
        if(strtotime($fecha->get('fecha_desde')->getData()->format('d-m-Y')) > strtotime($fecha->get('fecha_hasta')->getData()->format('d-m-Y'))):
            return false;
        endif;
        
        return true;
    }
    
    private function validDate($fecha) {
        $datePattern = '/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{1,4}$/';
        
        if(!$fecha):
            return false;
        endif;

        if(!preg_match($datePattern, $fecha->format('d-m-Y'))):
            return false;
        endif;
        
        return true;
    }

    public function showOneAction($id) {
        $id2 = explode(',', $id);
        foreach ($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $liquidacion = $this->getDoctrine()->getRepository('CtasCtesAgenciasBundle:Liquidaciones')->findOneBy(array('id' => $id2));
            endif;
        endforeach;
        
        $movs = $this->getDoctrine()->getRepository('MediterraneoFMBundle:liquidacionesMovimientos')->findBy(array('idLiquidacion' => $liquidacion));

        $movsData = $this->getMovsData($movs);
        
        return $this->render('CtasCtesAgenciasBundle::showLiquidacion.html.twig', array('liquidacion' => $liquidacion, 'movimientos' => $movs, 'movsData' => $movsData));
    }

    protected function getOrdenesAPagar($repository, $fecha_desde, $fecha_hasta, $id_agencia, $param) {
        $query = $repository->createQueryBuilder('op')
                ->where('op.estado = :estado')
                ->setParameter('estado', 1)
                ->andWhere('op.fecha >= :fecha_desde')
                ->setParameter('fecha_desde', $fecha_desde)
                ->andWhere('op.fecha <= :fecha_hasta')
                ->setParameter('fecha_hasta', $fecha_hasta)
                ->andWhere('op.id_agencia = :id_agencia')
                ->setParameter('id_agencia', $id_agencia);

        if(in_array('LI', $param)): # Si está seleccionada la opcion 'solo ordenes NO liquidadas'
            $query->andWhere('op.liquidado = :liquidado')
                ->setParameter('liquidado', 0);
        endif;

        if(in_array('CO', $param)): # Si está seleccionada la opcion 'solo ordenes cobradas'
            $query->andWhere('op.pagado = :pagado')
                ->setParameter('pagado', 1);
        endif;

        return $ordenesp = $query->getQuery()->getResult();
    }

    protected function getOrdenesNoCobradas($repository, $fecha_desde, $fecha_hasta, $id_agencia) {
        $query2 = $repository->createQueryBuilder('op')
                ->where('op.estado = :estado')
                ->setParameter('estado', 1)
                ->andWhere('op.fecha >= :fecha_desde')
                ->setParameter('fecha_desde', $fecha_desde)
                ->andWhere('op.fecha <= :fecha_hasta')
                ->setParameter('fecha_hasta', $fecha_hasta)
                ->andWhere('op.id_agencia = :id_agencia')
                ->setParameter('id_agencia', $id_agencia);

        /* if(in_array('LI', $param)): # Si está seleccionada la opcion 'solo ordenes no liquidadas'
            $query2->andWhere('op.liquidado = :liquidado')
                ->setParameter('liquidado', 0);
        endif; */

        #if(in_array('CO', $param)): # Si está seleccionada la opcion 'solo ordenes cobradas'
            $query2->andWhere('op.pagado = :pagado')
                ->setParameter('pagado', 0);
        #endif;

        return $ordenesp2 = $query2->getQuery()->getResult();
    }

    public function newMovAction(Request $request) {
        $liquidaciones = new Liquidaciones();
        $formType = new AgenciasFilterType();
        $form = $this->createForm($formType, $liquidaciones);
        
        $form->handleRequest($request);
        
        if($request->isMethod("POST")):
            if(!$this->validDate($form->get('fecha')->get('fecha_desde')->getData())):
                return $this->redirect($this->generateUrl('insertCtasCtesAgencias', array('e' => 1)));
            endif;
        endif;

        if($form->isValid()):
            $data = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $fechaDat = $data->getFecha();
            $liquidaciones->setFecha($fechaDat['fecha_desde']);
            $em->persist($liquidaciones);
            $em->flush();

            $comisiones = $request->request->get('comisiones');
            $importes = $request->request->get('importes');
            
            # Transformo las variables en arreglos
            $comisiones = explode(',', $comisiones);
            $comisiones = array_filter($comisiones);
            $importes = explode(',', $importes);
            $importes = array_filter($importes); #quito los elementos vacios del array
            foreach($importes as $importe): # Por cada movimiento se genera un registro en la tabla liquidacionesMov...
                $importe = explode('-', $importe);

                $repository = $this->getDoctrine()->getRepository('OrdenPubBundle:OrdenPub');

                $query = $repository->createQueryBuilder('o')
                            ->where('o.id_ordenpub = :id_ordenpub')
                            ->setParameter('id_ordenpub', $importe[0])
                            ->getQuery();

                $orden = $query->getSingleResult();

                $liquidacionesM = new liquidacionesMovimientos();
                $liquidacionesM->setIdOrdenpub($orden);
                $liquidacionesM->setImporteBase(floatval($importe[1]));
                $liquidacionesM->setIdLiquidacion($liquidaciones);
                foreach($comisiones as $comision):
                    $comision = explode('-', $comision);

                    if($comision[0] == $importe[0]):
                        $liquidacionesM->setPorcentaje(floatval($comision[1]));
                    endif;
                endforeach;

                $em->persist($liquidacionesM);
                $em->flush();

                $orden->setLiquidado(true); # Cambio el estado de la orden de publicidad a aliquidado
                $em->flush();
            endforeach;
            
            $this->get('session')->getFlashBag()->add(
                        'notice', 'La liquidacion ' . $liquidaciones->getId() . ' ha sido agregada con éxito.'
                    );

            return $this->redirect($this->generateUrl('showLiquidaciones'));
        endif;

        return $this->render('CtasCtesAgenciasBundle::insertCtasCtesAgencias.html.twig', array('form' => $form->createView(), 'form2' => array(), 'ordenesp' => array()));
    }

    protected function calculate($agencia, $ordenes) {
        if($agencia):
            $comisionObj = $agencia->getIdComision();
            if($comisionObj):
                $comision = $agencia->getIdComision()->getValor();
            endif;
        endif;

        $val['tot'] = 0;
        $val['subtot'] = 0;
        if($ordenes):
            foreach($ordenes as $orden):
                $val['comision'][$orden->getIdOrdenPub()] = $comision;

                $val['importe'][$orden->getIdOrdenPub()] = ($orden->getTotal() * $comision) / 100;

                $val['subtot'] += $orden->getTotal();

                $val['tot'] += $val['importe'][$orden->getIdOrdenPub()];
            endforeach;
        endif;

        return $val;
    }

    public function calcuAction(Request $request) {
        /* Estos valores solo están definidos cuando vienen por $.post con jquery */
        $com = $id_tarifa = $request->request->get('comision');
        $com2 = explode(',', $com); # Creo arreglo a partir de string separado por comas.
        $com3 = array_filter($com2); # Quito los elementos vacios del arreglo.
        foreach($com3 as $c4): # Ahora tengo elementos de la forma (id_ordenpub)-(comision)
            $c4 = explode('-', $c4); # Creo arreglo separando id_orden y comision
            $key = $c4[0];
            $value = $c4[1];

            $com5[$key] = $value; # Id_orden es el key del nuevo arreglo y la comision el valor
        endforeach;

        $subtot = $id_tarifa = $request->request->get('subtot');
        $subtot2 = explode(',', $subtot); # Creo arreglo a partir de string separado por comas.
        $subtot3 = array_filter($subtot2); # Quito los elementos vacios del arreglo.
        foreach($subtot3 as $s4): # Ahora tengo elementos de la forma (id_ordenpub)-(subtotal)
            $s4 = explode('-', $s4); # Creo arreglo separando id_orden y comision
            $key = $s4[0];
            $value = $s4[1];

            $subtot5[$key] = $value; # Id_orden es el key del nuevo arreglo y el subtotal el valor
        endforeach;
        /* Fin valores jquery */

        $val['tot'] = 0;
        if(isset($com5)):
            if(is_array($com5)):
                foreach($com5 as $key => $value):
                    $val['comision'][$key] = $value;

                    $val['importe'][$key] = ($subtot5[$key] * $value) / 100;

                    $val['tot'] += $val['importe'][$key];
                endforeach;
            endif;
        endif;

        die(json_encode($val));
    }

    public function showAction($page, $clear, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

    	$session = $this->get('session');
        
        if($request->request->get('Liquidaciones') != null):
            $session->set('Liquidaciones', $request->request->get('Liquidaciones'));
        endif;
        
        if($clear):
            $session->set('fechaDesde', '');
            $session->set('fechaHasta', '');
            $session->set('idAgencia', '');
        endif;
        
        $default = array('idAgencia' => '');
        if($session->get('idAgencia')):
            $default['idAgencia'] = $session->get('idAgencia');
        endif;
        
        $em = $this->getDoctrine()->getManager();
        $formType = new LiquidacionesFilterType($user->getRoles(), $default, $em);
        $form = $this->createForm($formType, null, array(
            'action' => $this->generateUrl('showLiquidaciones'),
            'method' => 'POST'
        ));
        
        $form->handleRequest($request);
        
        if($form->get('fecha')->getData()):
            $session->set('fechaDesde', $form->get('fecha')->getData());
        else:
            $session->set('fechaDesde', '');
        endif;
        if($form->get('fechaHasta')->getData()):
            $session->set('fechaHasta', $form->get('fechaHasta')->getData());
        else:
            $session->set('fechaHasta', '');
        endif;
        if($form->get('idAgencia')->getData()):
            $session->set('idAgencia', $form->get('idAgencia')->getData());
        else:
            $session->set('idAgencia', '');
        endif;
        
        $repository = $this->getDoctrine()->getRepository('CtasCtesAgenciasBundle:Liquidaciones');
        $liquidaciones = array();
        
        if(!$session->get('idAgencia') && !$session->get('fechaDesde') && !$session->get('fechaHasta')):
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $query = $repository->createQueryBuilder('l')->getQuery();
            else:
                $query = $repository->createQueryBuilder('l')
                    ->where('l.idAgencia = :idAgencia')
                    ->setParameter('idAgencia', $user->getIdAgencia()->getIdAgencia())
                    ->getQuery();
            endif;
            
            $liquidaciones = $query->getResult();
        endif;
        
        if($session->get('idAgencia') || $session->get('fechaDesde') || $session->get('fechaHasta')):
            $querystring = 'SELECT l FROM CtasCtesAgenciasBundle:Liquidaciones l WHERE ';
        
            if($session->get('fechaDesde')):
                $querystring .= 'l.fecha >= :fechaDesde ';
                
                if($session->get('fechaHasta')):
                    $querystring .= 'AND ';
                endif;
            endif;
            
            if($session->get('fechaHasta')):
                $querystring .= 'l.fecha <= :fechaHasta ';
            endif;
            
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                if($session->get('idAgencia')):
                    if($session->get('fechaDesde') || $session->get('fechaHasta')):
                        $querystring .= 'AND ';
                    endif;
                    
                    $querystring .= 'l.idAgencia = ' . $session->get('idAgencia') . ' ';
                endif;
            endif;
            
            if($session->get('fechaDesde') && $session->get('fechaHasta')):
                $query = $em->createQuery($querystring)->setParameters(array('fechaDesde' => $session->get('fechaDesde'), 'fechaHasta' => $session->get('fechaHasta')));
            elseif($session->get('fechaDesde') && !$session->get('fechaHasta')):
                $query = $em->createQuery($querystring)->setParameter('fechaDesde', $session->get('fechaDesde'));
            elseif(!$session->get('fechaDesde') && $session->get('fechaHasta')):
                $query = $em->createQuery($querystring)->setParameter('fechaHasta', $session->get('fechaHasta'));
            elseif(!$session->get('fechaDesde') && !$session->get('fechaHasta')):
                $query = $em->createQuery($querystring);
            else:
                $query = $em->createQuery($querystring);
            endif;
            
            $liquidaciones = $query->getResult();
        else:
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $query = $repository->createQueryBuilder('l')->getQuery();
            else:
                $query = $repository->createQueryBuilder('l')
                    ->where('l.idAgencia = :idAgencia')
                    ->setParameter('idAgencia', $user->getIdAgencia()->getIdAgencia())
                    ->getQuery();
            endif;
            
            $liquidaciones = $query->getResult();
        endif;

    	if(!$liquidaciones):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Liquidaciones', 'Nuevo' => $this->generateUrl('insertCtasCtesAgencias'), 'Restablecer' => $this->generateUrl('showLiquidaciones', array('page' => 1, 'clear' => 1))));
        endif;

    	$adapter = new ArrayAdapter($liquidaciones);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('Liquidaciones')));

        return $this->render('CtasCtesAgenciasBundle::showCtasCtesAgencias.html.twig', array('form' => $form->createView(), 'liquidaciones' => $liquidaciones, 'pager' => $pager));
    }

    public function deleteAction($id) {
    	$id2 = explode(',', $id);
    	$em = $this->getDoctrine()->getManager();
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $liquidacion = $this->getDoctrine()
                                ->getRepository('CtasCtesAgenciasBundle:Liquidaciones')
                                ->findOneBy(array('id' => $id2));
            
                if($liquidacion):
                    $liqMovimientos = $this->getDoctrine()
                                        ->getRepository('MediterraneoFMBundle:liquidacionesMovimientos')
                                        ->findBy(array('idLiquidacion' => $id2));

                    if($liqMovimientos):
                        foreach($liqMovimientos as $lm):
                            $orden = $lm->getIdOrdenPub();
                            $orden->setLiquidado(0);
                            $em->flush();
                        endforeach;
                    endif;

                    $em->remove($liquidacion);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add(
                            'notice', 'La liquidacion ' . $id2 . ' ha sido borrada con éxito.'
                    );
                    $em->clear();
                else:
                    $this->get('session')->getFlashBag()->add(
                        'error', 'No se ha podido eliminar la liquidación, porfavor intente nuevamente.'
                    );
                endif;
            endif;
        endforeach;
        
        return $this->redirect($this->generateUrl('showLiquidaciones'));
    }
    
    public function createPdfAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            if($id2 != 'yes'):
                $liquidacion = $this->getDoctrine()
                                    ->getRepository('CtasCtesAgenciasBundle:Liquidaciones')
                                    ->find($id2);
            endif;
        endforeach;
        
        $liquidacionMovimiento = $this->getDoctrine()
                                    ->getRepository('MediterraneoFMBundle:liquidacionesMovimientos')
                                    ->findBy(array('idLiquidacion' => $liquidacion->getId()));
        
        $id_agencia = $liquidacion->getIdAgencia()->getIdAgencia();
        
        $movsData = $this->getMovsData($liquidacionMovimiento);
        
        $code = base64_encode($id_agencia . date('d-m-Y H:i:s'));

        $web_dir = str_replace('web/', 'web', $_SERVER['DOCUMENT_ROOT']);
        
        $html = $this->renderView('CtasCtesAgenciasBundle::showCCAgenciasPdf.html.twig', array('liquidacion' => $liquidacion, 'movimientos' => $liquidacionMovimiento, 'web_dir' => $web_dir, 'movsData' => $movsData));
        
        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => "filename='nocobradas" . $code . ".pdf'"
            )
        );
    }
    
    private function getMovsDAta($movimientos) {
        $c = 0;
        $subTot = 0;
        foreach($movimientos as $movimiento):
            $fecha = $movimiento->getIdOrdenPub()->getFecha()->format('d-m-Y');
        
            if($c == 0):
                $fechaDesde = $fechaHasta = $fecha;
            endif;
            
            if(strtotime($fecha) < strtotime($fechaDesde)):
                $fechaDesde = $fecha;
            endif;
            
            if(strtotime($fecha) > strtotime($fechaHasta)):
                $fechaHasta = $fecha;
            endif;
            
            $subTot += $movimiento->getIdOrdenPub()->getTotal();
            
            $c++;
        endforeach;
        
        return array('fechaDesde' => $fechaDesde, 'fechaHasta' => $fechaHasta, 'subTot' =>$subTot);
    }
    
    public function createPdf2Action($ids_ord, $fecha_desde, $fecha_hasta) {
        #$format = $this->get('request')->get('_format');
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $ids_ord = urldecode($ids_ord);

        $ids = explode(',', $ids_ord);

        $repository = $this->getDoctrine()
                        ->getRepository('OrdenPubBundle:OrdenPub');

        /* Primero decimos where id= el primer item para despues hacer orwhere los demas que no estan cobrados */
        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
            $query = $repository->createQueryBuilder('op')->where("op.id_ordenpub = " . $ids[0]);
        else:
            $query = $repository->createQueryBuilder('op')->where("op.id_ordenpub = " . $ids[0])->andWhere('op.id_agencia = ' . $user->getIdAgencia());
        endif;

        if(is_array($ids)):
            foreach($ids as $id):
                $query->orWhere('op.id_ordenpub = ' . $id);
            endforeach;
        else:
            $query->orWhere('op.id_ordenpub = ' . $ids);
        endif;

        $ordenes = $query->getQuery()->getResult();

        $repository2 = $this->getDoctrine()->getRepository('OrdenPubBundle:OrdenTarifas');

        if($ordenes):
            $query2 = $repository2->createQueryBuilder('ot');
            $i = 0;
            foreach($ordenes as $orden):
                if($i == 0):
                    $query2->where('ot.id_ordenpub = ' . $orden);
                else:
                    $query2->orWhere('ot.id_ordenpub = ' . $orden);
                endif;

                $i++;
            endforeach;

            $ordenes_t = $query2->getQuery()->getResult();
        endif;

        $vencidas = array();
        if($ordenes_t):
            foreach($ordenes_t as $ot):
                $ahora = new \DateTime('now');
                if(strtotime($ahora->format('d-m-Y')) > strtotime($ot->getFechaHasta()->format('d-m-Y'))):
                    $vencidas[] = $ot->getIdOrdenPub()->getIdOrdenPub();
                endif;
            endforeach;
        endif;

        $code = base64_encode($user->getIdAgencia()->getIdAgencia().date('d-m-Y H:i:s'));

        $web_dir = str_replace('web/', 'web', $_SERVER['DOCUMENT_ROOT']);

        $html = $this->renderView('CtasCtesAgenciasBundle::showNC.html.twig', array('ordenes' => $ordenes, 'ordenes_t' => $ordenes_t, 'vencidas' => $vencidas, 'agencia' => $user->getIdAgencia(), 'fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta, 'web_dir' => $web_dir, 'membrete' => $user->getIdAgencia()->getPathMembrete()));
        
        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => "filename='nocobradas" . $code . ".pdf'"
            )
        );

        /* exec("echo '" . $html . "' | php ../vendor/dompdf/dompdf.php - -f 'bundles/ctasctesagencias/nocobrada" . $code . ".pdf'");

        $pdf = $this->get('templating.helper.assets')
            ->getUrl("bundles/ctasctesagencias/nocobrada$code.pdf");
        
        return $this->redirect($pdf); */
    }
}

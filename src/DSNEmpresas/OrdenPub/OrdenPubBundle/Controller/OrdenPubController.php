<?php

namespace DSNEmpresas\OrdenPub\OrdenPubBundle\Controller;

# Funciones kernel de symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;
# Controllers
use MediterraneoFM\MediterraneoFMBundle\Controller\TransformController;
# Entities
use DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenPub;
use DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenTarifas;
use MediterraneoFM\MediterraneoFMBundle\Entity\Pagares;
use DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Entity\CtasCtesClientes;
use DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\ordenesAnuladas;
use DSNEmpresas\System\SystemLogBundle\Entity\SystemLog;
# Types
use DSNEmpresas\OrdenPub\OrdenPubBundle\Form\InsertOrdenPubType;
use DSNEmpresas\OrdenPub\OrdenPubBundle\Form\InsertOrdenesAnuladasType;
use DSNEmpresas\OrdenPub\OrdenPubBundle\Form\RenovarOrdenPubType;
use DSNEmpresas\OrdenPub\OrdenPubBundle\Form\OrdenPubFilterType;

class OrdenPubController extends Controller {
    
    # Alta de tarifas, insert a la tabla tarifas.
    /* Aclaracion importante:
     * $ordenpub->getIdTarifa() te devuelve el arreglo de ids de PAUTAS o COSTOS TARIFAS, no los id de TARIFAS.
     */
    public function insertAction(Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        /* Seleccion de tarifas a partir de emisora (formulario jquery) */
        $id_emisora = $request->request->get('id_emisora');
        if($id_emisora):
            if($id_emisora == 0):
                return $this->render('OrdenPubBundle::tarifasSelect.html.twig', array('tarifas' => array()));
            else:
                $tarifas = $this->getDoctrine()
                                ->getRepository('TarifasBundle:Tarifas')
                                ->findBy(array('id_emisora' => $id_emisora));
            
                $tar = array();
                foreach($tarifas as $tarifa):
                    if(strtotime($tarifa->getFechaDesde()->format('d-m-Y')) < strtotime(date('d-m-Y')) && strtotime($tarifa->getFechaHasta()->format('d-m-Y')) >= strtotime(date('d-m-Y'))):
                        $tar[] = $tarifa;
                    endif;
                endforeach;
            
                if($tar):
                    return $this->render('OrdenPubBundle::tarifasSelect.html.twig', array('tarifas' => $tar));
                else:
                    return $this->render('OrdenPubBundle::tarifasSelect.html.twig', array('tarifas' => array()));
                endif;
            endif;
        endif;
        /* EOF seleccion de tarifas */

        /* Seleccion de pautas a partir de tarifas (formulario jquery) */
        $id_tarifa = $request->request->get('id_tarifa');
        if($id_tarifa):
            if($id_tarifa == 0):
                return $this->render('OrdenPubBundle::pautasSelect.html.twig', array('pautas' => array()));
            else:
                $pautas = $this->getDoctrine()
                            ->getRepository('PautasBundle:Pautas')
                            ->findBy(array('id_tarifa' => $id_tarifa));

            if($pautas):
                    # Render de las opciones (pautas)
                    return $this->render('OrdenPubBundle::pautasSelect.html.twig', array('pautas' => $pautas));
                else:
                    # Render de las opciones (pautas)
                    return $this->render('OrdenPubBundle::pautasSelect.html.twig', array('pautas' => array()));
                endif;
            endif;
        endif;
        /* FIN seleccion de pautas */

        /* Entidades necesarias para la orden y la generacion de tarifas */
        $ordenpub = new OrdenPub();
        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
            $emisoras = $this->getDoctrine()->getRepository('EmisorasBundle:Emisoras')->findAll();
            $clientes = $this->getDoctrine()->getRepository('ClientesBundle:Clientes')->findAll();
        else:
            $emisoras = $this->getDoctrine()->getRepository('EmisorasBundle:Emisoras')->findBy(array('id_ciudad' => $user->getIdAgencia()->getIdCiudad()));
            $clientes = $this->getDoctrine()
                        ->getRepository('ClientesBundle:Clientes')
                        ->findBy(array('id_agencia' => $user->getIdAgencia()->getIdAgencia()));
        endif;
        $tarifas = $this->getDoctrine()
                        ->getRepository('TarifasBundle:Tarifas')
                        ->findAll();
        $tarifasVig = array();
        $costotarifas = $this->getDoctrine()
                            ->getRepository('PautasBundle:Pautas')
                            ->findAll();
        /* Fin de las entidades */
        
        $em = $this->getDoctrine()->getManager();

        /* Formulario y su validación */
        $renovarRequest = $request->request->get('mediterraneofm_mediterraneofmbundle_renovarordenpubtype');
        if(isset($renovarRequest['renovar'])):
            if($renovarRequest['renovar'] == 1):
                $ordenvieja = $this->getDoctrine()
                                    ->getRepository('OrdenPubBundle:OrdenPub')
                                    ->findOneBy(array('id_ordenpub' => $renovarRequest['id_ordenvieja']));
                /* $ordenvieja->setEstado(0);
                $em->flush(); */
                $formType = new RenovarOrdenPubType($ordenvieja, $user->getIdAgencia(), $user->getRoles());
            endif;
        else:
            $formType = new InsertOrdenPubType($user->getIdAgencia(), $user->getRoles(), 'd-m-Y', false, array(
                'action' => $this->generateUrl('insertOrdenPub'),
                'method' => 'POST',
            ));
        endif;
        $form = $this->createForm($formType, $ordenpub);
        $validator = $this->get('validator');
        $errors = $validator->validate($ordenpub);
        /* Fin instancia de formulario */
        
        /* Mas entidades necesarias para la creación de la orden */
        if(!$clientes):
            return $this->render('OrdenPubBundle::insertOrdenPub.html.twig', array('form' => $form->createView(), 'errors' => array(0 => array('message' => 'No hay datos suficientes para crear una orden de publicidad.')), 'costotarifas' => $costotarifas, 'emisoras' => $emisoras, 'hidden' => 0, 'hidden2' => 3, 'tarifas' => $tarifas, 'tarifasVig' => $tarifasVig));
        endif;
        
        if($emisoras):
            foreach($emisoras as $emisora):
                $tmpTarifas = $this->getDoctrine()
                                ->getRepository('TarifasBundle:Tarifas')
                                ->findBy(array('id_emisora' => $emisora->getIdEmisora()));

                if($tmpTarifas):
                    foreach($tmpTarifas as $tarifa):
                        if($this->isVigente($tarifa->getFechaDesde()->format('d-m-Y'), $tarifa->getFechaHasta()->format('d-m-Y'))):
                            $tarifasVig[] = $tarifa;
                        endif;
                    endforeach;
                endif;
            endforeach;
        else:
            /* Control de existencia de emisoras */
            return $this->render('OrdenPubBundle::insertOrdenPub.html.twig', array('form' => $form->createView(), 'errors' => array(0 => array('message' => 'No hay datos suficientes para crear una orden de publicidad.')), 'costotarifas' => $costotarifas, 'emisoras' => $emisoras, 'hidden' => 0, 'hidden2' => 3, 'tarifas' => $tarifas, 'tarifasVig' => $tarifasVig));
            /* EOF control */
        endif;
        /* EOF entidades necesarias */
        
        /* El siguiente codigo solo se ejecuta si esta funcion esta procesando el formulario en vez de crearlo */
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();

            /* Como fecha tiene dato por defecto, debemos modificarlo 
                manualmente enviadolo como parametro nuevamente al formulario
                al ser modificado por el usuario. */
            $fecha = $form->get('fecha')->getData();
            /* EOF dato fecha */

            /* Con este parámetro del formulario controlaremos si 
               es la primera o la segunda vez que se envía el formulario
               (confirmado o no) */
            $confirmation = $form->get('confirmation')->getData();
            /* EOF dato de confirmacion */
            
            $idTarifaData = $form->get('id_tarifa')->getData();
            
            foreach($idTarifaData as $it):
                if($it):
                    if(is_object($it->getIdCostoTarifas()) && !is_integer($it->getIdCostoTarifas())):
                        return $this->render('OrdenPubBundle::insertOrdenPub.html.twig', array('form' => $form->createView(), 'errors' => array(0 => array('message' => "Porfavor seleccione correctamente las pautas.")), 'costotarifas' => $costotarifas, 'emisoras' => $emisoras, 'hidden' => 0, 'hidden2' => 3, 'tarifas' => $tarifas, 'tarifasVig' => $tarifasVig));
                    endif;
                endif;
            endforeach;
            
            /* Cambiamos el id de pauta (integer) por su objeto */
            $idTarifaData = $this->integerToObj($idTarifaData);
            /* FIN pasaje */

            /* CONTROLAR QUE LA FECHA DESDE NO PUEDE SER MAYOR QUE LA FECHA HASTA */
            if(!$this->fechasValidas($idTarifaData, $fecha)):
                return $this->render('OrdenPubBundle::insertOrdenPub.html.twig', array('form' => $form->createView(), 'errors' => array(0 => array('message' => 'Por favor, ingrese correctamente las fechas.'), 1 => array('message' => "La fecha desde no puede ser mayor que la fecha hasta.")), 'costotarifas' => $costotarifas, 'emisoras' => $emisoras, 'hidden' => 0, 'hidden2' => 3, 'tarifas' => $tarifas, 'tarifasVig' => $tarifasVig));
            endif;
            /* EOF Control fechas */

            /* Calculo de los subtotales de la orden de publicidad */
            $autototal = $this->autoCalculateTotal($idTarifaData);
            /* EOF calculo */
            
            /* Búsqueda de la fecha más lejana entre las condiciones de pago */
            $fechaCobro = $this->calculateFechaCobroPagare($idTarifaData);
            /* EOF busqueda */

            /* Del formulario obtenemos los id de pautas como string separado por comas, lo pasamos a array */
            $selectedPautasObj = $this->getPautasArray($idTarifaData);
            /* EOF pautas array */

            /* Busco cuales son las tarifas seleccionadas a partir de los ids de las pautas */
            #$objTarifasArray = array();
            $selectedTarifasObj = $this->getTarifasArray($selectedPautasObj);
            /* EOF tarifas */

            /* Busco cuales son las emisoras seleccionadas a partir de los id de las pautas */
            #$objEmisorasArray = array();
            $selectedEmisorasObj = $this->getEmisorasArray($selectedTarifasObj);
            /* EOF emisoras */
            
            if(!$selectedPautasObj):
                return $this->render('OrdenPubBundle::insertOrdenPub.html.twig', array('form' => $form->createView(), 'errors' => array(0 => array('message' => 'Porfavor seleccione correctamente las pautas.')), 'costotarifas' => $costotarifas, 'emisoras' => $emisoras, 'hidden' => 0, 'hidden2' => 3, 'tarifas' => $tarifas, 'tarifasVig' => $tarifasVig));
            endif;
            
            // Calcula el total de la orden
            $total_gral = $this->calculateTotal($autototal); // Total de la orden
            $total_letras = $this->transformLetras($total_gral);
            if($form->isValid()):
                /* Si hidden es 1 el primer formulario ya fue completado
                 * y se mostrarán los datos seleccionados para confirmarlos
                 */
                if($confirmation):
                    /* Guardado de datos en la tabla */
                    $ordenpub->setIdAgencia($user->getIdAgencia());
                    $ordenpub->setTotal($total_gral);
                    $em->persist($ordenpub);
                    $em->flush();
                    /* Fin de guardado */
                    
                    /* Una vez que se guardo el form puedo obtener el id y modificar el nro. de orden */
                    $nroorden = $this->getDoctrine()
                                    ->getRepository('OrdenPubBundle:OrdenPub')
                                    ->findOneBy(array('id_ordenpub' => $ordenpub->getIdOrdenPub()));
                    $nroorden->setNroOrdenpub($user->getIdAgencia().$ordenpub->getIdOrdenPub());
                    $em->flush();
                    /* Fin modificacion */

                    /* Select de las grillas a mostrar en la impresion
                    * esto hacemos porque no todas las tarifas son seleccionadas
                    * es decir, no todas las emisoras */
                    $idEmisorasSelectedArray = array();
                    foreach($selectedEmisorasObj as $emisora):
                        $idEmisorasSelectedArray[] = $emisora->getIdEmisora();
                    endforeach;
                    /* Fin Grillas */
                    
                    /* Guardo en c/c de cliente */
                    #$this->saveCtaCteCliente($ordenpub->getNroOrdenPub(), $ordenpub, $ordenpub->getIdCliente(), $total_gral);                    
                    /* EOF guardado */

                    /* Quitamos las emisoras no seleccionadas */
                    $idTarifaData = $this->quitarVacios($idTarifaData);
                    /* EOF depurado */
                    
                    /* Guardado de condiciones en la tabla intermedia orden_tarifas */
                    $this->saveTablaIntermediaTarifas($idTarifaData, $nroorden, $autototal);
                    /* EOF guardado */
                    
                    $ordentarifas = $this->getDoctrine()
                            ->getRepository("OrdenPubBundle:OrdenTarifas")
                            ->findBy(array('id_ordenpub' => $ordenpub->getIdOrdenPub()));
                    
                    /* Creación del pagaré que va al final de la orden */
                    $pagare = $this->savePagare($ordenpub, $ordenpub->getNroOrdenpub(), date('d-m-Y'), $fechaCobro->format('d-m-Y'), $total_letras, $total_gral);
                    /* EOF pagaré */

                    return $this->render('OrdenPubBundle::print.html.twig', array('ordenpub' => $ordenpub, 'ordentarifas' => $ordentarifas, 'pagare' => $pagare, 'emisorasSelected' => $idEmisorasSelectedArray, 'web_dir' => '', 'membrete' => $user->getIdAgencia()->getPathMembrete()));
                else:
                    $formType = new InsertOrdenPubType($user->getIdAgencia(), $user->getRoles(), $fecha->format('d-m-Y'), $confirmation = true, array(
                        'action' => $this->generateUrl('insertOrdenPub'),
                        'method' => 'POST',
                    ));
                    $form = $this->createForm($formType, $ordenpub);
                    
                    return $this->render('OrdenPubBundle::insertOrdenPub.html.twig', array('form' => $form->createView(), 'data' => $data, 'condic' => $idTarifaData, 'emisoras' => $emisoras, 'costotarifas' => $costotarifas, 'hidden' => 1, 'total' => $total_gral, 'hidden2' => 1, 'fechaCobro' => $fechaCobro, 'emtotal' => $autototal, 'tarifas' => $tarifas, 'tarifasVig' => $tarifasVig, 'confirmPautas' => $selectedPautasObj, 'confirmTarifas' => $selectedTarifasObj));
                endif;
            else:
              if (count($errors) > 0):
                  return $this->render('OrdenPubBundle::insertOrdenPub.html.twig', array('form' => $form->createView(),'errors' => $errors, 'costotarifas' => $costotarifas,'emisoras' => $emisoras, 'hidden' => 0, 'hidden2' => 3, 'tarifas' => $tarifas, 'tarifasVig' => $tarifasVig));
              endif;
            endif;
        endif;
        
        return $this->render('OrdenPubBundle::insertOrdenPub.html.twig', array('form' => $form->createView(), 'emisoras' => $emisoras, 'costotarifas' => $costotarifas, 'hidden' => 0, 'hidden2' => 3, 'tarifas' => $tarifas, 'tarifasVig' => $tarifasVig));
    }
    
    public function createOrdenPdfAction($id) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $ordenpub = $this->getDoctrine()
                        ->getRepository('OrdenPubBundle:OrdenPub')
                        ->find($id);
        
        $ordentarifas = $this->getDoctrine()
                            ->getRepository('OrdenPubBundle:OrdenTarifas')
                            ->findBy(array('id_ordenpub' => $id));
        
        $idEmisorasSelectedArray = array();
        foreach($ordentarifas as $ordentarifa):
            $idEmisorasSelectedArray[] = $ordentarifa->getIdCostoTarifas()->getIdTarifa()->getIdEmisora()->getIdEmisora();
        endforeach;
        
        $pagare = $this->getDoctrine()
                        ->getRepository('MediterraneoFMBundle:Pagares')
                        ->findOneBy(array('idOrdenpub' => $id));
        
        $code = base64_encode($user->getIdAgencia()->getIdAgencia().date('d-m-Y H:i:s'));

        $web_dir = str_replace('web/', 'web', $_SERVER['DOCUMENT_ROOT']);
        
        $html = $this->renderView('OrdenPubBundle::print.html.twig', array('ordenpub' => $ordenpub, 'ordentarifas' => $ordentarifas, 'pagare' => $pagare, 'emisorasSelected' => $idEmisorasSelectedArray, 'web_dir' => $web_dir, 'membrete' => $user->getIdAgencia()->getPathMembrete()));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => "filename='orden" . $code . ".pdf'"
            )
        );
    }

    protected function isVigente($desde, $hasta) {
        $hoy = new \DateTime('now');
        $hoy = strtotime($hoy->format('d-m-Y'));
        if(strtotime($desde) <= $hoy && strtotime($hasta) >= $hoy):
            return true;
        else:
            return false;
        endif;
    }

    protected function getPautasArray($condiciones) {
        $pautas = array();
        foreach($condiciones as $condicion):
            $pautas[] = $condicion->getIdCostoTarifas();
        endforeach;
        
        return $pautas;
    }

    protected function getTarifasArray($pautas) {
        $tarifas = array();
        foreach($pautas as $pauta):
            $tarifas[] = $pauta->getIdTarifa();
        endforeach;

        return $tarifas;
    }

    protected function quitarVacios($condiciones) {
        $c = 0;
        foreach($condiciones as $condicion):
            if(!$condicion->getIdCostoTarifas()):
                unset($condiciones[$c]);
            endif;
            $c++;
        endforeach;

        return $condiciones;
    }

    protected function getEmisorasArray($tarifas) {
        $emisoras = array();
        foreach($tarifas as $tarifa):
            if($tarifa):
                $emisoras[] = $tarifa->getIdEmisora();
            endif;
        endforeach;

        return $emisoras;
    }

    protected function fechasValidas($condiciones, $fecha) {
        $datePattern = '/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{1,4}$/';
        
        if(!$fecha):
            return false;
        endif;
        
        if(!preg_match($datePattern, $fecha->format('d-m-Y'))):
            return false;
        endif;
        
        foreach($condiciones as $condicion):
            if(!$condicion->getFechaDesde()): // Si el objeto no existe, entonces la fecha no fue correctamente ingresada
                return false;
            endif;
            if(!$condicion->getFechaHasta()): // Si el objeto no existe, entonces la fecha no fue correctamente ingresada
                return false;
            endif;
            if(!preg_match($datePattern, $condicion->getFechaDesde()->format('d-m-Y'))):
                return false;
            endif;
            if(!preg_match($datePattern, $condicion->getFechaHasta()->format('d-m-Y'))):
                return false;
            endif;
            if(strtotime($condicion->getFechaDesde()->format('d-m-Y')) > strtotime($condicion->getFechaHasta()->format('d-m-Y'))):
                return false;
            endif;
        endforeach;

        return true;
    }
    
    protected function integerToObj($condiciones) {
        foreach($condiciones as $condicion):
            $pauta = $this->getDoctrine()
                        ->getRepository('PautasBundle:Pautas')
                        ->findOneBy(array('id_costotarifas' => $condicion->getIdCostoTarifas()));
                        
            if($pauta):
                $condicion->setIdCostoTarifas($pauta);
            endif;
        endforeach;
        
        return $condiciones;
    }
    
    /**
     *
     * @Route("/ordenespub/show/{page}", defaults={"page"=1}, name="showOrdenesPub")
     * 
     */
    public function showAction(Request $request, $page, $cl = false) {
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $idTarifa = $request->request->get('idTarifa');
        if($idTarifa):
            $pautas = $this->getDoctrine()
                    ->getRepository('PautasBundle:Pautas')
                    ->findBy(array('id_tarifa' => $idTarifa));
        
            return $this->render('OrdenPubBundle::pautasSelectFilter.html.twig', array('pautas' => $pautas));
        endif;
        
        $tarifasSelect = $this->getDoctrine()
                    ->getRepository('TarifasBundle:Tarifas')
                    ->findAll();

        $ordentarifas = array();
        
        $session = $this->get('session');
        
        if($request->request->get('ordenescant') != null):
            $session->set('ordenescant', $request->request->get('ordenescant'));
        endif;

        if($cl == true):
            $session->set('fecha_desde', '');
            $session->set('fecha_hasta', '');
            $session->set('id_cliente', '');
            $session->set('nro_orden', '');
            $session->set('ordenar_por', '');
            $session->set('id_agencia', '');
            $session->set('vigencia', '');
            $session->set('liquidado', '');
            $session->set('tarifa', '');
            $session->set('pauta', '');
        endif;

        $default = array('id_cliente' => null, 'id_agencia' => null, 'id_tarifa' => null);
        if($session->get('id_cliente')):
            $default['id_cliente'] = $session->get('id_cliente');
        endif;
        if($session->get('id_agencia')):
            $default['id_agencia'] = $session->get('id_agencia');
        endif;
        if($session->get('tarifa')):
            $default['id_tarifa'] = $session->get('tarifa');
        endif;
        
        $ordent = new OrdenTarifas();
        $formType = new OrdenPubFilterType($user->getIdAgencia(), $user->getRoles(), $this->getDoctrine()->getManager(), $default);
        $form = $this->createForm($formType, $ordent);

        $repository = $this->getDoctrine()
                        ->getRepository('OrdenPubBundle:OrdenTarifas');

        $form->handleRequest($request);
        $data = $form->getData();
        if($request->isMethod('POST')):
            if(!$data->getFechaDesde()):
                $session->set('fecha_desde', '');
            endif;
            if(!$data->getFechaHasta()):
                $session->set('fecha_hasta', '');
            endif;
            if(!$data->getIdOrdenPub()->getNroOrdenpub()):
                $session->set('nro_orden', '');
            endif;
        endif;
        if($data->getFechaDesde()):
            $session->set('fecha_desde', $data->getFechaDesde());
        endif;
        if($data->getFechaHasta()):
            $session->set('fecha_hasta', $data->getFechaHasta());
        endif;
        if($form->get('id_tarifa')->getData()):
            $session->set('tarifa', $form->get('id_tarifa')->getData());
        else:
            if($form->get('id_tarifa')->getViewData() == 'null'): // valor enviado cuando se vacía el campo
                $session->set('tarifa', '');
            endif;
        endif;
        if($data->getIdCostotarifas()):
            $session->set('pauta', $data->getIdCostotarifas());
            if($data->getIdCostotarifas() == 'null'): // este es campo completado con jquery
                $session->set('pauta', '');
            endif;
        endif;
        if($data->getIdOrdenPub()):
            if($data->getIdOrdenPub()->getNroOrdenpub()):
                $session->set('nro_orden', $data->getIdOrdenPub()->getNroOrdenpub());
            endif;
            if($data->getIdOrdenPub()->getIdCliente()):
                $session->set('id_cliente', $data->getIdOrdenPub()->getIdCliente()->getIdCliente());
            else:
                if($form->get('id_ordenpub')->get('id_cliente')->getViewData() == 'null'): // valor enviado cuando se vacía el campo
                    $session->set('id_cliente', '');
                endif;
            endif;
            if($data->getIdOrdenPub()->getTextoPublicidad()):
                $session->set('ordenar_por', $data->getIdOrdenPub()->getTextoPublicidad());
            else:
                if($form->get('id_ordenpub')->get('texto_publicidad')->getViewData() == 'null'): // valor enviado cuando se vacía el campo
                    $session->set('ordenar_por', '');
                endif;
            endif;
            if($form->get('id_ordenpub')->has('id_agencia')):
                if($data->getIdOrdenPub()->getIdAgencia()):
                    $session->set('id_agencia', $data->getIdOrdenPub()->getIdAgencia());
                else:
                    if($form->get('id_ordenpub')->get('id_agencia')->getViewData() == 'null'): // valor enviado cuando se vacía el campo
                        $session->set('id_agencia', '');
                    endif;
                endif;
            endif;
            if($data->getIdOrdenPub()->getObservaciones()):
                $session->set('vigencia', $data->getIdOrdenPub()->getObservaciones());
            else:
                if($form->get('id_ordenpub')->get('observaciones')->getViewData() == 'null'): // valor enviado cuando se vacía el campo
                    $session->set('vigencia', '');
                endif;
            endif;
            if($data->getIdOrdenPub()->getLiquidado()):
                $session->set('liquidado', $data->getIdOrdenPub()->getLiquidado());
            else:
                if($form->get('id_ordenpub')->get('liquidado')->getViewData() == 'null'): // valor enviado cuando se vacía el campo
                    $session->set('liquidado', '');
                endif;
            endif;
        else:
            if($form->get('id_ordenpub')->get('id_cliente')->getViewData() == 'null'): // valor enviado cuando se vacía el campo
                $session->set('id_cliente', '');
            endif;
            if($form->get('id_ordenpub')->get('texto_publicidad')->getViewData() == 'null'): // valor enviado cuando se vacía el campo
                $session->set('ordenar_por', '');
            endif;
            if($form->get('id_ordenpub')->has('id_agencia')):
                if($form->get('id_ordenpub')->get('id_agencia')->getViewData() == 'null'):
                    $session->set('id_agencia', '');
                endif;
            endif;
            if($form->get('id_ordenpub')->get('observaciones')->getViewData() == 'null'): // valor enviado cuando se vacía el campo
                $session->set('vigencia', '');
            endif;
            if($form->get('id_ordenpub')->get('liquidado')->getViewData() == 'null'): // valor enviado cuando se vacía el campo
                $session->set('liquidado', '');
            endif;
        endif;

        // Si no hay ningún campo del filtro completado, buscar todo.
            if(!$session->get('fecha_desde') && !$session->get('fecha_hasta') && !$session->get('id_cliente') && !$session->get('nro_orden') && !$session->get('ordenar_por') && !$session->get('id_agencia') && !$session->get('vigencia') && !$session->get('liquidado') && !$session->get('pauta')):
                if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                    $query = $repository->createQueryBuilder('ot')
                        ->leftJoin('ot.id_ordenpub', 'o')
                        ->orderBy('o.fecha', 'desc')
                        ->getQuery();
                else:
                    $query = $repository->createQueryBuilder('ot')
                        ->leftJoin('ot.id_ordenpub', 'o')
                        ->where('o.id_agencia = :id_agencia')
                        ->setParameter('id_agencia', $user->getIdAgencia())
                        ->orderBy('o.fecha', 'desc')
                        ->getQuery();
                endif;
                
                $ordentarifas = $query->getResult();
            endif;
		
            
            // Si hay algún campo del filtro completado, filtrar. Sino buscar todo
            if($session->get('fecha_desde') || $session->get('fecha_hasta') || $session->get('id_cliente') || $session->get('nro_orden') || $session->get('ordenar_por') || $session->get('id_agencia') || $session->get('vigencia') || $session->get('liquidado') || $session->get('pauta') || $session->get('tarifa')):
                $em = $this->getDoctrine()->getManager();
                $querystring = 'SELECT ot FROM OrdenPubBundle:OrdenTarifas ot ';

                if($session->get('id_cliente') || $session->get('nro_orden') || $session->get('ordenar_por') || $session->get('id_agencia') || $session->get('vigencia') || $session->get('liquidado') || !in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                    $querystring .= 'JOIN ot.id_ordenpub o ';
                endif;
                
                if($session->get('tarifa')):
                    $querystring .= 'JOIN ot.id_costotarifas p ';
                endif;

                if($session->get('nro_orden')):
                    $querystring .= 'JOIN o.idCliente c JOIN c.id_agencia a ';
                endif;

                if($session->get('fecha_desde') || $session->get('fecha_hasta') || $session->get('id_cliente') || $session->get('nro_orden') || $session->get('id_agencia') || $session->get('vigencia') || $session->get('liquidado') || $session->get('pauta') || $session->get('tarifa')):
        	       $querystring .= 'WHERE ';
                else:
                    if(!in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                        $querystring .= 'WHERE ';
                    endif;
                endif;

                if($session->get('fecha_desde')):
                    $querystring .= 'ot.fecha_desde >= :fecha_desde ';

                    if($session->get('fecha_hasta') || $session->get('id_cliente') || $session->get('nro_orden') || $session->get('id_agencia') || $session->get('vigencia') || $session->get('liquidado') || $session->get('pauta') || $session->get('tarifa')):
                        $querystring .= 'AND ';
                    endif;
                endif;

                if($session->get('fecha_hasta')):
                    $querystring .= 'ot.fecha_hasta <= :fecha_hasta ';

                    if($session->get('id_cliente') || $session->get('nro_orden') || $session->get('id_agencia') || $session->get('vigencia') || $session->get('liquidado') || $session->get('pauta') || $session->get('tarifa')):
                        $querystring .= 'AND ';
                    endif;
                endif;

                if($session->get('id_cliente')):
                    $querystring .= 'o.idCliente = ' . $session->get('id_cliente') . ' ';

                    if($session->get('nro_orden') || $session->get('id_agencia') || $session->get('vigencia') || $session->get('liquidado') || $session->get('pauta') || $session->get('tarifa')):
                        $querystring .= 'AND ';
                    endif;
                endif;

                if($session->get('nro_orden')):
                    $querystring .= "CONCAT('OP/', a.id_agencia, '-', o.id_ordenpub) LIKE '%" . $session->get('nro_orden') . "%' ";

                    if($session->get('id_agencia') || $session->get('vigencia') || $session->get('liquidado') || $session->get('pauta') || $session->get('tarifa')):
                        $querystring .= 'AND ';
                    endif;
                endif;

                if($session->get('id_agencia')):
                    $querystring .= 'o.id_agencia = ' . $session->get('id_agencia') . ' ';

                    # $session->get('vigencia') contiene la seleccion de vigencia
                    if($session->get('vigencia') || $session->get('liquidado') || $session->get('pauta') || $session->get('tarifa')):
                        $querystring .= 'AND ';
                    endif;
                endif;

                # $session->get('vigencia') contiene la seleccion de vigencia (menor o mayor)
                if($session->get('vigencia')):
                    $querystring .= 'ot.fecha_hasta ' . $session->get('vigencia') . ' :fecha_hasta_vigencia ';
                
                    if($session->get('liquidado') || $session->get('pauta') || $session->get('tarifa')):
                        $querystring .= 'AND ';
                    endif;
                endif;
                
                if($session->get('liquidado')):
                    if($session->get('liquidado') == 'true'):
                        $querystring .= 'o.liquidado = ' . 1 . ' ';
                    elseif($session->get('liquidado') == 'false'):
                        $querystring .= 'o.liquidado = ' . 0 . ' ';
                    endif;
                    
                    if($session->get('pauta') || $session->get('tarifa')):
                        $querystring .= 'AND ';
                    endif;
                endif;
                
                if($session->get('pauta')):
                    $querystring .= 'ot.id_costotarifas = ' . $session->get('pauta') . ' ';
                    
                    if($session->get('tarifa')):
                        $querystring .= 'AND ';
                    endif;
                endif;
                
                if($session->get('tarifa')):
                    $querystring .= 'p.id_tarifa = ' . $session->get('tarifa') . ' ';
                endif;

                if(!in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                    if($session->get('fecha_desde') || $session->get('fecha_hasta') || $session->get('id_cliente') || $session->get('nro_orden') || $session->get('id_agencia') || $session->get('vigencia') || $session->get('liquidado') || $session->get('pauta') || $session->get('tarifa')):
                        $querystring .= 'AND ';
                    endif;
                    $querystring .= ' o.id_agencia = ' . $user->getIdAgencia() . ' ';
                endif;

                # $session->get('ordenar_por') contiene el orden seleccinado
                if($session->get('ordenar_por')):
                    $querystring .= 'ORDER BY ' . $session->get('ordenar_por') . ' DESC ';
                endif;

                // comparacion de fechas por parametros
                if($session->get('fecha_desde') && $session->get('fecha_hasta') && $session->get('vigencia')):
                    $query = $em->createQuery($querystring)->setParameters(array('fecha_desde' => $session->get('fecha_desde'), 'fecha_hasta' => $session->get('fecha_hasta'), 'fecha_hasta_vigencia' => new \DateTime('now')));
                elseif($session->get('fecha_desde') && $session->get('fecha_hasta') && !$session->get('vigencia')):
                    $query = $em->createQuery($querystring)->setParameters(array('fecha_desde' => $session->get('fecha_desde'), 'fecha_hasta' => $session->get('fecha_hasta')));
                elseif($session->get('fecha_desde') && !$session->get('fecha_hasta') && $session->get('vigencia')):
                    $query = $em->createQuery($querystring)->setParameters(array('fecha_desde' => $session->get('fecha_desde'), 'fecha_hasta_vigencia' => new \DateTime('now')));
                elseif(!$session->get('fecha_desde') && $session->get('fecha_hasta') && $session->get('vigencia')):
                    $query = $em->createQuery($querystring)->setParameters(array('fecha_hasta' => $session->get('fecha_hasta'), 'fecha_hasta_vigencia' => new \DateTime('now')));
                elseif($session->get('fecha_desde') && !$session->get('fecha_hasta') && !$session->get('vigencia')):
                    $query = $em->createQuery($querystring)->setParameter('fecha_desde', $session->get('fecha_desde'));
                elseif($session->get('fecha_hasta') && !$session->get('fecha_desde') && !$session->get('vigencia')):
                    $query = $em->createQuery($querystring)->setParameter('fecha_hasta', $session->get('fecha_hasta'));
                elseif($session->get('vigencia') && !$session->get('fecha_hasta') && !$session->get('fecha_desde')):
                    $query = $em->createQuery($querystring)->setParameter('fecha_hasta_vigencia', new \DateTime('now'));
                elseif(!$session->get('vigencia') && !$session->get('fecha_hasta') && !$session->get('fecha_desde')):
                    $query = $em->createQuery($querystring);
                else:
                    $query = $em->createQuery($querystring);
                endif;

                $ordentarifas = $query->getResult();
            else:
                foreach($user->getRoles() as $rol):
                    if($rol != 'ROLE_SUPER_ADMIN'):
                        $query = $repository->createQueryBuilder('ot')
                            ->leftJoin('ot.id_ordenpub', 'o')
                            ->where('o.id_agencia = :id_agencia')
                            ->setParameter('id_agencia', $user->getIdAgencia())
                            ->orderBy('o.fecha', 'desc')
                            ->getQuery();
                    else:
                        $query = $repository->createQueryBuilder('ot')
                            ->leftJoin('ot.id_ordenpub', 'o')
                            ->orderBy('o.fecha', 'desc')
                            ->getQuery();
                    endif;
                endforeach;
                
                $ordentarifas = $query->getResult();
            endif; 

        /* else:
            foreach($user->getRoles() as $rol):
                if($rol != 'ROLE_ADMIN'):
                    $query = $repository->createQueryBuilder('ot')
                        ->leftJoin('ot.id_ordenpub', 'o')
                        ->where('o.id_agencia = :id_agencia')
                        ->setParameter('id_agencia', $user->getIdAgencia())
                        ->getQuery();
                else:
                    $query = $repository->createQueryBuilder('ot')
                        ->getQuery();
                endif;
            endforeach;

            $ordentarifas = $query->getResult();
        endif; */       

        if (!$ordentarifas):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Ordenes de publicidad', 'Nuevo' => $this->generateUrl('insertOrdenPub'), 'Restablecer' => $this->generateUrl('showOrdenesPub', array('page' => 1, 'cl' => 1))));
        endif;
        
        $vencidas = array();
        $id_ordenesArray = array();
        foreach($ordentarifas as $ot):
            $id_ordenesArray[] = $ot->getIdOrdenPub()->getIdOrdenPub();
            if(strtotime($ot->getFechaHasta()->format('d-m-Y')) < strtotime(date('d-m-Y'))):
                $vencidas[$ot->getId()] = $ot->getId();
            endif;
        endforeach;
        
        $ordentarifas2 = array_unique($id_ordenesArray);
        
        foreach($ordentarifas2 as $a):
            $or[] = $this->getDoctrine()
                        ->getRepository('OrdenPubBundle:OrdenPub')
                        ->find($a);
        endforeach;

        /* $repository2 = $this->getDoctrine()
                ->getRepository('OrdenPubBundle:OrdenPub');
		
		$query2 = $repository2->createQueryBuilder('op')
		    ->leftJoin('op.id_cliente', 'c')
		    ->orderBy('c.nombre', 'DESC')
		    ->getQuery();
		
		$ordenpub = $query2->getResult(); */
        
        $adapter = new ArrayAdapter($or);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('ordenescant')));

        return $this->render('OrdenPubBundle::showOrdenesPub.html.twig', array('form' => $form->createView(), 'ordenpub' => $ot, 'ordentarifas' => $ordentarifas, 'pager' => $pager, 'vencidas' => $vencidas, 'tarifas' => $tarifasSelect));
    }
    
    public function showOneAction($nombre) {
        $nombre2 = explode(',', $nombre);
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        foreach($nombre2 as $id):
            if($id != 'yes'):
                $ordenpub = $this->getDoctrine()
                        ->getRepository('OrdenPubBundle:OrdenPub')
                        ->findOneBy(array('id_ordenpub' => $id));
            endif;
        endforeach;
        
        $ordentarifas = $this->getDoctrine()
                            ->getRepository("OrdenPubBundle:OrdenTarifas")
                            ->findBy(array('id_ordenpub' => $ordenpub->getIdOrdenPub()));
        
        $idEmisorasSelected = array();
        if($ordentarifas):
            foreach($ordentarifas as $ot):
                $idEmisorasSelected[] = $ot->getIdCostoTarifas()->getIdTarifa()->getIdEmisora()->getIdEmisora();
            endforeach;
        endif;
        
        $pagare = $this->getDoctrine()->getRepository('MediterraneoFMBundle:Pagares')->findOneBy(array('idOrdenpub' => $ordenpub->getIdOrdenPub()));
        
        return $this->render('OrdenPubBundle::print.html.twig', array('ordenpub' => $ordenpub, 'ordentarifas' => $ordentarifas, 'pagare' => $pagare, 'emisorasSelected' => $idEmisorasSelected, 'web_dir' => '', 'returnUrl' => 'showOrdenesPub', 'membrete' => $user->getIdAgencia()->getPathMembrete()));
    }
    
    public function showGrillaAction() {
        $emisionprograma = $this->getDoctrine()
                ->getRepository('MediterraneoFMBundle:EmisionPrograma')
                ->findAll();
        
        $count = count($emisionprograma);
        
        for($i = 0;$i < $count;$i++) {
   
            
        }
        
        $mencionprograma = $this->getDoctrine()
                ->getRepository('MediterraneoFMBundle:MencionPrograma')
                ->findAll();
        
        $programas = $this->getDoctrine()
                ->getRepository('ProgramasBundle:Programas')
                ->findAll();
        
        return $this->render('MediterraneoFMBundle::showGrilla.html.twig', array('emisionprograma' => $emisionprograma, 'mencionprograma' => $mencionprograma, 'programas' => $programas));
    }
    
    public function deleteAction($id) {        
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $ordenespub = $this->getDoctrine()->getRepository('OrdenPubBundle:OrdenPub')->find($id2);
                $em = $this->getDoctrine()->getManager();
                
                $em->remove($ordenespub);
                try {
                    $em->flush();
                    $user = $this->container->get('security.context')->getToken()->getUser();
                    
                    $logType = $em->getRepository('SystemLogBundle:LogTypes')->findOneBy(array('descripcion' => 'ALERT'));

                    $systemlog = new SystemLog();
                    $systemlog->setIdResponsable($user);
                    $systemlog->setFechaHora(new \DateTime('now'));
                    $systemlog->setDescripcion("Orden $id2 eliminada.");
                    $systemlog->setIdType($logType);
                    $em->merge($systemlog);
                    $em->flush();
                
                    $this->get('session')->getFlashBag()->add(
                            'notice', 'La orden "' . $ordenespub->getNroOrdenPub() . '" ha sido borrada con éxito.'
                    );
                } catch (\Doctrine\DBAL\DBALException $ex) {
                    $this->get('session')->getFlashBag()->add(
                        'error', 'La orden "' . $ordenespub->getNroOrdenPub() . '" no ha podido ser eliminada. Para hacerlo, por favor elimine todas las liquidaciones y los pagos de esta orden.'
                    );
                }

                $this->container->get('doctrine')->resetEntityManager();
            endif;
        endforeach;

        return $this->redirect($this->generateUrl('showOrdenesPub'));
    }
    
    public function anularAction($id_ord, Request $request) {
        $ordenesAnuladas = new ordenesAnuladas();
        $formType = new InsertOrdenesAnuladasType();
        $form = $this->createForm($formType, $ordenesAnuladas);

        $form->handleRequest($request);
        if($request->getMethod() == 'POST'):
            if($form->isValid()):
                $data = $form->getData();
                $motivo = $data->getMotivoAnula(); # Motivo siempre igual fuera del for.
                $id = explode(',', $id_ord);
                $em = $this->getDoctrine()->getManager();
                foreach($id as $id):
                    // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
                    if($id != 'yes'):
                        $orden = $this->getDoctrine()->getRepository('OrdenPubBundle:OrdenPub')->findOneBy(array('id_ordenpub' => $id));
                
                        if($orden->getEstado() == 1):
                            $orden->setEstado(0);
                        else:
                            $orden->setEstado(1);
                        endif;
                        $em->flush();
                        $this->get('session')->getFlashBag()->add(
                                'notice', 'El estado de la orden ' . $id . ' ha sido cambiado con éxito.'
                        );

                        $ordenesAnuladas->setIdOrdenpub($orden);
                        $ordenesAnuladas->setMotivoAnula($motivo); 
                        # Es necesario hacer set en cada objeto por el new hecho al final
                        $em->persist($ordenesAnuladas);
                        $em->flush();
                        $ordenesAnuladas = new ordenesAnuladas(); 
                        /* Ponemos el new al final para que haya un nuevo objeto al dar otra vuelta el foreach */
                    endif;
                endforeach;
        
                return $this->redirect($this->generateUrl('showOrdenesPub'));
            endif;
        endif;

        return $this->render('OrdenPubBundle::anularOrden.html.twig', array('form' => $form->createView(), 'id_ord' => $id_ord));
    }
    
    protected function calculateTotal($totales) {
        $total_gral = 0;
        foreach($totales as $subtotal):
            $total_gral += $subtotal;
        endforeach;
        
        return $total_gral;
    }
    
    protected function saveTablaIntermediaTarifas($condiciones, $orden, $autototal){
        $c = 0;
        foreach($condiciones as $condicion):
            $pauta = $this->getDoctrine()
                        ->getRepository('PautasBundle:Pautas')
                        ->findOneBy(array('id_costotarifas' => $condicion->getIdCostoTarifas()));

            $orden_tarifas = new OrdenTarifas();
            $orden_tarifas->setIdOrdenpub($orden);                    
            $orden_tarifas->setIdCostoTarifas($pauta);
            $orden_tarifas->setFechaDesde($condicion->getFechaDesde());
            $orden_tarifas->setFechaHasta($condicion->getFechaHasta());
            $orden_tarifas->setRecargo($condicion->getRecargo());
            $orden_tarifas->setDescuento($condicion->getDescuento());
            $orden_tarifas->setNeto($condicion->getNeto());
            $orden_tarifas->setTotal($autototal[$c]); // aca uso la variable $c
            $em = $this->getDoctrine()->getManager();
            $em->persist($orden_tarifas);
            $em->flush();
            $c++;
        endforeach;
    }
    
    public function renovarAction($id, Request $request) {
        /* Seleccion de tarifas a partir de emisora (formulario jquery) */
        $id_emisora = $request->request->get('id_emisora');
        if($id_emisora):
            if($id_emisora == 0):
                return $this->render('OrdenPubBundle::tarifasSelect.html.twig', array('tarifas' => array()));
            else:
                $tarifas = $this->getDoctrine()->getRepository('TarifasBundle:Tarifas')->findBy(array('id_emisora' => $id_emisora));
            
                foreach($tarifas as $tarifa):
                    if(strtotime($tarifa->getFechaDesde()->format('d-m-Y')) < strtotime(date('d-m-Y')) && strtotime($tarifa->getFechaHasta()->format('d-m-Y')) >= strtotime(date('d-m-Y'))):
                        $tar[] = $tarifa;
                    endif;
                endforeach;
            
                if($tar):
                    return $this->render('OrdenPubBundle::tarifasSelect.html.twig', array('tarifas' => $tar));
                else:
                    return $this->render('OrdenPubBundle::tarifasSelect.html.twig', array('tarifas' => array()));
                endif;
            endif;
        endif;
        /* EOF seleccion de tarifas */

        /* Seleccion de pautas a partir de tarifas (formulario jquery) */
        $id_tarifa = $request->request->get('id_tarifa');
        if($id_tarifa):
            if($id_tarifa == 0):
                return $this->render('OrdenPubBundle::pautasSelect.html.twig', array('pautas' => array()));
            else:
            $pautas = $this->getDoctrine()->getRepository('PautasBundle:Pautas')->findBy(array('id_tarifa' => $id_tarifa));

            if($pautas):
                    # Render de las opciones (pautas)
                    return $this->render('OrdenPubBundle::pautasSelect.html.twig', array('pautas' => $pautas));
                else:
                    # Render de las opciones (pautas)
                    return $this->render('OrdenPubBundle::pautasSelect.html.twig', array('pautas' => array()));
                endif;
            endif;
        endif;
        /* FIN seleccion de pautas */

        $id2 = explode(',', $id);
        if(is_array($id2)):
            foreach($id2 as $id):
                if($id != 'yes'):
                    $ordenvieja = $this->getDoctrine()
                                    ->getRepository('OrdenPubBundle:OrdenPub')
                                    ->find($id);

                    $condvieja = $this->getDoctrine()
                                    ->getRepository('OrdenPubBundle:OrdenTarifas')
                                    ->findBy(array('id_ordenpub' => $id));
                endif;
            endforeach;
        endif;
        $ordenpub = new OrdenPub();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $tarifas = $this->getDoctrine()->getRepository('TarifasBundle:Tarifas')->findAll();
        
        /* Entidades necesarias para la orden y la generacion de tarifas */
        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
            $emisoras = $this->getDoctrine()->getRepository('EmisorasBundle:Emisoras')->findAll();
        else:
            $emisoras = $this->getDoctrine()->getRepository('EmisorasBundle:Emisoras')->findBy(array('id_ciudad' => $user->getIdAgencia()->getIdCiudad()));
        endif; 
        /* Fin de las entidades */
        
        /* Agregar las tarifas seleccionadas */
        if($condvieja):
            foreach($condvieja as $condicion):
                $ordenpub->getIdTarifa()->add($condicion);
            endforeach;
        endif;
        /* EOF tarifas seleccionadas */
        
        /* Formulario y su validación */
        $formType = new RenovarOrdenPubType($ordenvieja, $user->getIdAgencia(), $user->getRoles());
        $form = $this->createForm($formType, $ordenpub);
        /* Fin instancia de formulario */
        
        return $this->render('OrdenPubBundle::renovarOrdenPub.html.twig', array('form' => $form->createView(), 'emisoras' => $emisoras, 'id_ordenvieja' => $id, 'tarifas' => $tarifas, 'condvieja' => $condvieja));
    }
    
    protected function savePagare($idOrdenpub, $nro_ordenpub, $fechaEmision, $fechaCobro, $totalLetras, $total){
        $pagares = new Pagares();
        $pagares->setIdOrdenpub($idOrdenpub);
        $pagares->setFechaEmision(new \DateTime($fechaEmision));
        $pagares->setFechaCobro(new \DateTime($fechaCobro));
        $pagares->setTotalLetras($totalLetras);
        $pagares->setTotal($total);
        $em = $this->getDoctrine()->getManager();
        $em->persist($pagares);
        $em->flush();
        
        $pagare = $this->getDoctrine()
                        ->getRepository('MediterraneoFMBundle:Pagares')
                        ->findOneBy(array('id' => $pagares->getId()));
        $pagare->setNroPagare($pagares->getId().$nro_ordenpub);
        $em->flush();
        
        return $pagare;
    }
    
    protected function calculateFechaCobroPagare($condiciones) {
        $fecha_cobro = new \DateTime(date('00-00-0000'));
        
        foreach($condiciones as $condicion):
            if($condicion->getFechaHasta()):
                $fecha_hasta = $condicion->getFechaHasta()->format('d-m-Y');
                if(strtotime($fecha_cobro->format('d-m-Y')) < strtotime($fecha_hasta)):
                    $fecha_cobro = $condicion->getFechaHasta();
                endif;
            endif;
        endforeach;
        
        return $fecha_cobro;
    }
    
    protected function transformLetras($valor) {
        $transform = new TransformController();
        return $transform->traducir($valor);
    }
    
    protected function autoCalculateTotal($condiciones) {
        $total = array();
        $c = 0;
        foreach($condiciones as $condicion):
            $recargo = 0;
            $descuento = 0;
            if($condicion->getRecargo()):
                $recargo = ($condicion->getNeto() * $condicion->getRecargo()) / 100;
            endif;
            if($condicion->getDescuento()):
                $descuento = ($condicion->getNeto() * $condicion->getDescuento()) / 100;
            endif;
            $total[$c] = $condicion->getNeto() + $recargo - $descuento;
            $condicion->setTotal($total[$c]);
            $c++;
        endforeach;

        $total = array_filter($total);
        
        return $total;
    }
    
    protected function saveCtaCteCliente($nro_ordenpub, $idOrdenpub, $idCliente, $debe = null, $haber = null) {
        $em = $this->getDoctrine()->getManager();

        $tipodoc = $this->getDoctrine()
                        ->getRepository('MediterraneoFMBundle:TiposDocumentos')
                        ->findOneBy(array('leyenda' => 'OP'));
		
        $ctactecliente = new CtasCtesClientes();
        
        $ctactecliente->setFecha(new \DateTime('now'));
        $ctactecliente->setIdCliente($idCliente);
		$ctactecliente->setIdTipoDocumento($tipodoc);
        $ctactecliente->setConcepto("Contrato orden de publicidad N°$nro_ordenpub");
        if($debe != null):
            $ctactecliente->setDebe($debe);
        else:
            $ctactecliente->setHaber($haber);
        endif;
        $em->persist($ctactecliente);
        $em->flush();

        $ctacte_ordenes = new CtaCteOrdenes();

        $ctacte_ordenes->setIdOrdenpub($idOrdenpub);
        $ctacte_ordenes->setIdCtasCtesC($ctactecliente);
        $em->persist($ctacte_ordenes);
        $em->flush();
    }
}

?>

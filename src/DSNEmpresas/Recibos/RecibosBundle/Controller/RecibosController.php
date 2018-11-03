<?php

namespace DSNEmpresas\Recibos\RecibosBundle\Controller;

# Funciones del kernel de Symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;
# Entities
use DSNEmpresas\Recibos\RecibosBundle\Entity\Recibos;
# Types
use MediterraneoFM\MediterraneoFMBundle\Form\InsertRecibosType;
use MediterraneoFM\MediterraneoFMBundle\Form\EditRecibosType;

class RecibosController extends Controller {
    
    # Alta de Recibos, insert en tabla Recibos.
    public function insertAction(Request $request) {
        $recibos = new Recibos();
        #$programa_mencion = new ProgramaMencion();
        $formType = new InsertRecibosType();
        $form = $this->createForm($formType, $recibos);
        
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->persist($recibos);
                $em->flush();
                
                return $this->render('RecibosBundle::printRecibos.html.twig', array('data' => $data));
                #return $this->redirect($this->generateUrl('insertRecibos'));
            endif;
        endif;
        
        return $this->render('RecibosBundle::insertRecibos.html.twig', array('form' => $form->createView()));
    }
    
    /**
     *
     * @Route("/recibos/show/{page}", defaults={"page"=1}, name="showRecibos")
     * 
     */
    public function showAction($page, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
            $recibos = $this->getDoctrine()
                    ->getRepository('RecibosBundle:Recibos')
                    ->findAll();
        else:
            $repository = $this->getDoctrine()
                    ->getRepository('RecibosBundle:Recibos');

            $query = $repository->createQueryBuilder('r')
                                ->leftJoin('r.id_cliente', 'c')
                                ->where('c.id_agencia = :id_agencia')
                                ->setParameter('id_agencia', $user->getIdAgencia())
                                ->getQuery();

            $recibos = $query->getResult();
        endif;
        
        $session = $this->get('session');
        
        if($request->request->get('Recibos') != null):
            $session->set('Recibos', $request->request->get('Recibos'));
        endif;
         
        if (!$recibos):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Recibos'));
        endif;
        
        $adapter = new ArrayAdapter($recibos);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('Recibos')));

        return $this->render('RecibosBundle::showRecibos.html.twig', array('recibos' => $recibos, 'pager' => $pager));
    }
    
    public function showOneAction($nombre, Request $request) {
        $name = str_replace('-', ' ', $nombre);
        
        $recibo = $this->getDoctrine()
                ->getRepository('RecibosBundle:Recibos')
                ->findOneBy(array('nro_recibo' => $name));
        
        $city = $this->getDoctrine()
                ->getRepository('RecibosBundle:Ciudades')
                ->find($recibo->getIdCiudad());
        
        $formType = new EditRecibosType($city->getNombre());
        $form = $this->createForm($formType, $recibo);
        
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($recibo);
            
            /* Obtengo el id de la ciudad seleccionada 
             * para que en la base de datos se guarde el id y no el nombre */
            $ciudades = $this->getDoctrine()
                ->getRepository('MediterraneoFMBundle:Ciudades')
                ->findOneByNombre($data->getIdCiudad());
            
            if ($form->isValid()):
                $this->get('session')->setFlash(
                    'notice',
                    'El recibo ha sido editado correctamente!'
                );
                $recibo->setIdCiudad($ciudades->getId());
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                return $this->redirect($this->generateUrl('showRecibos'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
        'errors' => $errors,
    ));
            endif;
        endif;
        
        return $this->render('RecibosBundle::showRecibo.html.twig', array('form' => $form->createView(), 'recibo' => $recibo));
    }
    
    public function deleteAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
        	// 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $recibos = $em->getRepository('RecibosBundle:Recibos')->findOneBy(array('nro_recibo' => $id2));

                $em->remove($recibos);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'El recibo ' . $id2 . ' ha sido borrado con éxito.'
                );
            endif;
        endforeach;

        return $this->redirect($this->generateUrl('showRecibos'));
    }
}
?>
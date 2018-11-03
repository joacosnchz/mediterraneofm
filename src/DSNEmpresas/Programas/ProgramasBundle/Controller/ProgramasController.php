<?php

namespace DSNEmpresas\Programas\ProgramasBundle\Controller;

# Funciones del kernel de Symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;
# Entities
use DSNEmpresas\Programas\ProgramasBundle\Entity\Programas;
# Types
use DSNEmpresas\Programas\ProgramasBundle\Form\InsertProgramasType;
use DSNEmpresas\Programas\ProgramasBundle\Form\EditProgramasType;
use DSNEmpresas\Programas\ProgramasBundle\Form\ProgramasFilterType;

class ProgramasController extends Controller {
    
    # Alta de Programas, insert en tabla Programas.
    public function insertAction(Request $request) {
        $programas = new Programas();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $formType = new InsertProgramasType($user->getIdAgencia()->getIdCiudad(), $user->getRoles());
        $form = $this->createForm($formType, $programas);
        
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($programas);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->persist($programas);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'El programa ' . $programas->getIdPrograma() . ' ha sido agregado con éxito.'
                        );
                
                return $this->redirect($this->generateUrl('showProgramas'));
            else:
                return $this->render('ProgramasBundle::insertProgramas.html.twig', array(
                    'form' => $form->createView(), 
                    'errors' => $errors,
                ));
            endif;
        endif;
        
        return $this->render('ProgramasBundle::insertProgramas.html.twig', array('form' => $form->createView(), 'errors' => array()));
    }
    
    /**
     *
     * @Route("/programas/show/{page}", defaults={"page"=1}, name="showProgramas")
     * 
     */
    public function showAction($page, $cl = false, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        /* Seleccion de programaciones a partir de emisoras (formulario jquery) */
        $id_emisora = $request->request->get('id_emisora');
        if($id_emisora):
            if($id_emisora == ""):
                return $this->render('ProgramasBundle::programacionesSelect.html.twig', array('progs' => array()));
            else:
            $progs = $this->getDoctrine()->getRepository('ProgramacionesBundle:Programaciones')->findBy(array('id_emisora' => $id_emisora));

            if($progs):
                    # Render de las opciones (programaciones)
                    return $this->render('ProgramasBundle::programacionesSelect.html.twig', array('progs' => $progs));
                else:
                    # Render de las opciones (programaciones)
                    return $this->render('ProgramasBundle::programacionesSelect.html.twig', array('progs' => array()));
                endif;
            endif;
        endif;
        /* FIN seleccion de programaciones */
        
        $session = $this->get('session');
        
        if($request->request->get('Programas') != null):
            $session->set('Programas', $request->request->get('Programas'));
        endif;

        if($cl == true):
            $session->set('nombre', '');
            $session->set('duracion_desde', '');
            $session->set('duracion_hasta', '');
            $session->set('emisora', '');
            $session->set('programacion', '');
        endif;

        $default = array('id_emisora' => null);
        if($session->get('emisora')):
            $default['id_emisora'] = $session->get('emisora');
        endif;

        $formType = new ProgramasFilterType($user->getIdAgencia()->getIdCiudad(), $user->getRoles(), $this->getDoctrine()->getManager(), $default);
        $pmas = new Programas();
        $form = $this->createForm($formType, $pmas);       

        $form->handleRequest($request);
        $data = $form->getData();
        
        if($data->getNombre()):
            $session->set('nombre', $data->getNombre());
        else:
            $session->set('nombre', '');
        endif;
        if($data->getDuracionDesde()):
            $session->set('duracion_desde', $data->getDuracionDesde());
        else:
            $session->set('duracion_desde', '');
        endif;
        if($data->getDuracionHasta()):
            $session->set('duracion_hasta', $data->getDuracionHasta());
        else:
            $session->set('duracion_hasta', '');
        endif;
        if($data->getIdProgramacion()):
            # Nombre contiene el ID DE EMISORA seleccionado.
            if($data->getIdProgramacion()->getNombre()):
                $session->set('emisora', $data->getIdProgramacion()->getNombre()->getIdEmisora());
            else:
                if($form->get('id_programacion')->get('nombre')->getViewData() == 'null'):
                    $session->set('emisora', '');
                endif;
            endif;

            if($form->get('id_programacion')->get('programacion')->getData()):
                $session->set('programacion', $form->get('id_programacion')->get('programacion')->getData());
                if($form->get('id_programacion')->get('programacion')->getData() == 'null'):
                    $session->set('programacion', '');
                endif;
            endif;
        else:
            if($form->get('id_programacion')->get('nombre')->getViewData() == 'null'):
                $session->set('emisora', '');
            endif;
            if($form->get('id_programacion')->get('programacion')->getData() == 'null'):
                $session->set('programacion', '');
            endif;
        endif;

        $repository = $this->getDoctrine()
                        ->getRepository('ProgramasBundle:Programas');

        if(!$session->get('nombre') && !$session->get('duracion_desde') && !$session->get('duracion_hasta') && !$session->get('emisora') && !$session->get('programacion')):
            
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $query = $repository->createQueryBuilder('p')
                            ->getQuery();
            else:
                $query = $repository->createQueryBuilder('p')
                        ->join('p.id_programacion', 'pr')
                        ->leftJoin('pr.id_emisora', 'e')
                        ->where('e.id_ciudad = :id_ciudad')
                        ->setParameter('id_ciudad', $user->getIdAgencia()->getIdCiudad())
                        ->getQuery();
            endif;

            $programas = $query->getResult();
        endif;

        if($session->get('nombre') || $session->get('duracion_desde') || $session->get('duracion_hasta') || $session->get('emisora') || $session->get('programacion')):
            $em = $this->getDoctrine()->getManager();
            $querystring = 'SELECT p FROM ProgramasBundle:Programas p ';

            if($session->get('emisora')):
                $querystring .= 'JOIN p.id_programacion pr ';

                if(!in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                    $querystring .= 'LEFT JOIN pr.id_emisora e ';
                endif;
            endif;

            /* Debo hacer los JOIN y LEFT JOIN de programaciones y emisoras antes
                de comenzar con las condiciones del query */
            if(!$session->get('emisora') && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $querystring .= 'JOIN p.id_programacion pr LEFT JOIN pr.id_emisora e ';
            endif;

            if($session->get('nombre') || $session->get('duracion_desde') || $session->get('duracion_hasta') || $session->get('programacion') || $session->get('emisora')):
                $querystring .= 'WHERE ';
            endif;

            if($session->get('nombre')):
                /* query que verifica si hay SIMILITUD desde el principio (_%%) 
                o desde el final (%%_) o si es igual */
                $querystring .= "p.nombre LIKE '_%" . $session->get('nombre') . "%' or p.nombre LIKE '%" . $session->get('nombre') . "_%' or p.nombre = '" . $session->get('nombre') . "' ";

                if($session->get('duracion_desde') || $session->get('duracion_hasta') || $session->get('emisora') || $session->get('programacion')):
                    $querystring .= 'AND ';
                endif;
            endif;

            if($session->get('programacion')):
                $querystring .= 'p.id_programacion = ' . $session->get('programacion') . ' ';

                if($session->get('duracion_desde') || $session->get('duracion_hasta') || $session->get('emisora')):
                    $querystring .= 'AND ';
                endif;
            endif;

            if($session->get('emisora')):
                $querystring .= 'pr.id_emisora = ' . $session->get('emisora') . ' ';

                if(!in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                    $querystring .= 'AND e.id_ciudad = ' . $user->getIdAgencia()->getIdCiudad()->getId() . ' ';
                endif;

                if($session->get('duracion_desde') || $session->get('duracion_hasta')):
                    $querystring .= 'AND ';
                endif;
            endif;

            if($session->get('duracion_desde')):
                $querystring .= 'p.duracion_desde >= :duracion_desde ';

                if($session->get('duracion_hasta')):
                    $querystring .= 'AND ';
                endif;
            endif;

            if($session->get('duracion_hasta')):
                $querystring .= 'p.duracion_hasta <= :duracion_hasta ';
            endif;

            if(!$session->get('emisora') && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $querystring .= 'AND e.id_ciudad = ' . $user->getIdAgencia()->getIdCiudad()->getId() . ' ';
            endif;

            if($session->get('duracion_desde') && $session->get('duracion_hasta')):
                $query = $em->createQuery($querystring)->setParameters(array('duracion_desde' => $session->get('duracion_desde'), 'duracion_hasta' => $session->get('duracion_hasta')));
            elseif($session->get('duracion_desde') && !$session->get('duracion_hasta')):
                $query = $em->createQuery($querystring)->setParameter('duracion_desde', $session->get('duracion_desde'));
            elseif($session->get('duracion_hasta') && !$session->get('duracion_desde')):
                $query = $em->createQuery($querystring)->setParameter('duracion_hasta', $session->get('duracion_hasta'));
            else:
                $query = $em->createQuery($querystring);
            endif;

            $programas = $query->getResult();
        else:
            if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
                $query = $repository->createQueryBuilder('p')
                            ->getQuery();
            else:
                $query = $repository->createQueryBuilder('p')
                    ->join('p.id_programacion', 'pr')
                    ->leftJoin('pr.id_emisora', 'e')
                    ->where('e.id_ciudad = :id_ciudad')
                    ->setParameter('id_ciudad', $user->getIdAgencia()->getIdCiudad())
                    ->getQuery();
            endif;

            $programas = $query->getResult();
        endif;

        if (!$programas):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Programas', 'Nuevo' => $this->generateUrl('insertProgramas'), 'Restablecer' => $this->generateUrl('showProgramas') . '/1/1'));
        endif;

        $adapter = new ArrayAdapter($programas);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('Programas')));

        return $this->render('ProgramasBundle::showProgramas.html.twig', array('form' => $form->createView(), 'programas' => $programas, 'pager' => $pager));
    }
    
    public function showOneAction($id, Request $request) {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            if($id2 != 'yes'):
                $programa = $this->getDoctrine()
                    ->getRepository('ProgramasBundle:Programas')
                    ->find($id2);
            endif;
        endforeach;
        
        $formType = new EditProgramasType($user->getIdAgencia()->getIdCiudad(), $user->getRoles());
        $form = $this->createForm($formType, $programa);
         
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($programa);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'notice', 'El programa ' . $programa->getIdPrograma() . ' ha sido editado con éxito.'
                );

                return $this->redirect($this->generateUrl('showProgramas'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
                                    'errors' => $errors,
                                ));
            endif;
        endif;
        
        return $this->render('ProgramasBundle::showPrograma.html.twig', array('form' => $form->createView(), 'programa' => $programa));
    }
    
    public function deleteAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
        	// 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $programas = $em->getRepository('ProgramasBundle:Programas')->findOneBy(array('id_programa' => $id2));

                $em->remove($programas);
                try {
                    $em->flush();
                    
                    $this->get('session')->getFlashBag()->add(
                            'notice', 'El programa "' . $programas->getNombre() . '" ha sido borrado con éxito.'
                    );
                } catch (\Doctrine\DBAL\DBALException $ex) {
                    $this->get('session')->getFlashBag()->add(
                            'error', 'El programa "' . $programas->getNombre() . '" no ha podido ser borrado. Para hacerlo, por favor elimine las grillas de este programa.'
                    );
                }
                
                $this->container->get('doctrine')->resetEntityManager();
            endif;
        endforeach;

        return $this->redirect($this->generateUrl('showProgramas'));
    }
}
?>
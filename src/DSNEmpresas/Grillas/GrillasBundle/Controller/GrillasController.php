<?php

namespace DSNEmpresas\Grillas\GrillasBundle\Controller;

# Funciones del kernel de Symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
# Entities
use MediterraneoFM\MediterraneoFMBundle\Entity\Grillas;
# Types
use DSNEmpresas\Grillas\GrillasBundle\Form\InsertGrillasType;

class GrillasController extends Controller {
    
    public function insertAction(Request $request) {
        # Objeto que modifico
        $grillas = new Grillas();
        # Formulario que modifico
        $formType = new InsertGrillasType();
        $form = $this->createForm($formType, $grillas);

        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($grillas);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->persist($grillas);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La grilla ' . $grillas->getId() . ' ha sido agregada con éxito.'
                        );
                
                return $this->redirect($this->generateUrl('insertGrillas'));
            else:
                return $this->render('MediterraneoFMBundle::validation.html.twig', array(
        'errors' => $errors,
    ));
            endif;
        endif;
        
        return $this->render('GrillasBundle::insertGrillas.html.twig', array('form' => $form->createView()));
    }
    
    public function getGrillas($select = 'all') {
        $user = $this->container->get('security.context')->getToken()->getUser();

        $tiposmenciones = $this->getDoctrine()
                            ->getRepository('MediterraneoFMBundle:TiposMenciones')
                            ->findBy(array(), array('nro_menciones' => 'ASC'));

        $programas = $this->getDoctrine()
                ->getRepository('ProgramasBundle:Programas')
                ->findBy(array(), array('duracion_desde' => 'ASC'));

        if(in_array('ROLE_SUPER_ADMIN', $user->getRoles())):
            if($select == 'all'):
               $emisoras = $this->getDoctrine()->getRepository('EmisorasBundle:Emisoras')->findAll();
            else:
                $emisoras = $this->getDoctrine()->getRepository('EmisorasBundle:Emisoras')->findBy(array('id_emisora' => $select));
            endif;
        else:
            if($select == 'all'):
                $emisoras = $this->getDoctrine()->getRepository('EmisorasBundle:Emisoras')->findBy(array('id_ciudad' => $user->getIdAgencia()->getIdCiudad()));
            else:
                $emisoras = $this->getDoctrine()->getRepository('EmisorasBundle:Emisoras')->findBy(array('id_ciudad' => $user->getIdAgencia()->getIdCiudad(), 'id_emisora' => $select));
            endif;
        endif;

        $programaciones = $this->getDoctrine()->getRepository('ProgramacionesBundle:Programaciones')->findBy(array('is_active' => 1));

        $grillas = $this->getDoctrine()
                ->getRepository('MediterraneoFMBundle:Grillas')
                ->findAll();
       
        if (!$grillas):
            return array();
        endif;
        
        $salidas = array();
        foreach($programas as $programa):
            $salidas[$programa->getIdPrograma()] = array();
            foreach($tiposmenciones as $tipomencion):
                $salidas[$programa->getIdPrograma()][$tipomencion->getNroMenciones()] = 0;
            endforeach;
        endforeach;
        foreach($grillas as $grilla):
            foreach($tiposmenciones as $tipomencion):
                if($grilla->getIdTiposMenciones()->getNroMenciones() == $tipomencion->getNroMenciones()):
                    $salidas[$grilla->getIdPrograma()->getIdPrograma()][$tipomencion->getNroMenciones()] = $grilla->getNroSalidas();
                endif;
            endforeach;
        endforeach;
        
        return array('salidas' => $salidas, 'grillas' => $grillas, 'tiposmenciones' => $tiposmenciones, 'programas' => $programas, 'emisoras' => $emisoras, 'programaciones' => $programaciones);
    }
    
    public function showAction($select) {
        $grillas = $this->getGrillas($select);
        
        if($grillas):
            return $this->render('GrillasBundle::showGrillas.html.twig', array('grillas' => $grillas));
        else:
            return $this->render('GrillasBundle::showGrillas.html.twig');
        endif;
    }
}

?>
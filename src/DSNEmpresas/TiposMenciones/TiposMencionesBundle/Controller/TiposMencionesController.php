<?php

namespace DSNEmpresas\TiposMenciones\TiposMencionesBundle\Controller;

# Funciones del kernel de Symfony
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
# Entities
use MediterraneoFM\MediterraneoFMBundle\Entity\TiposMenciones;
# Types
use DSNEmpresas\TiposMenciones\TiposMencionesBundle\Form\InsertTiposMencionesType;
use DSNEmpresas\TiposMenciones\TiposMencionesBundle\Form\EditTiposMencionesType;

class TiposMencionesController extends Controller {
    
    public function insertAction(Request $request) {
        # Objeto que modifico
        $tiposmenciones = new TiposMenciones();
        # Formulario que modifico
        $formType = new InsertTiposMencionesType();
        $form = $this->createForm($formType, $tiposmenciones);

        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            #$data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($tiposmenciones);
            
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->persist($tiposmenciones);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La mención ' . $tiposmenciones->getId() . ' ha sido agregada con éxito.'
                        );   
                
                return $this->redirect($this->generateUrl('showTiposMenciones'));
            else:
                return $this->render('TiposMencionesBundle::insertTiposMenciones.html.twig', array('form' => $form->createView(), 
        'errors' => $errors,
    ));
            endif;
        endif;
        
        return $this->render('TiposMencionesBundle::insertTiposMenciones.html.twig', array('form' => $form->createView(), 'errors' => array()));
    }

    public function showOneAction($id, Request $request) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            if($id2 != 'yes'):
                $mencion = $this->getDoctrine()
                    ->getRepository('MediterraneoFMBundle:TiposMenciones')
                    ->find($id2);
            endif;
        endforeach;
        
        $formType = new EditTiposMencionesType();
        $form = $this->createForm($formType, $mencion);
         
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($mencion);
            
            #if(is_integer($data->getNroMenciones()) && $this->UniqueValid($data)): # Validación manual debido a ser una edición
            if($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'La mencion ' . $mencion->getId() . ' ha sido editada con éxito.'
                        );

                return $this->redirect($this->generateUrl('showTiposMenciones'));
            else:
                return $this->render('TiposMencionesBundle::showTipoMencion.html.twig', array(
                            'form' => $form->createView(),
                            'tipomencion' => $mencion,
                            'errors' => array(0 => array('message' => 'El valor "' . $data->getNroMenciones() . '" ya existe en la base de datos. Debe ser de tipo numerico.')),
                        ));
            endif;
        endif;
        
        return $this->render('TiposMencionesBundle::showTipoMencion.html.twig', array('form' => $form->createView(), 'tipomencion' => $mencion, 'errors' => array()));
    }

    protected function UniqueValid($data) {
        $menciones = $this->getDoctrine()->getRepository('MediterraneoFMBundle:TiposMenciones')->findAll();

        if($menciones):
            foreach($menciones as $mencion):
                if($data->getNroMenciones() == $mencion->getNroMenciones() && $data->getId() != $mencion->getId()):
                    return false;
                endif;
            endforeach;
        else:
            return true;
        endif;

        return true;
    }

    public function deleteAction($id) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se selecciona todo.
            if($id2 != 'yes'):
                $em = $this->getDoctrine()->getManager();
                $tipomencion = $em->getRepository('MediterraneoFMBundle:TiposMenciones')->findOneBy(array('id' => $id2));

                $em->remove($tipomencion);
                try {
                    $em->flush();
                    
                    $this->get('session')->getFlashBag()->add(
                        'notice', 'La mencion "' . $tipomencion->getNroMenciones() . '" ha sido borrada con éxito.'
                    );
                } catch (\Doctrine\DBAL\DBALException $ex) {
                    $this->get('session')->getFlashBag()->add(
                        'error', 'La mencion "' . $tipomencion->getNroMenciones() . '" no ha podido ser borrada. Para hacerlo, por favor elimine las pautas y las grillas de este tipo de mención.'
                    );
                }
                
                $this->container->get('doctrine')->resetEntityManager();
            endif;
        endforeach;
        
        return $this->redirect($this->generateUrl('showTiposMenciones'));
    }
}

?>

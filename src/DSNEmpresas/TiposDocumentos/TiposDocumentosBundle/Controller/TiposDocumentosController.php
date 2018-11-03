<?php

namespace DSNEmpresas\TiposDocumentos\TiposDocumentosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
# Paginador
use MakerLabs\PagerBundle\Pager;
use MakerLabs\PagerBundle\Adapter\ArrayAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
# Entidades
use MediterraneoFM\MediterraneoFMBundle\Entity\TiposDocumentos;
# Formularios
use DSNEmpresas\TiposDocumentos\TiposDocumentosBundle\Form\InsertTiposDocumentosType;
use DSNEmpresas\TiposDocumentos\TiposDocumentosBundle\Form\EditTiposDocumentosType;

class TiposDocumentosController extends Controller {

    public function insertAction(Request $request) {
        $tiposdocumentos = new TiposDocumentos();
        $formType = new InsertTiposDocumentosType();
        $form = $this->createForm($formType, $tiposdocumentos);

        $form->handleRequest($request);
        $validator = $this->get('validator');
        $errors = $validator->validate($tiposdocumentos);

        if($form->isValid()):
        	$em = $this->getDoctrine()->getManager();
            $em->persist($tiposdocumentos);
            $em->flush();
            $this->get('session')->getFlashBag()->add(
                    'notice', 'El tipo de documento ' . $tiposdocumentos->getId() . ' ha sido agregado con éxito.'
                );
            
            return $this->redirect($this->generateUrl('showTiposDocumentos'));
        else:
            if (count($errors) > 0):
                  return $this->render('TiposDocumentosBundle::insertTiposDocumentos.html.twig', array('form' => $form->createView(),'errors' => $errors));
              endif;
    	endif;

        return $this->render('TiposDocumentosBundle::insertTiposDocumentos.html.twig', array('form' => $form->createView(), 'errors' => array()));
    }

    public function showOneAction($id, Request $request) {
        $id2 = explode(',', $id);
        foreach($id2 as $id2):
            if($id2 != 'yes'):
                $documento = $this->getDoctrine()
                    ->getRepository('MediterraneoFMBundle:TiposDocumentos')
                    ->find($id2);
            endif;
        endforeach;
        
        $formType = new EditTiposDocumentosType();
        $form = $this->createForm($formType, $documento);
         
        if ($request->getMethod() == 'POST'):
            $form->bind($request);
            $data = $form->getData();
            $validator = $this->get('validator');
            $errors = $validator->validate($documento);
            
            #if($this->UniqueValid($data)): # Validación manual debido a ser una edición
            if ($form->isValid()):
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'El tipo de documento ' . $documento->getId() . ' ha sido editado con éxito.'
                        );

                return $this->redirect($this->generateUrl('showTiposDocumentos'));
            else:
                return $this->render('TiposDocumentosBundle::showTipoDocumento.html.twig', array(
                            'form' => $form->createView(),
                            'tipodocumento' => $documento,
                            'errors' => array(0 => array('message' => 'El valor "' . $data->getLeyenda() . '" ya existe en la base de datos.')),
                        ));
            endif;
        endif;
        
        return $this->render('TiposDocumentosBundle::showTipoDocumento.html.twig', array('form' => $form->createView(), 'tipodocumento' => $documento, 'errors' => array()));
    }

    /**
     *
     * @Route("/tiposdocumentos/show/{page}", defaults={"page"=1}, name="showTiposDocumentos")
     * 
     */
    public function showAction($page, Request $request) {
        $tiposdocumentos = $this->getDoctrine()->getRepository('MediterraneoFMBundle:TiposDocumentos')->findAll();
        
        $session = $this->get('session');
        
        if($request->request->get('TiposDocumentos') != null):
            $session->set('TiposDocumentos', $request->request->get('TiposDocumentos'));
        endif;

        if (!$tiposdocumentos):
            return $this->render('TemplateBundle::showVacio.html.twig', array('entity' => 'Tipos documentos'));
        endif;

        $adapter = new ArrayAdapter($tiposdocumentos);

        $pager = new Pager($adapter, array('page' => $page, 'limit' => $session->get('TiposDocumentos')));

        return $this->render('TiposDocumentosBundle::showTiposDocumentos.html.twig', array('tiposdocumentos' => $tiposdocumentos, 'pager' => $pager));
    }

    public function deleteAction($id) {
    	$id2 = explode(',', $id);
    	$em = $this->getDoctrine()->getManager();
        foreach($id2 as $id2):
            // 'yes' valor que también envía el formulario si se seleccionan todas las ordenes.
            if($id2 != 'yes'):
                $tipodocumento = $this->getDoctrine()->getRepository('MediterraneoFMBundle:TiposDocumentos')->findOneBy(array('id' => $id2));

                $em->remove($tipodocumento);
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                        'notice', 'El tipo de documento ' . $id2 . ' ha sido borrado con éxito.'
                );
            endif;
        endforeach;
        
        return $this->redirect($this->generateUrl('showTiposDocumentos'));
    }
}

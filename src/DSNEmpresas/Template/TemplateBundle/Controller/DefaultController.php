<?php

namespace DSNEmpresas\Template\TemplateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

    public function searchEntityAction(Request $request) {
    	$property = trim($request->request->get('property'));
    	$search = trim($request->request->get('search'));
        $class = trim($request->request->get('class'));
        
    	if($property == null || $search == null):
    		die('Por favor, complete correctamente los campos.');
    	endif;

        $em = $this->getDoctrine()->getManager();

        $querystring = "SELECT c FROM " . $class . " c WHERE c." . $property . " LIKE '%" . $search . "%' ";

        $query = $em->createQuery($querystring);

        $results = $query->getResult();

    	if(!$results):
    		die('No se han encontrado resultados de "' . $search . '" en ' . $class . '.' . $property);
    	else:
        	return $this->render('TemplateBundle:Form:searchResults.html.twig', array('results' => $results, 'property' => $property));
        endif;
    }
    
    public function accesoDenegadoAction() {
        return $this->render('TemplateBundle::accesoDenegado.html.twig');
    }
}

<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MediterraneoFM\MediterraneoFMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Hackzilla\BarcodeBundle\Utility\Barcode;
use MediterraneoFM\MediterraneoFMBundle\Entity\CodigoBarras;

class TestController extends Controller {
   
    public function testAction(Request $request) {
        $cb = new CodigoBarras();
        $form = $this->createFormBuilder($cb)
                ->add('codigo', 'text')
                ->add('codificacion', 'choice', array(
                    'choices' => array(
                        Barcode::ENCODING_ANY => 'Cualquiera',
                        Barcode::ENCODING_EAN => 'EAN', // admite solo 12 o 13 caracteres numericos
                        Barcode::ENCODING_UPC => 'UPC', // admite solo 6 caracteres numericos
                        Barcode::ENCODING_ISBN => 'ISBN(EAN-13)', // admite solo 9 o 10 caracteres numericos
                        Barcode::ENCODING_39 => '39',
                        Barcode::ENCODING_128 => '128',
                        Barcode::ENCODING_128C => '128C', // admite cantidad par de caracteres numericos
                        Barcode::ENCODING_128B => '128B',
                        Barcode::ENCODING_I25 => 'I25',
                        Barcode::ENCODING_128RAW => '128RAW', // no funciona
                        Barcode::ENCODING_CBR => 'CBR',
                        Barcode::ENCODING_MSI => 'MSI',
                        Barcode::ENCODING_PLS => 'PLS'
                    )
                ))
                ->add('escala', 'choice', array(
                    'choices' => array(
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                        6 => '6',
                        7 => '7'
                    )
                ))
                ->add('save', 'submit', array('label' => 'Crear codigo'))
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isValid()):
            $barcode = $this->container->get('hackzilla_barcode');
            $barcode->setGenbarcodeLocation('/usr/local/bin/genbarcode');
            $barcode->setEncoding($form->get('codificacion')->getData());
            $barcode->setScale($form->get('escala')->getData());
            $cod = $this->validateCod($form->get('codigo')->getData(), $form->get('codificacion')->getData());
            
            return $this->render('MediterraneoFMBundle::test.html.twig', array('form' => $form->createView(), 'codtxt' => $cod, 'codigo' => $barcode->outputHtml($cod)));
        endif;
        
        return $this->render('MediterraneoFMBundle::test.html.twig', array('form' => $form->createView()));
        
    }

    private function validateCod($cod, $codificacion) {
        $caracteres = str_split($cod); // Array de caracteres

        foreach($caracteres as $caracter):
            if(!is_numeric($caracter)):
                echo 'Se ha eliminado el caracter ' . $caracter . '<br>';
                unset($caracteres[array_search($caracter, $caracteres)]);
            endif;
        endforeach;

        if($codificacion == Barcode::ENCODING_EAN):
            $long = count($caracteres);
            if($long < 12):
                $dif = 12 - $long;
                for($i = 0;$i < $dif;$i++) {
                    $caracteres[] = '0';
                }
            endif;

            if($long > 13):
                for($i = 13;$i <= $long;$i++) {
                    unset($caracteres[$i]);
                }
            endif;
        endif;

        return implode($caracteres); // retorna string
    }
}
?>

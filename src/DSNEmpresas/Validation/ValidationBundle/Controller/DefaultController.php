<?php

namespace DSNEmpresas\Validation\ValidationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;

class DefaultController extends Controller {
    private $valid = true;
    var $form;
    var $dataClass;
    var $serviceContainer;
    
    public function __construct($serviceContainer) {
        $this->serviceContainer = $serviceContainer;
    }
    
    public function validate($form, $denyChilds = array()) {
        // $denychilds = childs that won't be validated
        $this->form = $form;
        // Check revied information is correct
        $this->dataClass = $this->form->getConfig()->getDataClass();
        $this->checkRecievedData($denyChilds);
        
        $childs = $form->createView()->children;
        
        foreach($childs as $child):
            /* Check recursively if the child has more childs
             * and then validate it.
             */
            $this->validateChildrenParentRelation($child, $denyChilds);
        endforeach;
    }
    
    private function checkRecievedData($childs) {
        // Form must be a form
        if(!($this->form instanceof \Symfony\Component\Form\Form)):
            throw new Exception('The first parameter must be form created with the form type class.');
        endif;
        
        if($childs): // if child exist
            if(!is_array($childs)): // They must be an array of childs
                throw new Exception('The second parameter must be the child\'s array that won\'t be validated.');
            endif;
            
            
            if($this->dataClass != null):
                foreach($childs as $child):
                    $fieldsExist = $this->childExistRecursive($child);

                    if(!$fieldsExist):
                        throw new Exception("The child '" . $child . "' in childs array does not exist in class metadata.");
                    endif;
                endforeach;
            endif;
        endif;
    }
    
    private function childExistRecursive($child) {
        // if the child has more childs
        if(property_exists($child, 'children')):
            if(is_array($child->children) && count($child->children) > 0):
                foreach($child->children as $secondChild): // get all this childs and call again this function
                    $this->childExistRecursive($secondChild);
                endforeach;
            else:            
                return $this->childExist($child);
            endif;
        else:
            return false;
        endif;
        
        return true;
    }
    
    private function childExist($child) {
        $doctrineServ = $this->serviceContainer
                        ->get('doctrine')
                        ->getManager()
                        ->getMetadataFactory();
            
        // Check if doctrine has metadata for this class
        #if($doctrineServ->hasMetaDataFor($this->dataClass)):
            $classMetadata = $doctrineServ->getMetaDataFor($this->dataClass);
        #endif;

        $childOptions = $this->form->get($child)->getConfig()->getOptions();

        if($childOptions['mapped']): // check the next only if the child belongs to the dataClass
            $BA = 0;
            $BA2 = 0;
            if($classMetadata):
                foreach($classMetadata->fieldNames as $field):
                    if($child != $field): // child must be a field of the class
                        $BA = 1;
                    endif;
                endforeach;

                foreach($classMetadata->columnNames as $column):
                    if($child != $column): // or child must be a column of the class (column name could be different of the field name)
                        $BA2 = 1;
                    endif;
                endforeach;

                if($BA && $BA2):
                    return false;
                endif;
            endif;
        endif;
    }
    
    public function isValid() {
        // Public function to check if the validated form is valid
        if($this->valid):
            return true;
        else:
            return false;
        endif;
    }
    
    private function validateChildrenParentRelation($child, $denyChilds) {
        // if the child has more childs
        if(!in_array($child->vars['name'], $denyChilds)):
            if(is_array($child->children) && count($child->children) > 0):
                foreach($child->children as $secondChild): // get all this childs and call again this function
                    $this->validateChildrenParentRelation($secondChild, $denyChilds);
                endforeach;
            else: // if it has no more childs validate it.
                $this->prepareValidation($child);
            endif;
        endif;
    }
    
    private function prepareValidation($child) {
        $data = $this->getChildData($child); // get the data if this child is correct
        $type = $this->getChildTypeRecursive($child); // get child type(integer, date, string, datetime, etc), so we can know how to validate it
        
        if($data && $type):
            $this->validateChild($data, $type); // validate child taking in count the data type.
        endif;
    }
    
    private function getChildData($child) {
        if(is_array($child->children) && count($child->children) > 0):
            foreach($child->children as $secondChild):
                $this->getChildData($secondChild);
            endforeach;
        else:
            return $child->vars['value'];
        endif;
    }
    
    private function getChildTypeRecursive($child) {        
        if(is_array($child->children) && count($child->children) > 0):
            foreach($child->children as $secondChild):
                $this->getChildTypeRecursive($secondChild);
            endforeach;
        else:
            return $this->getChildType($child);
        endif;
    }
    
    private function getChildType($child) {
        // Get child's orm data
        $doctrineServ = $this->serviceContainer
                    ->get('doctrine')
                    ->getManager()
                    ->getMetadataFactory();

        // if exist metadata for this class
        #if($doctrineServ->hasMetadataFor($this->dataClass)):
            $doctrineMetadata = $doctrineServ->getMetaDataFor($this->dataClass);
        
            // if I can't get child's type, search for the parent type.
            if($doctrineMetadata->getTypeOfField($child->vars['name']) == null):
                $parent = $this->searchParent($child);
                $parentOptions = $parent->getConfig()->getOptions();
                if(isset($parentOptions['input'])): // if child has't got input option, such as choice field type, don't need to validate
                    return $parentOptions['input'];
                endif;
            else:
                return $doctrineMetadata->getTypeOfField($child->vars['name']);
            endif;
        #endif;
    }
    
    private function searchParent($child) {
        $parent = $child->parent->parent;
        if(is_object($parent) && ($parent instanceof \Symfony\Component\Form\FormView)):
            $par = $this->searchParent($child->parent);
        
            return $par->get($child->vars['name']);
        endif;
        
        return $this->form->get($child->vars['name']);
    }
    
    private function validateChild($data, $type){
        if($data): // check if data exist (the request could be not POST)
            // Validate data send taking in count the data type
            if($type == 'integer' || $type == 'smallint' || $type == 'bigint'):
                $this->validateInteger($data);
            elseif($type == 'decimal' || $type == 'float'):
                $this->validateDecimal($data);
            elseif($type == 'text' || $type == 'string'):
                $this->validateString($data);
            elseif($type == 'date' || $type == 'datetime' || $type == 'time'):
                $this->validateDateTime($data);
            endif;
        endif;
    }
    
    private function validateInteger($data) {
        /* no debería tener puntos, comas, ni letras
         * ni simbolos como $, U$S,
         * solo numeros, puntos y comas.
         */
        echo 'Validate integer: ' . $data . '<br>';
    }
    
    private function validateDecimal($data) {
        /* no debería tener letras ni simbolos como $, U$S
         * solo números
         */
        echo 'Validate decimal: ' . $data . '<br>';
    }
    
    private function validateString($data) {
        /* que no contenga caracteres especiales
         * como html o script
         */
        echo 'Validate string: ' . $data . '<br>';
    }
    
    private function validateDateTime($data) {
        /* teniendo en cuenta el format ingresado en el form type
         * y utilizando la funcion checkdate() de php
         */
        echo 'Validate datetime: ' . $data . '<br>';
    }
}

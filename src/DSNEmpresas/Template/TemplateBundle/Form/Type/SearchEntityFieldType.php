<?php

namespace DSNEmpresas\Template\TemplateBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SearchEntityFieldType extends AbstractType {
    var $doctrine;

    public function __construct($doctrine) {
        $this->doctrine = $doctrine;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $choices = array();
        $classMeta = $this->doctrine->getMetadataFactory()->getMetadataFor($options['class']);
        foreach($classMeta->fieldNames as $field):
            if(!$classMeta->isIdentifier($field)):
                $choices[$field] = ucfirst($field);
            endif;
        endforeach;

        $builder
            ->add('result', 'entity', array(
                'class' => $options['class'],
                'property' => $options['property'],
                'attr' => array('class' => 'result'),
            ))
            ->add('property', 'choice', array(
                'choices' => $choices,
                'attr' => array('class' => 'property', 'searchOn' => $options['class']),
            ))
            ->add('search', 'text', array(
                'attr' => array('class' => 'search'),
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => '',
            'property' => '',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'searchEntity';
    }
}

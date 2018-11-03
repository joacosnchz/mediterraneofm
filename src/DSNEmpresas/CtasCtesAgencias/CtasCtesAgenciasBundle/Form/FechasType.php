<?php

namespace DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FechasType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fecha_desde', 'date', array(
                'label' => 'Fecha desde (*)',
                'required' => true,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => array('placeholder' => 'Fecha desde' ,'title' => 'Click sobre el campo para completarlo.', 'size' => 8),
            ))
            ->add('fecha_hasta', 'date', array(
                'label' => 'Fecha hasta (*)',
                'required' => true,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => array('placeholder' => 'Fecha hasta', 'title' => 'Click sobre el campo para completarlo.', 'size' => 8),
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_fechastype';
    }
}
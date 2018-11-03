<?php

namespace DSNEmpresas\OrdenPub\OrdenPubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrdenTarifasType extends AbstractType {
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('fecha_desde', null, array(
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => array('size' => 7, 'title' => 'Click sobre el campo para completarlo.'),
                'required' => true,
            ))
            ->add('fecha_hasta', null, array(
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => array('size' => 7, 'title' => 'Click sobre el campo para completarlo.'),
                'required' => true,
            ))
            ->add('id_costotarifas', 'hidden', array(
                'required' => true,
                'data_class' => 'DSNEmpresas\Pautas\PautasBundle\Entity\Pautas',
            ))
            ->add('neto', 'money', array(
                'currency' => 'ARS',
                'grouping' => false,
                'precision' => 2,
                'label' => 'Subtotal',
                'attr' => array(
                            'size' => 4,
                            'onkeypress' => "return justNumbers(event);"
                            ),
                'required' => true,
            ))
            ->add('descuento', null, array(
                'label' => 'Descuento (%)',
                'attr' => array(
                            'size' => 2,
                            'onkeypress' => "return justNumbers(event);",
                            'onchange' => 'lala()',
                            ),
                'required' => false,
            ))
            ->add('recargo', null, array(
                'label' => 'Recargo (%)',
                'attr' => array(
                            'size' => 2,
                            'onkeypress' => "return justNumbers(event);"
                            ),
                'required' => false,
            ))
            ->add('total', 'money', array(
                'currency' => 'ARS',
                'grouping' => false,
                'precision' => 2,
                'label' => 'Total',
                'attr' => array(
                            'size' => 4,
                            'onkeypress' => "return justNumbers(event);"
                            ),
                'read_only' => true,
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenTarifas',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'task_item',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_ordentarifas';
    }
}

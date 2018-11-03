<?php

namespace DSNEmpresas\OrdenPub\OrdenPubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InsertOrdenesAnuladasType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('motivoAnula', 'textarea', array(
                'required' => true,
                'label' => 'Motivo anulacion (*)',
                "attr" => array("cols" => 50, "rows" => 8, "placeholder" => 'Ingrese aquí el motivo por el cual se anula/n la/s orden/es.'),
            ))
            ->add('save', 'submit', array(
                'label' => 'Aceptar',
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\ordenesAnuladas',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'task_item',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_insertordenesanuladas';
    }
}

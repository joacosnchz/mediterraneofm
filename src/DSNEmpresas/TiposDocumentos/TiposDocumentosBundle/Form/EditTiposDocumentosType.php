<?php

namespace DSNEmpresas\TiposDocumentos\TiposDocumentosBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditTiposDocumentosType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tipoMovimiento', 'choice', array(
                'choices' => array('D' => 'Debe', 'H' => 'Haber'),
                'required' => true,
                'label' => 'Tipo movimiento (*)',
            ))
            ->add('descripcion', null, array(
                'required' => false,
            ))
            ->add('leyenda', null, array(
                'required' => true,
                'label' => 'Leyenda (*)',
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MediterraneoFM\MediterraneoFMBundle\Entity\TiposDocumentos',
            'csrf_protection' => false,
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
        return 'mediterraneofm_mediterraneofmbundle_edittiposdocumentos';
    }
}

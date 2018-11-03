<?php

namespace DSNEmpresas\Programas\ProgramasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DSNEmpresas\Programas\ProgramasBundle\Form\ProgramasFilterType2;

class ProgramasFilterType extends AbstractType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    var $rol;
    var $id_ciudad_agencia;

    public function __construct($id_ciudad_agencia, $rol, $em, $default) {
        $this->id_ciudad_agencia = $id_ciudad_agencia;
        $this->rol = $rol;
        $this->em = $em;
        $this->default = $default;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_ciudad_agencia = $this->id_ciudad_agencia;
        $rol = $this->rol;

        $builder
            ->add('nombre', null, array(
                'label' => false,
                'required' => false,
                'attr' => array('placeholder' => 'Nombre'),
            ))
            ->add('duracion_desde', null, array(
                'label' => false,
                'required' => false,
                'widget' => 'single_text',
                'attr' => array('placeholder' => 'Duracion desde', 'size' => 7),
            ))
            ->add('duracion_hasta', null, array(
                'label' => false,
                'required' => false,
                'widget' => 'single_text',
                'attr' => array('placeholder' => 'Duracion hasta', 'size' => 7),
            ))
            ->add('id_programacion', new ProgramasFilterType2($id_ciudad_agencia, $rol, $this->em, $this->default), array(
                'label' => false,
            ))
            ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Programas\ProgramasBundle\Entity\Programas',
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
        return 'mediterraneofm_mediterraneofmbundle_programasfiltertype';
    }
}

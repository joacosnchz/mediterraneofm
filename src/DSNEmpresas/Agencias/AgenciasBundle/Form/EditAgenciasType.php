<?php

namespace DSNEmpresas\Agencias\AgenciasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditAgenciasType extends AbstractType {
    var $path;
    
    public function __construct($path) {
        $this->path = $path;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('nombre', null, array(
                'required' => true,
                'label' => 'Nombre (*)'
            ))
            ->add('apellido', null, array(
                'required' => true,
                'label' => 'Apellido (*)',
            ))
            ->add('razonSocial', null, array(
                'required' => true,
                'label' => 'Razon Social (*)',
            ))
            ->add('direccion')
            ->add('id_ciudad', 'entity', array(
                'class' => 'CiudadesBundle:Ciudades',
                'property' => 'nombre',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c');
                },
                'required' => true,
                'label' => 'Zona (*)',
            ))
            ->add('telefono_particular')
            ->add('telefono_comercial', null, array(
                'required' => true,
                'label' => 'Telefono comercial (*)',
            ))
            ->add('celular')
            ->add('email', null, array(
                'required' => true,
                'label' => 'Email (*)',
            ))
            ->add('idComision', 'entity', array(
                'class' => 'ComisionesBundle:Comisiones',
                'property' => 'descripcion',
                'required' => true,
                'label' => 'Comisión (*)',
            ))
            ->add('is_active', 'checkbox', array(
                'label' => 'Activo',
                'required' => false,
            ))
            ->add('imagen', 'file', array(
                'label' => 'Logo (*)',
                'required' => false,
            ))
            ->add('membrete', null, array(
                'label' => 'Membrete (*)',
                'required' => true,
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Agencias\AgenciasBundle\Entity\Agencias',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'task_item',
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_editagenciastype';
    }
}

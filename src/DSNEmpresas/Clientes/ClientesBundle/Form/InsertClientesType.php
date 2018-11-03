<?php

namespace DSNEmpresas\Clientes\ClientesBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InsertClientesType extends AbstractType {
    var $rol;

    public function __construct($rol) {
        $this->rol = $rol;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $rol = $this->rol;

        $builder
            ->add('nombre', null, array(
                'label' => 'Nombre (*)',
                'required' => true,
            ))
            ->add('apellido', null, array(
                'label' => 'Apellido (*)',
                'required' => true,
            ))
            ->add('direccion', null, array(
                'required' => false,
            ))
            ->add('razon_social', null, array(
                'required' => false,
            ))
            ->add('comercio', null, array(
                'label' => 'Comercio (*)',
                'required' => true,
            ))
            ->add('dni', null, array(
                'required' => false,
            ))
            ->add('cuit', null, array(
                'label' => 'Cuit',
                'required' => false,
            ))
            ->add('email', null, array(
                'required' => false,
            ))
            /* ->add('telefono_particular', null, array(
                'required' => false,
                'label' => 'Teléfono particular',
            )) */
            ->add('telefono_comercial', null, array(
                'required' => true,
                'label' => 'Teléfono comercial (*)',
            ))
            ->add('celular', null, array(
                'required' => false,
            ))
            ->add('is_active', 'checkbox', array(
                'label' => 'Activo',
                'required' => false,
            ))
            ->add('iva', 'choice', array(
                'choices' => array('Resp. Inscr.' => 'Resp. Inscr.', 'Consumidor final' => 'Consumidor final', 'Excento/No gravado' => 'Excento/No gravado', 'Import/Export' => 'Import/Export', 'Resp. Monotributo' => 'Resp. Monotributo', 'No categorizada' => 'No categorizada'),
            ));

            if(in_array('ROLE_SUPER_ADMIN', $rol)):
                $builder->add('id_agencia', 'entity', array(
                        'class' => 'AgenciasBundle:Agencias',
                        'property' => 'razonSocial',
                        'required' => true,
                        'label' => 'Agencia (*)',
               ));
            endif;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Clientes\ClientesBundle\Entity\Clientes',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'task_item',
        ));
    }

    public function getName() {
        return 'mediterraneofm_mediterraneofmbundle_insertclientestype';
    }
}

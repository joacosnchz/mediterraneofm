<?php

namespace DSNEmpresas\Clientes\ClientesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientesFilterType extends AbstractType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    var $rol;
    var $defaults;
    var $em;
    
    public function __construct($rol, $defaults, $em) {
        $this->rol = $rol;
        $this->defaults = $defaults;
        $this->em = $em;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                # Nombre o apellido
            ->add('nombre', null, array(
                'required' => false,
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Nombre y/o apellido'
                ),
            ))
                # Razon social o comercio
            ->add('razon_social', null, array(
                'required' => false,
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Razon social o comercio'
                ),
            ));
        
        if($this->defaults['isActive']):
            $builder->add('isActive', 'choice', array(
                    'choices' => array('true' => 'Activado', 'false' => 'No activado'),
                    'empty_value' => 'Seleccione estado',
                    'empty_data' => 'null',
                    'required' => false,
                    'label' => false,
                    'data' => $this->defaults['isActive'],
                    'attr' => array(
                        'onchange' => "this.form.submit()"
                    ),
                ));
        else:
            $builder->add('isActive', 'choice', array(
                    'choices' => array('true' => 'Activado', 'false' => 'No activado'),
                    'empty_value' => 'Seleccione estado',
                    'empty_data' => 'null',
                    'required' => false,
                    'label' => false,
                    'attr' => array(
                        'onchange' => "this.form.submit()"
                    ),
                ));
        endif;
        
        if(in_array('ROLE_SUPER_ADMIN', $this->rol)):
            if($this->defaults['idAgencia']):
                $builder->add('id_agencia', 'entity', array(
                    'class' => 'AgenciasBundle:Agencias',
                    'property' => 'razonSocial',
                    'required' => false,
                    'label' => false,
                    'empty_value' => 'Seleccione agencia',
                    'empty_data' => 'null',
                    'data' => $this->em->getReference('AgenciasBundle:Agencias', $this->defaults['idAgencia']),
                    'attr' => array(
                        'onchange' => "this.form.submit()"
                    ),
                ));
            else:
                $builder->add('id_agencia', 'entity', array(
                    'class' => 'AgenciasBundle:Agencias',
                    'property' => 'razonSocial',
                    'required' => false,
                    'label' => false,
                    'empty_value' => 'Seleccione agencia',
                    'empty_data' => 'null',
                    'attr' => array(
                        'onchange' => "this.form.submit()"
                    ),
                ));
            endif;
        endif;

        $builder->add('orden', 'choice', array(
            'mapped' => false,
            'choices' => array(
                'nombre' => 'Nombre',
                'comercio' => 'Comercio',
                'saldo' => 'Saldo'
            ),
            'required' => false,
            'label' => false,
            'empty_value' => 'Ordenar por:',
            'empty_data' => 'null',
            'attr' => array(
                'onchange' => "this.form.submit()"
            ),
        ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Clientes\ClientesBundle\Entity\Clientes',
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
        return 'mediterraneofm_mediterraneofmbundle_clientesfiltertype';
    }
}

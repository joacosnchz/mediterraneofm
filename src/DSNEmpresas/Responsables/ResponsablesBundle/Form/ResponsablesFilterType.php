<?php

namespace DSNEmpresas\Responsables\ResponsablesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResponsablesFilterType extends AbstractType {
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private $rol;
    private $defaults;
    private $em;
    
    public function __construct($rol, $defaults, $em) {
        $this->rol = $rol;
        $this->defaults = $defaults;
        $this->em = $em;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('nombre', 'text', array(
                'label' => false,
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Nombre y/o apellido',
                )
            ));
        
        if(isset($this->defaults['is_active'])):
            $builder
                ->add('is_active', 'choice', array(
                    'required' => false,
                    'label' => false,
                    'empty_value' => 'Seleccione estado',
                    'empty_data' =>  'null',
                    'data' => $this->defaults['is_active'],
                    'choices' => array('true' => 'Activado', 'false' => 'No activado'),
                    'attr' => array(
                        'onchange' => "this.form.submit()",
                    ),
                ))
            ;
        else:
            $builder
                ->add('is_active', 'choice', array(
                    'required' => false,
                    'label' => false,
                    'empty_value' => 'Seleccione estado',
                    'empty_data' =>  'null',
                    'choices' => array('true' => 'Activado', 'false' => 'No activado'),
                    'attr' => array(
                        'onchange' => "this.form.submit()"
                    ),
                ))
            ;
        endif;
        
        if(in_array('ROLE_SUPER_ADMIN', $this->rol)):
            if(isset($this->defaults['id_agencia'])):
                $builder
                    ->add('id_agencia', 'entity', array(
                        'required' => false,
                        'label' => false,
                        'class' => 'AgenciasBundle:Agencias',
                        'property' => 'razon_social',
                        'empty_value' => 'Seleccione agencia',
                        'empty_data' => 'null',
                        'data' => $this->em->getReference('AgenciasBundle:Agencias', $this->defaults['id_agencia']),
                        'attr' => array(
                            'onchange' => "this.form.submit()"
                        )
                    ))
                ;
            else:
                $builder
                    ->add('id_agencia', 'entity', array(
                        'required' => false,
                        'label' => false,
                        'class' => 'AgenciasBundle:Agencias',
                        'property' => 'razon_social',
                        'empty_value' => 'Seleccione agencia',
                        'empty_data' => 'null',
                        'attr' => array(
                            'onchange' => "this.form.submit()"
                        )
                    ))
                ;
            endif;
        endif;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Responsables\ResponsablesBundle\Entity\Responsables',
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
        return 'mediterraneofm_mediterraneofmbundle_responsablesfilter';
    }
}

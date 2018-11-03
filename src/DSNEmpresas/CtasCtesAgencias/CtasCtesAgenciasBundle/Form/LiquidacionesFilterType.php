<?php

namespace DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LiquidacionesFilterType extends AbstractType {
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    var $rol;
    var $default;
    var $em;

    public function __construct($rol, $default, $em) {
        $this->rol = $rol;
        $this->default = $default;
        $this->em = $em;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('fecha', 'date', array(
                'label' => 'Fecha desde',
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => array(
                    'placeholder' => 'Fecha desde',
                    'title' => 'Click sobre el campo para completarlo.',
                ),
            ))
            ->add('fechaHasta', 'date', array(
                'label' => 'Fecha hasta',
                'required' => false,
                'mapped' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => array(
                    'placeholder' => 'Fecha hasta',
                    'title' => 'Click sobre el campo para completarlo.',
                ),
            ));
        
        if(in_array('ROLE_SUPER_ADMIN', $this->rol)):
            if($this->default['idAgencia']):
                $builder->add('idAgencia', 'entity', array(
                    'class' => 'AgenciasBundle:Agencias',
                    'property' => 'razonSocial',
                    'required' => false,
                    'empty_value' => 'Seleccione agencia',
                    'data' => $this->em->getReference('AgenciasBundle:Agencias', $this->default['idAgencia']),
                ));
            else:
                $builder->add('idAgencia', 'entity', array(
                    'class' => 'AgenciasBundle:Agencias',
                    'property' => 'razonSocial',
                    'required' => false,
                    'empty_value' => 'Seleccione agencia',
                ));
            endif;
        endif;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Entity\Liquidaciones'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_liquidaciones';
    }
}

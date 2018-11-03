<?php

namespace DSNEmpresas\OrdenPub\OrdenPubBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrdenPubFilterType3 extends AbstractType {
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private $id_agencia;
    private $rol;
    private $em;
    private $default;
    
    public function __construct($id_agencia, $rol, $em, $default) {
        $this->id_agencia = $id_agencia;
        $this->rol = $rol;
        $this->em = $em;
        $this->default = $default;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if(in_array('ROLE_SUPER_ADMIN', $this->rol)):
            $builder
                ->add('nombre', 'text', array(
                    'label' => false,
                ))
                ->add('id_agencia', 'text', array(
                    'label' => false,
                ))
            ;
        else:
            $builder
                ->add('nombre', 'text', array(
                    'label' => false,
                ))
            ;
        endif;
        
        $builder
            ->add('nombre')
            ->add('id_agencia')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Clientes\ClientesBundle\Entity\Clientes'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dsnempresas_clientes_clientesbundle_clientes';
    }
}

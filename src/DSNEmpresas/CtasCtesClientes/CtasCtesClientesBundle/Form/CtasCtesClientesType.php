<?php

namespace DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CtasCtesClientesType extends AbstractType {
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    var $rol;
    var $idAgencia;
    
    public function __construct($rol, $idAgencia) {
        $this->rol = $rol;
        $this->idAgencia = $idAgencia;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_agencia = $this->idAgencia;
        
        if(in_array('ROLE_SUPER_ADMIN', $this->rol)):
            $builder->add('idCliente', 'entity', array(
                'class' => 'ClientesBundle:Clientes',
                'property' => 'nomappraziva',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                            ->where('n.isActive = :is_active')
                            ->setParameter('is_active', 1);
                },
                'required' => true,
                'label' => 'Cliente (*)',
                'attr' => array('title' => 'Solo clientes activos.')
            ));
        else:
            $builder->add('idCliente', 'entity', array(
                'class' => 'ClientesBundle:Clientes',
                'property' => 'nomappraziva',
                'query_builder' => function(EntityRepository $er) use ($id_agencia) {
                    return $er->createQueryBuilder('n')
                            ->where('n.isActive = :is_active')
                            ->setParameter('is_active', 1)
                            ->andWhere('n.id_agencia = :id_agencia')
                            ->setParameter('id_agencia', $id_agencia);
                },
                'required' => true,
                'label' => 'Cliente (*)',
                'attr' => array('title' => 'Solo clientes activos y de la misma agencia actual.')
            ));
        endif;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Entity\CtasCtesClientes',
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
        return 'dsnempresas_ctasctesclientes_ctasctesclientesbundle_ctasctesclientes';
    }
}

<?php

namespace DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Form\FechasType;

class ClientesFilterType extends AbstractType {
    var $id_agencia;
    var $rol;
    var $em;
    var $cliente;
    
    public function __construct($id_agencia, $rol, $em, $cliente) {
        $this->id_agencia = $id_agencia;
        $this->rol = $rol;
        $this->em = $em;
        $this->cliente = $cliente;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_agencia = $this->id_agencia;
        $rol = $this->rol;
        $em = $this->em;
        $cliente = $this->cliente;
		
        $builder->add('fecha', new FechasType());
        
        if(in_array('ROLE_SUPER_ADMIN', $rol)):
            if(isset($cliente)):
                $builder->add('idCliente', 'entity', array(
                    'class' => 'ClientesBundle:Clientes',
                    'property' => 'comercio',
                    'empty_value' => 'Cliente',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                                ->andWhere('c.isActive = :is_active')
                                ->setParameter('is_active', 1)
                                ->orderBy('c.comercio');
                    },
                    'required' => true,
                    'label' => false,
                    'data' => $em->getReference('ClientesBundle:Clientes', $cliente),
                    'attr' => array('title' => 'Solo clientes activos.'),
                ));
            else:
                $builder->add('idCliente', 'entity', array(
                    'class' => 'ClientesBundle:Clientes',
                        'property' => 'comercio',
                        'empty_value' => 'Cliente',
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('c.isActive = :is_active')
                                    ->setParameter('is_active', 1)
                                    ->orderBy('c.comercio');
                        },
                        'required' => true,
                        'label' => false,
                        'attr' => array('title' => 'Solo clientes activos.'),
                    ));
            endif;
        else:
            if(isset($cliente)):
                $builder->add('idCliente', 'entity', array(
                'class' => 'ClientesBundle:Clientes',
                    'property' => 'comercio',
                    'empty_value' => 'Cliente',
                    'query_builder' => function(EntityRepository $er) use($id_agencia) {
                        return $er->createQueryBuilder('c')
                                ->where('c.id_agencia = :id_agencia')
                                ->setParameter('id_agencia' , $id_agencia)
                                ->andWhere('c.isActive = :is_active')
                                ->setParameter('is_active', 1)
                                ->orderBy('c.comercio');
                    },
                    'required' => true,
                    'label' => false,
                    'data' => $em->getReference('ClientesBundle:Clientes', $cliente),
                    'attr' => array('title' => 'Solo clientes activos y de la misma agencia actual.'),
                ));
            else:
                $builder->add('idCliente', 'entity', array(
                    'class' => 'ClientesBundle:Clientes',
                        'property' => 'comercio',
                        'empty_value' => 'Cliente',
                        'query_builder' => function(EntityRepository $er) use($id_agencia) {
                            return $er->createQueryBuilder('c')
                                    ->where('c.id_agencia = :id_agencia')
                                    ->setParameter('id_agencia' , $id_agencia)
                                    ->andWhere('c.isActive = :is_active')
                                    ->setParameter('is_active', 1)
                                ->orderBy('c.comercio');
                        },
                        'required' => true,
                        'label' => false,
                        'attr' => array('title' => 'Solo clientes activos y de la misma agencia actual.'),
                    ));
            endif;
        endif;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Entity\CtasCtesClientes',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            'csrf_field_name' => 'mediterraneofm_mediterraneofmbundle_clientesfiltertype_debe',
            // a unique key to help generate the secret token
            'intention' => 'task_item',
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_clientesfiltertype';
    }
}

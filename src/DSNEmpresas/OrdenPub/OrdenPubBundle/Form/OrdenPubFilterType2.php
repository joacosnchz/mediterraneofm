<?php

namespace DSNEmpresas\OrdenPub\OrdenPubBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DSNEmpresas\OrdenPub\OrdenPubBundle\Form\OrdenPubFilterType3;

class OrdenPubFilterType2 extends AbstractType {
    var $id_agencia;
    var $rol;
    
    public function __construct($id_agencia, $rol, $em, $default) {
        $this->id_agencia = $id_agencia;
        $this->rol = $rol;
        $this->em = $em;
        $this->default = $default;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_agencia = $this->id_agencia;
        $rol = $this->rol;
        
        $builder
            ->add('nro_ordenpub', 'text', array(
                'attr' => array(
                    'placeholder' => 'Nro. orden',
                    'size' => 8
                ),
                'label' => false,
                'required' => false,
            ))
            ->add('texto_publicidad', 'choice', array(
                'choices' => array('o.nro_ordenpub' => 'Por nro. orden', 'o.idCliente' => 'Por Cliente', 'o.fecha' => 'Por fecha'),
                'label' => false,
                'empty_value' => 'Ordenar por:',
                'empty_data' => 'null',
                'required' => false,
                'attr' => array('onchange' => "this.form.submit()"),
            ))
            ->add('observaciones', 'choice', array(
                'choices' => array('>=' => 'Vigentes', '<' => 'Vencidas'),
                'label' => false,
                'required' => false,
                'empty_value' => 'Vigencia',
                'empty_data' => 'null',
                'attr' => array('onchange' => "this.form.submit()"),
            ))
            ->add('liquidado', 'choice', array(
                'choices' => array('true' => 'Liquidada', 'false' => 'No liquidada'),
                'empty_value' => 'Estado liquidacion',
                'empty_data' => 'null',
                'required' => false,
                'label' => false,
                'attr' => array('onchange' => "this.form.submit()"),
            ))
            ;

            if(in_array('ROLE_SUPER_ADMIN', $rol)):
                if($this->default['id_cliente']):
                    $builder
                        ->add('id_cliente', 'entity', array(
                            'class' => 'ClientesBundle:Clientes',
                            'property' => 'nomappraziva',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('c')
                                        ->where('c.isActive = :is_active')
                                        ->setParameter('is_active', 1)
                                        ->orderBy('c.comercio');
                            },
                            'required' => false,
                            'empty_value' => 'Seleccione cliente',
                            'empty_data' => 'null',
                            'label' => false,
                            'data' => $this->em->getReference('ClientesBundle:Clientes', $this->default['id_cliente']),
                            'attr' => array('onchange' => "this.form.submit()"),
                        ));
                else:
                    $builder
                        ->add('id_cliente', 'entity', array(
                            'class' => 'ClientesBundle:Clientes',
                            'property' => 'nomappraziva',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('c')
                                        ->where('c.isActive = :is_active')
                                        ->setParameter('is_active', 1)
                                        ->orderBy('c.comercio');
                            },
                            'required' => false,
                            'empty_value' => 'Seleccione cliente',
                            'empty_data' => 'null',
                            'label' => false,
                            'attr' => array('onchange' => "this.form.submit()"),
                        ));
                endif;

                if($this->default['id_agencia']):
                    $builder->add('id_agencia', 'entity', array(
                            'class' => 'AgenciasBundle:Agencias',
                            'property' => 'razonSocial',
                            'required' => false,
                            'label' => false,
                            'empty_value' => 'Seleccione agencia',
                            'empty_data' => 'null',
                            'data' => $this->em->getReference('AgenciasBundle:Agencias', $this->default['id_agencia']),
                            'attr' => array('onchange' => "this.form.submit()"),
                    ));
                else:
                    $builder->add('id_agencia', 'entity', array(
                            'class' => 'AgenciasBundle:Agencias',
                            'property' => 'razonSocial',
                            'required' => false,
                            'label' => false,
                            'empty_value' => 'Seleccione agencia',
                            'empty_data' => 'null',
                            'attr' => array('onchange' => "this.form.submit()"),
                    ));
                endif;
            else:
                if($this->default['id_cliente']):
                    $builder
                        ->add('id_cliente', 'entity', array(
                            'class' => 'ClientesBundle:Clientes',
                            'property' => 'nomappraziva',
                            'data' => $this->em->getReference('ClientesBundle:Clientes', $this->default['id_cliente']),
                            'query_builder' => function(EntityRepository $er) use($id_agencia) {
                                return $er->createQueryBuilder('c')
                                        ->where('c.id_agencia = :id_agencia')
                                        ->setParameter('id_agencia' , $id_agencia)
                                        ->andWhere('c.isActive = :is_active')
                                        ->setParameter('is_active', 1)
                                        ->orderBy('c.comercio');
                            },
                            'required' => false,
                            'empty_value' => 'Seleccione cliente',
                            'empty_data' => 'null',
                            'label' => false,
                            'attr' => array('onchange' => "this.form.submit()", 'title' => 'Solo clientes activos y de la misma agencia actual.'),
                        ));
                else:
                    $builder
                        ->add('id_cliente', 'entity', array(
                            'class' => 'ClientesBundle:Clientes',
                            'property' => 'nomappraziva',
                            'query_builder' => function(EntityRepository $er) use($id_agencia) {
                                return $er->createQueryBuilder('c')
                                        ->where('c.id_agencia = :id_agencia')
                                        ->setParameter('id_agencia' , $id_agencia)
                                        ->andWhere('c.isActive = :is_active')
                                        ->setParameter('is_active', 1)
                                        ->orderBy('c.comercio');
                            },
                            'required' => false,
                            'empty_value' => 'Seleccione cliente',
                            'empty_data' => 'null',
                            'label' => false,
                            'attr' => array('onchange' => "this.form.submit()", 'title' => 'Solo clientes activos y de la misma agencia actual.'),
                        ));
                endif;
            endif;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenPub',
            /* 'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'task_item', */
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_ordenpubfiltertype2';
    }
}

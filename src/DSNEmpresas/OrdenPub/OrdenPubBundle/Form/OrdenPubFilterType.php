<?php

namespace DSNEmpresas\OrdenPub\OrdenPubBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DSNEmpresas\OrdenPub\OrdenPubBundle\Form\OrdenPubFilterType2;

class OrdenPubFilterType extends AbstractType {
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
            ->add('fecha_desde', 'date', array(
                'required' => false,
                'label' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => array(
                    'title' => 'Click sobre el campo para completarlo.',
                    'size' => 8,
                    'placeholder' => 'Fecha desde'
                ),
            ))
            ->add('fecha_hasta', 'date', array(
                'label' => false,
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => array(
                    'title' => 'Click sobre el campo para completarlo.',
                    'size' => 8,
                    'placeholder' => 'Fecha hasta'
                ),
            ))
            ->add('id_costotarifas', 'hidden', array(
                'required' => false,
                'label' => false,
            ))
            ->add('id_ordenpub', new OrdenPubFilterType2($id_agencia, $rol, $this->em, $this->default), array(
                'label' => false,
            ))
        ;
        
        if(in_array('ROLE_SUPER_ADMIN', $this->rol)):
            if($this->default['id_tarifa']):
                $builder
                    ->add('id_tarifa', 'entity', array(
                        'mapped' => false,
                        'class' => 'TarifasBundle:Tarifas',
                        'property' => 'nombre',
                        'required' => false,
                        'label' => false,
                        'attr' => array('class' => 'tarifas'),
                        'empty_value' => 'Seleccione tarifa',
                        'empty_data'=> 'null',
                        'data' => $this->em->getReference('TarifasBundle:Tarifas', $this->default['id_tarifa']),
                    ));
            else:
                $builder  
                    ->add('id_tarifa', 'entity', array(
                        'mapped' => false,
                        'class' => 'TarifasBundle:Tarifas',
                        'property' => 'nombre',
                        'required' => false,
                        'label' => false,
                        'attr' => array('class' => 'tarifas'),
                        'empty_value' => 'Seleccione tarifa',
                        'empty_data'=> 'null',
                    ));
            endif;
        else:
            if($this->default['id_tarifa']):
                $builder
                    ->add('id_tarifa', 'entity', array(
                        'mapped' => false,
                        'class' => 'TarifasBundle:Tarifas',
                        'property' => 'nombre',
                        'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('t')
                                        ->leftJoin('t.id_emisora', 'e')
                                        ->where('e.id_ciudad = :id_ciudad')
                                        ->setParameter('id_ciudad', $this->id_agencia->getIdCiudad())
                                        ->andWhere('t.fecha_desde < :fecha_hoy')
                                        ->andWhere('t.fecha_hasta > :fecha_hoy')
                                        ->setParameter('fecha_hoy', new \DateTime('now'))
                                    ;
                            },
                        'required' => false,
                        'label' => false,
                        'attr' => array('class' => 'tarifas'),
                        'empty_value' => 'Seleccione tarifa',
                        'empty_data'=> 'null',
                        'data' => $this->em->getReference('TarifasBundle:Tarifas', $this->default['id_tarifa']),
                    ));
            else:
                $builder  
                    ->add('id_tarifa', 'entity', array(
                        'mapped' => false,
                        'class' => 'TarifasBundle:Tarifas',
                        'property' => 'nombre',
                        'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('t')
                                        ->leftJoin('t.id_emisora', 'e')
                                        ->where('e.id_ciudad = :id_ciudad')
                                        ->setParameter('id_ciudad', $this->id_agencia->getIdCiudad())
                                        ->andWhere('t.fecha_desde < :fecha_hoy')
                                        ->andWhere('t.fecha_hasta > :fecha_hoy')
                                        ->setParameter('fecha_hoy', new \DateTime('now'))
                                    ;
                            },
                        'required' => false,
                        'label' => false,
                        'attr' => array('class' => 'tarifas'),
                        'empty_value' => 'Seleccione tarifa',
                        'empty_data'=> 'null',
                    ));
            endif;
        endif;        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenTarifas',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'task_item',
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_ordenpubfiltertype';
    }
}

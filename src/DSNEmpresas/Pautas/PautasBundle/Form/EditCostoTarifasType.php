<?php

namespace DSNEmpresas\Pautas\PautasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditCostoTarifasType extends AbstractType {
    var $tarifas;
    var $id_ciudad;
    var $rol;

    public function __construct($id_ciudad, $rol) {
        $this->id_ciudad = $id_ciudad;
        $this->rol = $rol;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_ciudad = $this->id_ciudad;
        $rol = $this->rol;

        if(in_array('ROLE_SUPER_ADMIN', $rol)):
            $builder->add('id_tarifa', 'entity', array(
                    'class' => 'TarifasBundle:Tarifas',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('t')
                            ->where('t.fecha_desde <= :fecha_desde')
                            ->setParameter('fecha_desde', new \DateTime('now'))
                            ->andWhere('t.fecha_hasta >= :fecha_hasta')
                            ->setParameter('fecha_hasta', new \DateTime('now'));
                    },
                    'required' => true,
                    'label' => 'Tarifa (*)',
                ));
        else:
            $builder->add('id_tarifa', 'entity', array(
                    'class' => 'TarifasBundle:Tarifas',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) use($id_ciudad) {
                        return $er->createQueryBuilder('t')
                            ->where('t.fecha_desde <= :fecha_desde')
                            ->setParameter('fecha_desde', new \DateTime('now'))
                            ->andWhere('t.fecha_hasta >= :fecha_hasta')
                            ->setParameter('fecha_hasta', new \DateTime('now'))
                            ->leftJoin('t.id_emisora', 'e')
                            ->andWhere('e.id_ciudad = :id_ciudad')
                            ->setParameter('id_ciudad', $id_ciudad);
                    },
                    'required' => true,
                    'label' => 'Tarifa (*)',
                ));
        endif;
        
        $builder
            ->add('periodo', 'entity', array(
                    'class' => 'MediterraneoFMBundle:Periodos',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'required' => true,
                    'label' => 'Periodo (*)',
                ))
            ->add('id_tipo_mencion', 'entity', array(
                    'class' => 'MediterraneoFMBundle:TiposMenciones',
                    'property' => 'nro_menciones',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'required' => true,
                    'label' => 'Tipo de mencion (*)',
                ))
            ->add('duracion', null, array(
                'label' => 'Duracion (en seg.) (*)',
                'required' => true,
            ))
            ->add('costo', null, array(
                'label' => 'Costo (*)',
                'required' => true,
                'attr' => array('onkeypress' => "return justNumbers(event);",),
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Pautas\PautasBundle\Entity\Pautas',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'task_item',
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_editcostotarifastype';
    }
}

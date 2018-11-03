<?php

namespace DSNEmpresas\Programas\ProgramasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InsertProgramasType extends AbstractType {
    var $id_ciudad;
    var $rol;
    
    public function __construct($id_ciudad, $rol) {
        $this->id_ciudad = $id_ciudad;
        $this->rol = $rol;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_ciudad = $this->id_ciudad;
        $rol = $this->rol;
        
        $builder->add('nombre', 'text', array(
                'label' => 'Nombre (*)',
                'required' => true,
            ));
        if(in_array('ROLE_SUPER_ADMIN', $rol)):
            $builder->add('id_programacion', 'entity', array(
                'class' => 'ProgramacionesBundle:Programaciones',
                'property' => 'nombre',
                'query_builder' => function(EntityRepository $er) use($id_ciudad) {
                    return $er->createQueryBuilder('p')
                            ->andWhere('p.is_active = :is_active')
                            ->setParameter('is_active', 1);
                },
                'label' => 'Programacion (*)',
                'attr' => array('title' => 'Programaciones activas.'),
                ));
        else:
            $builder->add('id_programacion', 'entity', array(
                'class' => 'ProgramacionesBundle:Programaciones',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) use($id_ciudad) {
                        return $er->createQueryBuilder('p')
                                ->leftJoin('p.id_emisora', 'e')
                                ->where('e.id_ciudad = :id_ciudad')
                                ->setParameter('id_ciudad', $id_ciudad)
                                ->andWhere('p.is_active = :is_active')
                                ->setParameter('is_active', 1);
                    },
                    'label' => 'Programacion (*)',
                    'attr' => array('title' => 'Programaciones activas y de emisoras de la misma ciudad que la agencia actual.'),
                ));
        endif;
        $builder->add('duracion_desde', 'time', array(
                'label' => 'Duracion desde (*)',
                'required' => true,
                'widget' => 'single_text',
                'attr' => array('size' => 4, 'title' => 'Click sobre el campo para completarlo.'),
            ))
            ->add('duracion_hasta', 'time', array(
                'label' => 'Duracion hasta (*)',
                'required' => true,
                'widget' => 'single_text',
                'attr' => array('size' => 4, 'title' => 'Click sobre el campo para completarlo.'),
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Programas\ProgramasBundle\Entity\Programas',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'task_item',
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_insertprogramastype';
    }
}

<?php

namespace DSNEmpresas\Grillas\GrillasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InsertGrillasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id_programa', 'entity', array(
                'class' => 'ProgramasBundle:Programas',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'label' => 'Programa (*)',
                ))
            ->add('id_tipos_menciones', 'entity', array(
                'class' => 'MediterraneoFMBundle:TiposMenciones',
                    'property' => 'nro_menciones',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'label' => 'Tipo de mencion (*)',
                ))
            ->add('nro_salidas', null, array(
                'label' => 'Nro. salidas (*)'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MediterraneoFM\MediterraneoFMBundle\Entity\Grillas',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'task_item',
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_insertgrillastype';
    }
}

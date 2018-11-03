<?php

namespace DSNEmpresas\Tarifas\TarifasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditTarifasType extends AbstractType {
    var $id_ciudad;
    var $rol;

    public function __construct($id_ciudad, $rol) {
        $this->id_ciudad = $id_ciudad;
        $this->rol = $rol;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_ciudad = $this->id_ciudad;
        $rol = $this->rol;

        $builder
            ->add('nombre', null, array(
                'required' => true,
                'label' => 'Nombre (*)',
            ))
            ->add('fecha_desde', 'date', array(
                'required' => true,
                'label' => 'Fecha desde (*)',
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => array('title' => 'Click sobre el campo para completarlo.', 'size' => 7),
            ))
            ->add('fecha_hasta', 'date', array(
                'required' => true,
                'label' => 'Fecha hasta (*)',
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => array('title' => 'Click sobre el campo para completarlo.', 'size' => 7),
            ));
        if(in_array('ROLE_SUPER_ADMIN', $rol)):
            $builder->add('id_emisora', 'entity', array(
                    'class' => 'EmisorasBundle:Emisoras',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'required' => true,
                    'label' => 'Emisora (*)',
                ));
        else:
            $builder->add('id_emisora', 'entity', array(
                    'class' => 'EmisorasBundle:Emisoras',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) use ($id_ciudad) {
                        return $er->createQueryBuilder('c')
                                ->where('c.id_ciudad = :id_ciudad')
                                ->setParameter('id_ciudad', $id_ciudad);
                    },
                    'required' => true,
                    'label' => 'Emisora (*)',
                ));
        endif;
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Tarifas\TarifasBundle\Entity\Tarifas',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'task_item',
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_edittarifastype';
    }
}

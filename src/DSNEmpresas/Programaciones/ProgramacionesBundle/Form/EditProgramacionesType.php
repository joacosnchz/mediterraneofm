<?php

namespace DSNEmpresas\Programaciones\ProgramacionesBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditProgramacionesType extends AbstractType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    var $id_ciudad_agencia;
    var $rol;
    
    public function __construct($id_ciudad_agencia, $rol) {
        $this->id_ciudad_agencia = $id_ciudad_agencia;
        $this->rol = $rol;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_ciudad_agencia = $this->id_ciudad_agencia;
        $rol = $this->rol;

        if(in_array('ROLE_SUPER_ADMIN', $rol)):
            $builder->add('id_emisora', 'entity', array(
                'class' => 'EmisorasBundle:Emisoras',
                    'property' => 'nombre',
                    'required' => true,
                    'label' => 'Emisora (*)',
                ));
        else:
            $builder->add('id_emisora', 'entity', array(
                'class' => 'EmisorasBundle:Emisoras',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) use ($id_ciudad_agencia) {
                        return $er->createQueryBuilder('c')
                                ->where('c.id_ciudad = :id_ciudad')
                                ->setParameter('id_ciudad', $id_ciudad_agencia);
                    },
                    'required' => true,
                    'label' => 'Emisora (*)',
                    'attr' => array('title' => 'Solo emisoras de la misma zona que agencia actual.')
                ));
        endif;
        $builder->add('nombre', null, array(
                'label' => 'Descripcion (*)',
            ))
            ->add('is_active', null, array(
                'label' => 'Activo',
                'required' => false,
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Programaciones\ProgramacionesBundle\Entity\Programaciones',
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
        return 'mediterraneofm_mediterraneofmbundle_editprogramacionestype';
    }
}

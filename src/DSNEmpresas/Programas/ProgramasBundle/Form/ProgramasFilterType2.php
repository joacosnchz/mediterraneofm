<?php

namespace DSNEmpresas\Programas\ProgramasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProgramasFilterType2 extends AbstractType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    var $rol;
    var $id_ciudad_agencia;

    public function __construct($id_ciudad_agencia, $rol, $em, $default) {
        $this->id_ciudad_agencia = $id_ciudad_agencia;
        $this->rol = $rol;
        $this->em = $em;
        $this->default = $default;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $rol = $this->rol;
        $id_ciudad_agencia = $this->id_ciudad_agencia;

        if(in_array('ROLE_SUPER_ADMIN', $rol)):
            if($this->default['id_emisora']):
                $builder->add('nombre', 'entity', array(
                        'class' => 'EmisorasBundle:Emisoras',
                        'property' => 'nombre',
                        'required' => false,
                        'empty_value' => 'Seleccione emisora',
                        'empty_data' => 'null',
                        'data' => $this->em->getReference('EmisorasBundle:Emisoras', $this->default['id_emisora']),
                        'label' => false,
                    ));
            else:
                $builder->add('nombre', 'entity', array(
                        'class' => 'EmisorasBundle:Emisoras',
                        'property' => 'nombre',
                        'required' => false,
                        'empty_value' => 'Seleccione emisora',
                        'empty_data' => 'null',
                        'label' => false,
                    ));
            endif;
        else:
            if($this->default['id_emisora']):
                $builder->add('nombre', 'entity', array(
                        'class' => 'EmisorasBundle:Emisoras',
                        'property' => 'nombre',
                        'query_builder' => function(EntityRepository $er) use($id_ciudad_agencia) {
                                return $er->createQueryBuilder('e')
                                        ->where('e.id_ciudad = :id_ciudad')
                                        ->setParameter('id_ciudad' , $id_ciudad_agencia);
                            },
                        'required' => false,
                        'empty_value' => 'Seleccione emisora',
                        'empty_data' => 'null',
                        'label' => false,
                        'data' => $this->em->getReference('EmisorasBundle:Emisoras', $this->default['id_emisora']),
                    ));
            else:
                $builder->add('nombre', 'entity', array(
                    'class' => 'EmisorasBundle:Emisoras',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) use($id_ciudad_agencia) {
                            return $er->createQueryBuilder('e')
                                    ->where('e.id_ciudad = :id_ciudad')
                                    ->setParameter('id_ciudad' , $id_ciudad_agencia);
                        },
                    'required' => false,
                    'empty_value' => 'Seleccione emisora',
                    'empty_data' => 'null',
                    'label' => false,
                ));
            endif;
        endif;
        
        $builder
                ->add('programacion', 'hidden', array(
                    'mapped' => false,
                    'label' => false,
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
            'data_class' => 'DSNEmpresas\Programas\ProgramasBundle\Entity\Programas',
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
        return 'mediterraneofm_mediterraneofmbundle_programasfiltertype2';
    }
}
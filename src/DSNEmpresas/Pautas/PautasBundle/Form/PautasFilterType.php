<?php

namespace DSNEmpresas\Pautas\PautasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PautasFilterType extends AbstractType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    var $rol;
    var $id_ciudad;
    var $em;
    var $defaults;

    public function __construct($rol, $id_ciudad, $em, $defaults) {
        $this->rol = $rol;
        $this->id_ciudad = $id_ciudad;
        $this->defaults = $defaults;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $rol = $this->rol;
        $id_ciudad = $this->id_ciudad;
        
        if(isset($this->defaults['descripcion'])):
            $builder
                ->add('descripcion', 'text', array(
                    'label' => false,
                    'required' => false,
                    'mapped' => false,
                    'attr' => array('placeholder' => 'Descripcion'),
                    'data' => $this->defaults['descripcion'],
                ))
            ;
        else:
            $builder
                ->add('descripcion', 'text', array(
                    'label' => false,
                    'required' => false,
                    'mapped' => false,
                    'attr' => array('placeholder' => 'Descripcion'),
                ))
            ;
        endif;

        if(in_array('ROLE_SUPER_ADMIN', $rol)):
            if(isset($this->defaults['id_tarifa_pautas'])):
                $builder->add('id_tarifa', 'entity', array(
                        'class' => 'TarifasBundle:Tarifas',
                        'property' => 'nombre',
                        'required' => false,
                        'label' => false,
                        'data' => $this->em->getReference('TarifasBundle:Tarifas', $this->defaults['id_tarifa_pautas']),
                        'empty_value' => 'Seleccione tarifa',
                        'empty_data' => 'null',
                        'attr' => array('onchange' => 'this.form.submit()'),  
                    ));
            else:
                $builder->add('id_tarifa', 'entity', array(
                        'class' => 'TarifasBundle:Tarifas',
                        'property' => 'nombre',
                        'required' => false,
                        'label' => false,
                        'empty_value' => 'Seleccione tarifa',
                        'empty_data' => 'null',
                        'attr' => array('onchange' => 'this.form.submit()'),  
                    ));
            endif;
        else:
            if(isset($this->defaults['id_tarifa_pautas'])):
                $builder->add('id_tarifa', 'entity', array(
                        'class' => 'TarifasBundle:Tarifas',
                        'property' => 'nombre',
                        'query_builder' => function(EntityRepository $er) use($id_ciudad) {
                            return $er->createQueryBuilder('t')
                                        ->leftJoin('t.id_emisora', 'e')
                                        ->where('e.id_ciudad = :id_ciudad')
                                        ->setParameter('id_ciudad', $id_ciudad);
                        },
                        'required' => false,
                        'label' => false,
                        'data' => $this->em->getReference('TarifasBundle:Tarifas', $this->defaults['id_tarifa_pautas']),
                        'empty_value' => 'Seleccione tarifa',
                        'empty_data' => 'null',
                        'attr' => array('onchange' => 'this.form.submit()'),  
                    ));
            else:
                $builder->add('id_tarifa', 'entity', array(
                        'class' => 'TarifasBundle:Tarifas',
                        'property' => 'nombre',
                        'query_builder' => function(EntityRepository $er) use($id_ciudad) {
                            return $er->createQueryBuilder('t')
                                        ->leftJoin('t.id_emisora', 'e')
                                        ->where('e.id_ciudad = :id_ciudad')
                                        ->setParameter('id_ciudad', $id_ciudad);
                        },
                        'required' => false,
                        'label' => false,
                        'empty_value' => 'Seleccione tarifa',
                        'empty_data' => 'null',
                        'attr' => array('onchange' => 'this.form.submit()'),  
                    ));
            endif;
        endif;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Pautas\PautasBundle\Entity\Pautas',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            'csrf_field_name' => 'mediterraneofm_mediterraneofmbundle_clientesfiltertype_debe',
            // a unique key to help generate the secret token
            'intention' => 'task_item',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_pautasfiltertype';
    }
}

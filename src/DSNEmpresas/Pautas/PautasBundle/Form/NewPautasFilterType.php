<?php

namespace DSNEmpresas\Pautas\PautasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NewPautasFilterType extends AbstractType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    var $id_ciudad;
    var $rol;
    var $em;
    var $defaults;

    public function __construct($id_ciudad, $rol, $em, $defaults) {
        $this->id_ciudad = $id_ciudad;
        $this->rol = $rol;
        $this->em = $em;
        $this->defaults = $defaults;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_ciudad = $this->id_ciudad;
        $rol = $this->rol;
        $em = $this->em;
        $defaults = $this->defaults;

        if(in_array('ROLE_ADMIN', $rol)):
            if($defaults):
                $builder->add('id_tarifa', 'entity', array(
                    'class' => 'EmisorasBundle:Emisoras',
                    'property' => 'nombre',
                    'label' => false,
                    'required' => false,
                    'empty_value' => 'Seleccione emisora',
                    'data' => $em->getReference('EmisorasBundle:Emisoras', $defaults['id_emisora']),
                ));
            else:
                $builder->add('id_tarifa', 'entity', array(
                    'class' => 'EmisorasBundle:Emisoras',
                    'property' => 'nombre',
                    'label' => false,
                    'required' => false,
                    'empty_value' => 'Seleccione emisora',
                ));
            endif;
        else:
            if($defaults):
                $builder->add('id_tarifa', 'entity', array(
                    'class' => 'EmisorasBundle:Emisoras',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) use($id_ciudad) {
                        return $er->createQueryBuilder('p')
                            ->where('p.id_ciudad = :id_ciudad')
                            ->setParameter('id_ciudad', $id_ciudad)
                        ;
                    },
                    'label' => false,
                    'required' => false,
                    'empty_value' => 'Seleccione emisora',
                    'attr' => array('id' => 'emisora'),
                    'data' => $em->getReference('EmisorasBundle:Emisoras', $defaults['id_emisora']),
                ));
            else:
                $builder->add('id_tarifa', 'entity', array(
                    'class' => 'EmisorasBundle:Emisoras',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) use($id_ciudad) {
                        return $er->createQueryBuilder('p')
                            ->where('p.id_ciudad = :id_ciudad')
                            ->setParameter('id_ciudad', $id_ciudad)
                        ;
                    },
                    'label' => false,
                    'required' => false,
                    'empty_value' => 'Seleccione emisora',
                    'attr' => array('id' => 'emisora'),
                ));
            endif;
        endif;
            /* ->add('id_tarifa', 'entity', array(
                'class' => 'TarifasBundle:Tarifas',
                'property' => 'nombre',
                'required' => false,
                'label' => false,
                'empty_value' => 'Seleccione tarifa',                
            )); */
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
        return 'mediterraneofm_mediterraneofmbundle_newpautasfiltertype';
    }
}

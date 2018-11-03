<?php

namespace DSNEmpresas\Pautas\PautasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InsertCostoTarifasType extends AbstractType {
    var $tarifas;
    var $rol;
    
    public function __construct($tarifas, $rol) {
        $this->tarifas = $tarifas; # Estas tarifas ya están filtradas en el controlador por ROL
        $this->rol = $rol;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $tarifas = $this->tarifas;
        
        // ACÁ LAS TARIFAS YA ESTAN FILTRADAS. ($tarifas)
        $builder->add('id_tarifa', 'choice', array(
                    'choices' => $tarifas,
                    'required' => true,
                    'label' => 'Tarifa (*)',
                    'attr' => array('title' => 'Solo tarifas vigentes.')
                ));
        
        $builder
            ->add('periodo', 'entity', array(
                    'class' => 'MediterraneoFM\MediterraneoFMBundle\Entity\Periodos',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'required' => true,
                    'label' => 'Periodo (*)',
                ))
            ->add('id_tipo_mencion', 'entity', array(
                    'class' => 'MediterraneoFM\MediterraneoFMBundle\Entity\TiposMenciones',
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
        return 'mediterraneofm_mediterraneofmbundle_insertcostotarifastype';
    }
}

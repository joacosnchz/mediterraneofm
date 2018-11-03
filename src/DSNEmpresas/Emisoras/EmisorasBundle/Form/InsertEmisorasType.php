<?php
namespace DSNEmpresas\Emisoras\EmisorasBundle\Form;


# Clase para hacer selects con entities
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InsertEmisorasType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $builder
            ->add('nombre', null, array(
                'label' => 'Nombre (*)',
                'required' => true,
            ))
            ->add($builder->create('direccion', null, array(
                'required' => false,
            )))
            ->add('telefono_comercial', null, array(
                'required' => true,
                'label' => 'Teléfono comercial (*)'
            ))   
            ->add('id_ciudad', 'entity', array(
                'class' => 'CiudadesBundle:Ciudades',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'required' => true,
                    'label' => 'Zona (*)',
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Emisoras\EmisorasBundle\Entity\Emisoras',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'task_item',
        ));
    }

    public function getName() {
        return 'mediterraneofm_mediterraneofmbundle_insertemisorastype';
    }
}

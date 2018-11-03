<?php

namespace DSNEmpresas\OrdenPub\OrdenPubBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DSNEmpresas\OrdenPub\OrdenPubBundle\Form\OrdenTarifasType;

class InsertOrdenPubType extends AbstractType {
    var $id_agencia;
    var $rol;
    var $fecha;
    var $confirmation;
    var $renovar;
    
    public function __construct($id_agencia, $rol, $fecha = 'd-m-Y', $confirmation = false, $renovar = false) {
        $this->id_agencia = $id_agencia;
        $this->rol = $rol;
        $this->fecha = new \DateTime(date($fecha));
        $this->confirmation = $confirmation;
        $this->renovar = $renovar;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_agencia = $this->id_agencia;
        $rol = $this->rol;
        
        $builder->add('fecha', null, array(
            'required' => true,
            'label' => 'Fecha (*)',
            'format' => 'dd-MM-yyyy',
            'widget' => 'single_text',
            'attr' => array('title' => 'Click sobre el campo para completarlo.', 'size' => 7, 'placeholder' => 'Fecha'),
            'data' => $this->fecha,
        ));

        
        if(in_array('ROLE_SUPER_ADMIN', $rol)):
            $builder->add('idCliente', 'entity', array(
                'class' => 'ClientesBundle:Clientes',
                'property' => 'nomappraziva',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                            ->where('n.isActive = :is_active')
                            ->setParameter('is_active', 1)
                            ->orderBy('n.comercio');
                },
                'required' => true,
                'label' => 'Cliente (*)',
                'attr' => array('title' => 'Solo clientes activos.')
            ));
        else:
            $builder->add('idCliente', 'entity', array(
                'class' => 'ClientesBundle:Clientes',
                'property' => 'nomappraziva',
                'query_builder' => function(EntityRepository $er) use ($id_agencia) {
                    return $er->createQueryBuilder('n')
                            ->where('n.isActive = :is_active')
                            ->setParameter('is_active', 1)
                            ->andWhere('n.id_agencia = :id_agencia')
                            ->setParameter('id_agencia', $id_agencia)
                            ->orderBy('n.comercio');;
                },
                'required' => true,
                'label' => 'Cliente (*)',
                'attr' => array('title' => 'Solo clientes activos y de la misma agencia actual.')
            ));
        endif;
        
        $builder->add('texto_publicidad', 'textarea', array(
                    'label' => 'Texto publicidad',
                    'required' => false,
                    "attr" => array(
                        "cols" => 50, 
                        "rows" => 8, 
                        "placeholder" => 'Ingrese aquí el texto que dirá el locutor.',
                        'onkeypress' => 'return limita(event, 400);',
                        'onkeyup' => 'actualizaInfo(400)',
                    )
                ))
            ->add('observaciones', 'textarea', array(
                'required' => false,
                'attr' => array("cols" => 50, "rows" => 8, "placeholder" => "Ingrese aquí alguna observación a ser tenida en cuenta.")
            ))
            ->add('id_tarifa', 'collection', array(
                'type' => new OrdenTarifasType(),
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('confirmation', 'hidden', array(
                'mapped' => false,
                'label' => false,
                'data' => $this->confirmation,
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenPub',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'task_item',
        ));
    }

    public function getName() {
        return 'mediterraneofm_mediterraneofmbundle_insertordenpubtype';
    }

}

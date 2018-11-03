<?php

namespace DSNEmpresas\OrdenPub\OrdenPubBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DSNEmpresas\OrdenPub\OrdenPubBundle\Form\OrdenTarifasType;

class RenovarOrdenPubType extends AbstractType {
    var $id_cliente;
    var $texto_publicidad;
    var $observaciones;
    var $id_agencia;
    var $rol;
    var $ordenvieja;
    
    public function __construct($ordenvieja, $id_agencia, $rol) {
        $this->ordenvieja = $ordenvieja;
        $this->id_agencia = $id_agencia;
        $this->rol = $rol;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_cliente = $this->id_cliente;
        $texto_publicidad = $this->texto_publicidad;
        $observaciones = $this->observaciones;
        $id_agencia = $this->id_agencia;
        $rol = $this->rol;
        
        $builder->add('fecha', null, array(
            'required' => true,
            'label' => 'Fecha (*)',
            'format' => 'dd-MM-yyyy',
            'widget' => 'single_text',
            'attr' => array(
                'title' => 'Click sobre el campo para completarlo.',
                'size' => 6,
                'placeholder' => 'Fecha',
                'value' => $this->ordenvieja->getFecha()->format('d-m-Y')
            ),
        ));
        if(in_array('ROLE_SUPER_ADMIN', $rol)):
            $builder->add('id_cliente', 'entity', array(
                'class' => 'ClientesBundle:Clientes',
                'property' => 'nomappraziva',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('n')
                            ->where('n.isActive = :is_active')
                            ->setParameter('is_active', 1);
                },
                'required' => true,
                'label' => 'Cliente (*)',
                'preferred_choices' => array($this->ordenvieja->getIdCliente()),
                'attr' => array('title' => 'Solo clientes activos.')
            ));
        else:
            $builder->add('id_cliente', 'entity', array(
                'class' => 'ClientesBundle:Clientes',
                'property' => 'nomappraziva',
                'query_builder' => function(EntityRepository $er) use ($id_agencia) {
                    return $er->createQueryBuilder('n')
                            ->where('n.isActive = :is_active')
                            ->setParameter('is_active', 1)
                            ->andWhere('n.id_agencia = :id_agencia')
                            ->setParameter('id_agencia', $id_agencia);
                },
                'required' => true,
                'label' => 'Cliente (*)',
                'preferred_choices' => array($this->ordenvieja->getIdCliente()),
                'attr' => array('title' => 'Solo clientes activos y de la misma agencia actual.'),
            ));
        endif;

        $builder
            ->add('texto_publicidad', 'textarea', array(
                    'label' => 'Texto publicidad (*)',
                    'required' => false,
                    'data' => $this->ordenvieja->getTextoPublicidad(),
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
                    'data' => $this->ordenvieja->getObservaciones(),
                    'attr' => array("cols" => 50, 
                        "rows" => 8, 
                        "placeholder" => "Ingrese aquí alguna observación a ser tenida en cuenta por el locutor.",
                        )
            ))
            ->add('id_tarifa', 'collection', array(
                'type' => new OrdenTarifasType(),
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('renovar', 'hidden', array(
                'mapped' => false,
                'label' => false,
                'data' => 1,
            ))
            ->add('id_ordenvieja', 'hidden', array(
                'mapped' => false,
                'label' => false,
                'data' => $this->ordenvieja->getIdOrdenPub(),
            ))
            ->add('confirmation', 'hidden', array(
                'mapped' => false,
                'label' => false,
                'data' => 0,
            ))
            ;        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenPub',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'task_item',
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_renovarordenpubtype';
    }
}

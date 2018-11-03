<?php

namespace DSNEmpresas\Pautas\PautasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ShowPautasType extends AbstractType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    var $id_agencia;

    public function __construct($id_agencia) {
        $this->id_agencia = $id_agencia;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_agencia = $this->id_agencia;
        $id_ciudad = $id_agencia->getIdCiudad()->getId();

        $builder
            /* ->add('duracion')
            ->add('periodo')
            ->add('costo')
            ->add('id_tipo_mencion') */
            ->add('id_tarifa', 'entity', array(
                'class' => 'EmisorasBundle:Emisoras',
                'property' => 'nombre',
                'query_builder' => function(EntityRepository $er) use($id_ciudad) {
                    return $er->createQueryBuilder('e')
                            ->where('e.id_ciudad = :id_ciudad')
                            ->setParameter('id_ciudad', $id_ciudad);
                },
                'required' => true,
                'label' => false,
            ))
            ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Pautas\PautasBundle\Entity\Pautas',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_showpautastype';
    }
}

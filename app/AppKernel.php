<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new MediterraneoFM\MediterraneoFMBundle\MediterraneoFMBundle(),
            new DSNEmpresas\Template\TemplateBundle\TemplateBundle(),
            new DSNEmpresas\TiposDocumentos\TiposDocumentosBundle\TiposDocumentosBundle(),
            new DSNEmpresas\OrdenPub\OrdenPubBundle\OrdenPubBundle(),
            new DSNEmpresas\Emisoras\EmisorasBundle\EmisorasBundle(),
            new DSNEmpresas\Tarifas\TarifasBundle\TarifasBundle(),
            new DSNEmpresas\Responsables\ResponsablesBundle\ResponsablesBundle(),
            new DSNEmpresas\Clientes\ClientesBundle\ClientesBundle(),
            new DSNEmpresas\Pautas\PautasBundle\PautasBundle(),
            new DSNEmpresas\Agencias\AgenciasBundle\AgenciasBundle(),
            new DSNEmpresas\Programas\ProgramasBundle\ProgramasBundle(),
            new DSNEmpresas\Recibos\RecibosBundle\RecibosBundle(),
            new DSNEmpresas\Facturas\FacturasBundle\FacturasBundle(),
            new DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\CtasCtesClientesBundle(),
            new DSNEmpresas\Listados\ListadosBundle\ListadosBundle(),
            new DSNEmpresas\Grillas\GrillasBundle\GrillasBundle(),
            new DSNEmpresas\Programaciones\ProgramacionesBundle\ProgramacionesBundle(),
            new DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\CtasCtesAgenciasBundle(),
            new DSNEmpresas\TiposMenciones\TiposMencionesBundle\TiposMencionesBundle(),
            new DSNEmpresas\Comisiones\ComisionesBundle\ComisionesBundle(),
            new DSNEmpresas\ModsGenerator\ModsGeneratorBundle\ModsGeneratorBundle(),
            new DSNEmpresas\Validation\ValidationBundle\ValidationBundle(),
            new DSNEmpresas\Ciudades\CiudadesBundle\CiudadesBundle(),
            new DSNEmpresas\System\SystemLogBundle\SystemLogBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new MakerLabs\PagerBundle\MakerLabsPagerBundle(),
            new RaulFraile\Bundle\LadybugBundle\RaulFraileLadybugBundle(), // quitar en modo produccion
            new Hackzilla\BarcodeBundle\HackzillaBarcodeBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}

<?php

namespace MediterraneoFM\MediterraneoFMBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CodigoBarras
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class CodigoBarras
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=50)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="codificacion", type="string", length=100)
     */
    private $codificacion;

    /**
     * @var integer
     *
     * @ORM\Column(name="escala", type="integer")
     */
    private $escala;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return CodigoBarras
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set codificacion
     *
     * @param string $codificacion
     * @return CodigoBarras
     */
    public function setCodificacion($codificacion)
    {
        $this->codificacion = $codificacion;

        return $this;
    }

    /**
     * Get codificacion
     *
     * @return string 
     */
    public function getCodificacion()
    {
        return $this->codificacion;
    }

    /**
     * Set escala
     *
     * @param integer $escala
     * @return CodigoBarras
     */
    public function setEscala($escala)
    {
        $this->escala = $escala;

        return $this;
    }

    /**
     * Get escala
     *
     * @return integer 
     */
    public function getEscala()
    {
        return $this->escala;
    }
}

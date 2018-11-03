<?php

namespace DSNEmpresas\Template\TemplateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SearchEntityFieldType
 */
class SearchEntityFieldType
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $result;

    /**
     * @var string
     */
    private $search;

    /**
     * @var string
     */
    private $property;


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
     * Set result
     *
     * @param string $result
     * @return SearchEntityFieldType
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result
     *
     * @return string 
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set property
     *
     * @param string $property
     * @return SearchEntityFieldType
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Get property
     *
     * @return string 
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set search
     *
     * @param string $search
     * @return SearchEntityFieldType
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Get search
     *
     * @return string 
     */
    public function getSearch()
    {
        return $this->search;
    }
}

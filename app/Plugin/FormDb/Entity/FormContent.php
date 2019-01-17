<?php

namespace Plugin\FormDb\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FormContent
 */
class FormContent extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $urlpath;

    /**
     * @var string
     */
    private $form_name;

    /**
     * @var string
     */
    private $form_value;


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
     * Set urlpath
     *
     * @param string $urlpath
     * @return FormContent
     */
    public function setUrlpath($urlpath)
    {
        $this->urlpath = $urlpath;

        return $this;
    }

    /**
     * Get urlpath
     *
     * @return string 
     */
    public function getUrlpath()
    {
        return $this->urlpath;
    }

    /**
     * Set form_name
     *
     * @param string $formName
     * @return FormContent
     */
    public function setFormName($formName)
    {
        $this->form_name = $formName;

        return $this;
    }

    /**
     * Get form_name
     *
     * @return string 
     */
    public function getFormName()
    {
        return $this->form_name;
    }

    /**
     * Set form_value
     *
     * @param string $formValue
     * @return FormContent
     */
    public function setFormValue($formValue)
    {
        $this->form_value = $formValue;

        return $this;
    }

    /**
     * Get form_value
     *
     * @return string 
     */
    public function getFormValue()
    {
        return $this->form_value;
    }
}

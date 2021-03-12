<?php

namespace Xtwoend\Model; 

trait HasTablePrefix
{
    protected $prefix;

    /**
     * Get the prefix associated with the model.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix ?? '';
    }

    /**
     * Set the Prefix associated with the model.
     *
     * @param  string  $Prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->getPrefix() . parent::getTable();
    }

    
}

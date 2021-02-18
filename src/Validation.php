<?php

namespace Xtwoend\Model;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;


trait Validation 
{
    protected $rules = [];
    protected $messages = [];
    protected $errors;

    /**
     * Validates current attributes against rules
     */
    public function validate()
    {   
        $validator = container()->get(ValidatorFactoryInterface::class);
        $v = $validator->make($this->attributes, $this->getRules(), $this->getMessages());

        if ($v->passes())
        {
            return true;
        }
        $this->setErrors($v->messages());

        return false;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
    
    /**
     * Set error message bag
     * 
     * @var \Hyperf\Utils\MessageBag
     */
    protected function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * Retrieve error message bag
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Inverse of wasSaved
     */
    public function hasErrors()
    {
        return ! empty($this->errors);
    }
}
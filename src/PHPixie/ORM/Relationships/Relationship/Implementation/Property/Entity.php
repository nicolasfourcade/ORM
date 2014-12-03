<?php

namespace PHPixie\ORM\Relationships\Relationship\Implementation\Property;

abstract class Entity extends \PHPixie\ORM\Relationships\Relationship\Implementation\Property
                              implements \PHPixie\ORM\Relationships\Relationship\Property\Entity
{
    protected $model;
    protected $value;
    protected $isLoaded = false;

    public function __construct($handler, $side, $model)
    {
        parent::__construct($handler, $side);
        $this->model = $model;
    }

    public function __invoke()
    {
        return $this->value();
    }

    public function reload()
    {
        $this->isLoaded = true;
        $this->value = $this->load();
        return $this->value;
    }

    public function reset()
    {
        $this->value = null;
        $this->isLoaded = false;
    }

    public function entity()
    {
        return $this->model;
    }

    public function value()
    {
        if (!$this->isLoaded)
            $this->reload();

        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        $this->isLoaded = true;
    }

    public function isLoaded()
    {
        return $this->isLoaded;
    }

    abstract protected function load();
}
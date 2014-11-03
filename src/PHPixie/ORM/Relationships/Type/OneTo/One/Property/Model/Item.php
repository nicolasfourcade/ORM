<?php

namespace PHPixie\ORM\Relationships\Type\OneTo\Type\One\Property\Model;

class Item extends \PHPixie\ORM\Relationships\Type\OneTo\Type\One\Property\Model
{

    public function load()
    {
        $item = parent::load();
        $this->handler->setItemOwner($this->config, $item, $this->model);
    }

    public function set($item)
    {
        $this->processSet($this->model, $item);
    }
}

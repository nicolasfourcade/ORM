<?php

namespace PHPixie\ORM\Relationships\Type\Embeds;

abstract class Handler extends \PHPixie\ORM\Relationships\Relationship\Implementation\Handler\Embedded/*
                       implements \PHPixie\ORM\Relationships\Relationship\Handler\Preloading,
                                  \PHPixie\ORM\Relationships\Relationship\Handler\Mapping\Database,
                                  \PHPixie\ORM\Relationships\Relationship\Handler\Mapping\Embedded*/
{
    public function mapPreload($side, $property, $result, $plan)
    {
        $config = $side->config();
        $preloadResult = $this->relationship->preloadResult($result, $config->path);
        
        $preloader = $this->relationship->preloader();
        
        $this->mappers->preload()->map(
            $preloader,
            $config->itemModel,
            $property->preload(),
            $preloadResult,
            $plan
        );
        
        return $preloader;
    }

    public function mapDatabaseQuery($query, $side, $group, $plan)
    {
        $this->mapConditionBuilder($query, $side, $group, $plan);
    }
    
    public function mapEmbeddedContainer($container, $side, $group, $plan)
    {
        $this->mapConditionBuilder($container, $side, $group, $plan);
    }
    
    protected function removeItemFromOwner($item)
    {
        $owner = $item->owner();
        if ($owner !== null) {
            $propertyName = $item->ownerPropertyName();
            $property = $owner->getRelationshipProperty($propertyName);
            if ($property instanceof \PHPixie\ORM\Relationships\Type\Embeds\Type\One\Property\Item) {
                $property->remove();
            } else {
                $property->remove($item);
            }
        }
    }
    
    abstract protected function mapConditionBuilder($builder, $side, $group, $plan);
}

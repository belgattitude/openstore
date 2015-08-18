<?php

namespace Openstore\Stdlib\Hydrator\Strategy;

use DoctrineModule\Stdlib\Hydrator\Strategy\AllowRemoveByValue;
use Doctrine\Common\Collections\Collection;

//class MultiSelectStrategy extends AllowRemoveByValue

class NestedExtractor extends AllowRemoveByValue
{
    public function extract($value)
    {
        if ($value instanceof Collection) {
            $return = array();
            foreach ($value as $entity) {
                $return[] = $entity->getId();
            }
            return $return;
        }
        if (is_object($value)) {
            return $value->getId();
        }
        
        return $value;
    }
}

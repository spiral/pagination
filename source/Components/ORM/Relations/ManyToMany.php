<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Relations;

use Spiral\Components\ORM\ActiveRecord;
use Spiral\Components\ORM\Relation;
use Spiral\Components\ORM\Selector;

class ManyToMany extends Relation
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::MANY_TO_MANY;

    /**
     * Indication that relation represent multiple records.
     */
    const MULTIPLE = true;

    /**
     * Internal ORM relation method used to create valid selector used to pre-load relation data or
     * create custom query based on relation options.
     *
     * @return Selector
     */
    protected function createSelector()
    {
        //For Many-to-Many relation we have to use custom loader to parse data, this is ONLY for
        //this type of relation
        $loader = new Selector\Loaders\ManyToManyLoader($this->orm, '', $this->definition);

        return $loader->createSelector($this->parent->getRoleName())->where(
            $loader->getPivotAlias() . '.' . $this->definition[ActiveRecord::THOUGHT_INNER_KEY],
            $this->parent->getField($this->definition[ActiveRecord::INNER_KEY])
        );
    }

    /**
     * Mount relation keys to parent or children models to ensure their connection. Method called
     * when model requests relation save.
     *
     * @param ActiveRecord $model
     * @return ActiveRecord
     */
    protected function mountRelation(ActiveRecord $model)
    {
        //Nothing to do, every fetched model should be already linked
        return $model;
    }
}
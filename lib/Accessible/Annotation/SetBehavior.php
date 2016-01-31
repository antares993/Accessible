<?php

namespace Accessible\Annotation;

/**
 * A property having this annotation will behave as a set.
 *
 * @Annotation
 * @Target("PROPERTY")
 * @Attributes({
 *   @Attribute("itemName", type="string"),
 *   @Attribute("methods", type="array<string>")
 * })
 */
class SetBehavior extends AbstractCollection
{
    /**
     * Initializes the annotation.
     *
     * @param mixed $values The annotation's parameters.
     */
    public function __construct($values)
    {
        $this->defaultMethods = array(AbstractCollection::ADD, AbstractCollection::REMOVE);
        parent::__construct($values);
    }
}
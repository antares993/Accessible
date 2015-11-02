<?php

namespace Accessible\Tests\TestsCases;

use Accessible\AccessibleTrait;
use Accessible\Annotations\Access;

class BasicTestCase
{
    use AccessibleTrait;

    /**
     * @Access({Access::GET, Access::SET})
     */
    private $foo = "foo";

    /**
     * @Access({Access::IS})
     */
    private $bar = "bar";

    /**
     * @Access({Access::HAS})
     */
    private $baz = "baz";

    private $notAccessibleProperty;
}

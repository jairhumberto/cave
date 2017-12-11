<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 * 
 * @copyright Copyright (c) 2018 Squille
 * @license   this software is distributed under MIT license, see the
 *            LICENSE file.
 */

namespace Squille\Cave;

class Unconformance
{
    protected $corrections;

    public function __construct(SQLList $corrections)
    {
        $this->corrections = $corrections;
    }

    public function fix(mysqli $connection)
    {
        foreach($this->corrections->getItens() as $correction) {
            $connection->query($correction->getSQL());
        }
    }
}

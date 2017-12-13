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
    protected $description;

    public function __construct(SQLList $corrections, $description)
    {
        $this->corrections = $corrections;
        $this->description = $description;
    }

    public function fix(\PDO $connection)
    {
        foreach($this->corrections->getItens() as $correction) {
            $connection->query($correction->getSQL());
        }
    }
    
    public function getDescription()
    {
        return $this->description;
    }
}

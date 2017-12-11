<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 * 
 * @copyright Copyright (c) 2018 Squille
 * @license   this software is distributed under MIT license, see the
 *            LICENSE file.
 */

namespace Squille\Cave;

class ReferenceList extends IndexList
{
    protected $table;
    
    public function getTable()
    {
        return $this->table;
    }
    
    public function setTable($value)
    {
        $this->table = $value;
    }
}

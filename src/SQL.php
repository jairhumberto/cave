<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 * 
 * @copyright Copyright (c) 2018 Squille
 * @license   this software is distributed under MIT license, see the
 *            LICENSE file.
 */

namespace Squille\Cave;

class SQL
{
    protected $sql;
    
    public function __construct($sql)
    {
        $this->sql = $sql;
    }
    
    public function getSQL()
    {
        return $this->sql;
    }
}

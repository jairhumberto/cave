<?php

namespace Squille\Cave\Xml;

use DOMElement;
use Squille\Cave\Models\ITableModel;

class XmlTable implements ITableModel
{
    public function __construct(DOMElement $element)
    {

    }

    public function getName()
    {
        // TODO: Implement getName() method.
    }

    public function getEngine()
    {
        // TODO: Implement getEngine() method.
    }

    public function getRowFormat()
    {
        // TODO: Implement getRowFormat() method.
    }

    public function getCollation()
    {
        // TODO: Implement getCollation() method.
    }

    public function getChecksum()
    {
        // TODO: Implement getChecksum() method.
    }

    public function getFields()
    {
        // TODO: Implement getFields() method.
    }

    public function getConstraints()
    {
        // TODO: Implement getConstraints() method.
    }

    public function getIndexes()
    {
        // TODO: Implement getIndexes() method.
    }

    public function checkIntegrity(ITableModel $tableModel)
    {
        // TODO: Implement checkIntegrity() method.
    }
}

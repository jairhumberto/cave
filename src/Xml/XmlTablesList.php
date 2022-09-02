<?php

namespace Squille\Cave\Xml;

use DOMElement;
use DOMNode;
use Squille\Cave\ArrayList;
use Squille\Cave\Models\ITablesListModel;

class XmlTablesList extends ArrayList implements ITablesListModel
{
    private $root;

    public function __construct(DOMElement $parent)
    {
        $this->root = $this->createRootElement($parent);
        parent::__construct($this->root->childNodes);
    }

    /**
     * @param DOMElement $parent
     * @return DOMNode
     */
    private function createRootElement(DOMElement $parent)
    {
        $tables = $this->getRootElement($parent);
        if ($tables == null) {
            $tables = $parent->ownerDocument->createElement("tables");
            $parent->appendChild($tables);
        }
        return $tables;
    }

    /**
     * @param DOMElement $parent
     * @return DOMNode|null
     */
    private function getRootElement(DOMElement $parent)
    {
        foreach ($parent->childNodes as $childNode) {
            if ($childNode->nodeName == "tables") {
                return $childNode;
            }
        }
        return null;
    }

    public function checkIntegrity(ITablesListModel $tablesListModel)
    {
        // TODO: Implement checkIntegrity() method.
    }
}

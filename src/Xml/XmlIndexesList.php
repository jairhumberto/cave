<?php

namespace Squille\Cave\Xml;

use DOMElement;
use DOMNode;
use PDO;
use PDOStatement;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractIndexesListModel;
use Squille\Cave\Models\AbstractIndexModel;
use Squille\Cave\Models\IndexModelInterface;
use Squille\Cave\Unconformity;

class XmlIndexesList extends AbstractIndexesListModel
{
    private $root;
    private $table;

    public function __construct(DOMElement $parent, XmlTable $table)
    {
        $this->root = $this->createRootElement($parent);
        $this->table = $table;
        parent::__construct($this->retrieveIndexes());
    }

    /**
     * @param DOMElement $parent
     * @return DOMNode
     */
    private function createRootElement(DOMElement $parent)
    {
        $indexes = $this->getRootElement($parent);
        if ($indexes == null) {
            $indexes = $parent->ownerDocument->createElement("indexes");
            $parent->appendChild($indexes);
        }
        return $indexes;
    }

    /**
     * @param DOMElement $parent
     * @return DOMNode|null
     */
    private function getRootElement(DOMElement $parent)
    {
        foreach ($parent->childNodes as $childNode) {
            if ($childNode->nodeName == "indexes") {
                return $childNode;
            }
        }
        return null;
    }

    private function retrieveIndexes()
    {
        try {
            $selectExpressions = MySqlPartialIndex::selectExpressions() ?: "*";
            $stm = $this->pdo->query("
                SELECT $selectExpressions
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE
                    TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME='{$this->getTable()}'
                    AND NOT EXISTS(
                        SELECT 1
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE
                            TABLE_SCHEMA = DATABASE()
                            AND TABLE_NAME = '{$this->getTable()}'
                            AND CONSTRAINT_NAME = INDEX_NAME
                        LIMIT 1
                    )
            ");
            return $this->groupIndexes($stm->fetchAll(PDO::FETCH_CLASS, MySqlPartialIndex::class, [$this->pdo, $this->table]) ?: []);
        } finally {
            if (isset($stm) && $stm instanceof PDOStatement) {
                $stm->closeCursor();
            }
        }
    }

    public function getTable()
    {
        return $this->table;
    }

    private function groupIndexes(array $partialIndexes)
    {
        $keys = [];
        $groups = $this->groupPartialIndexes($partialIndexes);
        foreach ($groups as $group) {
            $keys[] = MySqlIndexFactory::createInstance($this->pdo, $group);
        }
        return $keys;
    }

    private function groupPartialIndexes(array $partialIndexes)
    {
        $groups = [];
        foreach ($partialIndexes as $part) {
            if (!array_key_exists($part->getName(), $groups)) {
                $groups[$part->getName()] = [];
            }
            $groups[$part->getName()][] = $part;
        }
        return $groups;
    }

    protected function missingIndexUnconformity(IndexModelInterface $indexModel)
    {
        $description = "alter table {$this->getTable()} add {$indexModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($indexModel) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} ADD $indexModel");
        });
        return new Unconformity($description, $instructions);
    }

    protected function exceedingIndexUnconformity(AbstractIndexModel $mySqlIndex)
    {
        $description = "alter table {$this->getTable()} drop index {$mySqlIndex->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($mySqlIndex) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} DROP INDEX {$mySqlIndex->getName()}");
        });
        return new Unconformity($description, $instructions);
    }
}

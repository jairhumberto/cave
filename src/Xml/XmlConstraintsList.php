<?php

namespace Squille\Cave\Xml;

use DOMElement;
use DOMNode;
use PDO;
use PDOStatement;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractConstraintModel;
use Squille\Cave\Models\AbstractConstraintsListModel;
use Squille\Cave\Models\ConstraintModelInterface;
use Squille\Cave\Unconformity;

class XmlConstraintsList extends AbstractConstraintsListModel
{
    private $root;
    private $table;

    public function __construct(DOMElement $parent, XmlTable $table)
    {
        $this->root = $this->createRootElement($parent);
        $this->table = $table;
        parent::__construct($this->retrieveConstraints());
    }

    /**
     * @param DOMElement $parent
     * @return DOMNode
     */
    private function createRootElement(DOMElement $parent)
    {
        $constraints = $this->getRootElement($parent);
        if ($constraints == null) {
            $constraints = $parent->ownerDocument->createElement("constraints");
            $parent->appendChild($constraints);
        }
        return $constraints;
    }

    /**
     * @param DOMElement $parent
     * @return DOMNode|null
     */
    private function getRootElement(DOMElement $parent)
    {
        foreach ($parent->childNodes as $childNode) {
            if ($childNode->nodeName == "constraints") {
                return $childNode;
            }
        }
        return null;
    }

    private function retrieveConstraints()
    {
        try {
            $selectExpressions = MySqlPartialConstraint::selectExpressions() ?: "*";
            $stm = $this->pdo->query("
                SELECT $selectExpressions
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE
                    TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME='{$this->getTable()}'
                    AND EXISTS(
                        SELECT 1
                        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE
                            TABLE_SCHEMA = DATABASE()
                            AND TABLE_NAME = '{$this->getTable()}'
                            AND CONSTRAINT_NAME = INDEX_NAME
                        LIMIT 1
                    )
            ");
            return $this->groupConstraints($stm->fetchAll(PDO::FETCH_CLASS, MySqlPartialConstraint::class, [$this->pdo, $this->table]) ?: []);
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

    private function groupConstraints(array $partialConstraints)
    {
        $keys = [];
        $groups = $this->groupPartialConstraints($partialConstraints);
        foreach ($groups as $group) {
            $keys[] = MySqlConstraintFactory::createInstance($this->pdo, $group);
        }
        return $keys;
    }

    private function groupPartialConstraints(array $partialConstraints)
    {
        $groups = [];
        foreach ($partialConstraints as $part) {
            if (!array_key_exists($part->getName(), $groups)) {
                $groups[$part->getName()] = [];
            }
            $groups[$part->getName()][] = $part;
        }
        return $groups;
    }

    protected function missingConstraintUnconformity(ConstraintModelInterface $constraintModel)
    {
        $description = "alter table {$this->getTable()} add {$constraintModel->getName()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($constraintModel) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} ADD $constraintModel");
        });
        return new Unconformity($description, $instructions);
    }

    protected function exceedingConstraintUnconformity(AbstractConstraintModel $mySqlConstraint)
    {
        $description = "alter table {$this->getTable()} {$mySqlConstraint->dropCommand()}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($mySqlConstraint) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} {$mySqlConstraint->dropCommand()}");
        });
        return new Unconformity($description, $instructions);
    }
}

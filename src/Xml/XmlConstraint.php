<?php

namespace Squille\Cave\Xml;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractConstraintModel;
use Squille\Cave\Models\ConstraintModelInterface;
use Squille\Cave\Unconformity;

class XmlConstraint extends AbstractConstraintModel
{
    private $pdo;
    private $table;

    public function __construct(PDO $pdo, array $partialConstraints)
    {
        $this->pdo = $pdo;
        $this->table = $partialConstraints[0]->getTable();
        parent::__construct($partialConstraints);
    }

    public function getTable()
    {
        return $this->table;
    }

    protected function incompatibleConstraintUnconformity(ConstraintModelInterface $constraintModel)
    {
        $description = "alter table {$this->getTable()} {$this->dropCommand()}";
        $instructions = new InstructionsList();
        $instructions->add(function () {
            $this->pdo->query("ALTER TABLE {$this->getTable()} {$this->dropCommand()}");
        });
        $instructions->add(function () use ($constraintModel) {
            $this->pdo->query("ALTER TABLE {$this->getTable()} ADD $constraintModel");
        });
        return new Unconformity($description, $instructions);
    }

    public function getName()
    {
        // TODO: Implement getName() method.
    }
}

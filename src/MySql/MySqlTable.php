<?php

namespace Squille\Cave\MySql;

use PDO;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\ITableModel;
use Squille\Cave\MySql\Keys\MySqlKeysList;
use Squille\Cave\UnconformitiesList;
use Squille\Cave\Unconformity;

class MySqlTable implements ITableModel
{
    private $Name;
    private $Engine;
    private $Row_format;
    private $Collation;
    private $Checksum;

    private $pdo;
    private $fields;
    private $keys;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->fields = new MySqlFieldsList($this->pdo, $this);
        $this->keys = new MySqlKeysList($this->pdo, $this);
    }

    /**
     * @inheritDoc
     */
    public function checkIntegrity(ITableModel $model)
    {
        $unconformities = new UnconformitiesList();

        if ($this->getEngine() != $model->getEngine()) {
            $unconformities->add($this->engineUnconformity($model));
        }

        if ($this->getRowFormat() != $model->getRowFormat()) {
            $unconformities->add($this->rowFormatUnconformity($model));
        }

        if ($this->getCollation() != $model->getCollation()) {
            $unconformities->add($this->collateUnconformity($model));
        }

        if ($this->getChecksum() != $model->getChecksum()) {
            $unconformities->add($this->checksumUnconformity($model));
        }

        return $unconformities
            ->merge($this->getFields()->checkIntegrity($model->getFields()))
            ->merge($this->getKeys()->checkIntegrity($model->getKeys()));
    }

    /**
     * @inheritDoc
     */
    public function getEngine()
    {
        return $this->Engine;
    }

    private function engineUnconformity(ITableModel $model)
    {
        $description = "alter table `{$model->getName()}` engine {`{$this->getEngine()}` -> `{$model->getEngine()}`}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($model) {
            $this->pdo->query("
                ALTER TABLE `{$model->getName()}`
                ENGINE `{$model->getEngine()}`
            ");
        });
        return new Unconformity($description, $instructions);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @inheritDoc
     */
    public function getRowFormat()
    {
        return $this->Row_format;
    }

    private function rowFormatUnconformity(ITableModel $model)
    {
        $description = "alter table `{$model->getName()}` row_format {`{$this->getRowFormat()}` -> `{$model->getRowFormat()}`}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($model) {
            $this->pdo->query("
                ALTER TABLE `{$model->getName()}`
                ROW_FORMAT `{$model->getEngine()}`
            ");
        });
        return new Unconformity($description, $instructions);
    }

    /**
     * @inheritDoc
     */
    public function getCollation()
    {
        return $this->Collation;
    }

    private function collateUnconformity(ITableModel $model)
    {
        $description = "alter table `{$model->getName()}` collate {`{$this->getCollation()}` -> `{$model->getCollation()}`}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($model) {
            $this->pdo->query("
                ALTER TABLE `{$model->getName()}`
                COLLATE `{$model->getCollation()}`
            ");
        });
        return new Unconformity($description, $instructions);
    }

    /**
     * @inheritDoc
     */
    public function getChecksum()
    {
        return $this->Checksum;
    }

    private function checksumUnconformity(ITableModel $model)
    {
        $description = "alter table `{$model->getName()}` checksum {`{$this->getChecksum()}` -> `{$model->getChecksum()}`}";
        $instructions = new InstructionsList();
        $instructions->add(function () use ($model) {
            $this->pdo->query("
                ALTER TABLE `{$model->getName()}`
                CHECKSUM {$model->getChecksum()}
            ");
        });
        return new Unconformity($description, $instructions);
    }

    /**
     * @inheritDoc
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @inheritDoc
     */
    public function getKeys()
    {
        return $this->keys;
    }
}

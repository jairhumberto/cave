<?php

namespace Squille\Cave\Xml;

use DOMElement;
use Squille\Cave\InstructionsList;
use Squille\Cave\Models\AbstractFieldModel;
use Squille\Cave\Models\IFieldModel;
use Squille\Cave\Unconformity;

class XmlField extends AbstractFieldModel
{
    private $root;
    private $table;
    private $field;
    private $type;
    private $collation;
    private $null;
    private $key;
    private $default;
    private $extra;
    private $comment;

    public function __construct(DOMElement $element, XmlTable $table)
    {
        $this->root = $element;
        $this->table = $table;
    }

    /**
     * @param DOMElement $element
     * @param XmlTable $table
     * @return XmlField
     */
    public static function fromDomElement(DOMElement $element, XmlTable $table)
    {
        $instance = new XmlField($element, $table);
        $instance->setField($element->getAttribute("field"));
        $instance->setType($element->getAttribute("type"));
        $instance->setCollation($element->getAttribute("collation"));
        $instance->setNull($element->getAttribute("null"));
        $instance->setKey($element->getAttribute("key"));
        $instance->setDefault($element->getAttribute("default"));
        $instance->setExtra($element->getAttribute("extra"));
        $instance->setComment($element->getAttribute("comment"));
        return $instance;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    public function __toString()
    {
        $columnDefinition = $this->getColumnDefinition();
        return "`{$this->getField()}` $columnDefinition";
    }

    private function getColumnDefinition()
    {
        $columnDefinition = [$this->getType()];
        if ($this->getNull() == "YES") {
            $columnDefinition[] = "NULL";
        } else {
            $columnDefinition[] = "NOT NULL";
        }
        if (strlen($this->getDefault())) {
            $default = $this->defaultFiltered();
            if (!$this->defaultIsFunction() && !is_numeric($default)) {
                $default = "'$default'";
            }
            $columnDefinition[] = "DEFAULT $default";
        }
        if ($this->getExtra()) {
            $columnDefinition[] = $this->getExtra();
        }
        if ($this->getComment()) {
            $columnDefinition[] = "COMMENT '{$this->getComment()}'";
        }
        if ($this->getCollation()) {
            $columnDefinition[] = "COLLATE {$this->getCollation()}";
        }
        return join(" ", $columnDefinition);
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function getNull()
    {
        return $this->null;
    }

    /**
     * @param string $null
     */
    public function setNull($null)
    {
        $this->null = $null;
    }

    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    private function defaultFiltered()
    {
        $default = $this->getDefault();
        if (strpos($this->getType(), "bit") !== false) {
            $default = substr($default, 2, -1);
        }
        return $default;
    }

    private function defaultIsFunction()
    {
        return substr($this->getDefault(), -1) == ")";
    }

    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param string $extra
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function getCollation()
    {
        return $this->collation;
    }

    /**
     * @param string $collation
     */
    public function setCollation($collation)
    {
        $this->collation = $collation;
    }

    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    protected function typeUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} type {{$this->getType()} -> {$fieldModel->getType()}}";
        return new Unconformity($description);
    }

    public function getTable()
    {
        return $this->table;
    }

    protected function collationUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} collate {{$this->getCollation()} -> {$fieldModel->getCollation()}}";
        return new Unconformity($description);
    }

    protected function nullUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} null {{$this->getNull()} -> {$fieldModel->getNull()}}";
        return new Unconformity($description);
    }

    protected function defaultUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} default {'{$this->getDefault()}' -> '{$fieldModel->getDefault()}'}";
        return new Unconformity($description);
    }

    protected function extraUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} extra {{$this->getExtra()} -> {$fieldModel->getExtra()}}";
        return new Unconformity($description);
    }

    protected function commentUnconformity(IFieldModel $fieldModel)
    {
        $description = "alter table {$fieldModel->getTable()} modify {$fieldModel->getField()} comment {'{$this->getComment()}' -> '{$fieldModel->getComment()}'}";
        return new Unconformity($description);
    }

    protected function definitionUnconformity(IFieldModel $fieldModel)
    {
        $instructions = new InstructionsList();
        $instructions->add(function () use ($fieldModel) {
            $this->pdo->query("
                ALTER TABLE {$fieldModel->getTable()}
                MODIFY $fieldModel
            ");
        });
        return new Unconformity("", $instructions);
    }
}

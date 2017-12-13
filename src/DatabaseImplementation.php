<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 *
 * @copyright Copyright (c) 2018 Squille
 * @license   this software is distributed under MIT license, see the
 *            LICENSE file.
 */

namespace Squille\Cave;

class DatabaseImplementation extends Database
{
    protected $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
        parent::__construct();

        // Estabelecendo as propriedades do banco.
        $result = $this->connection->query("SHOW VARIABLES LIKE 'character_set_database'");
        $this->setCharset($result->fetchObject()->Value);
        $result->closeCursor();

        $result = $this->connection->query("SHOW VARIABLES LIKE 'collation_database'");
        $this->setCollation($result->fetchObject()->Value);
        $result->closeCursor();

        // Estabelecendo as tabelas do banco.
        $result = $this->connection->query("SHOW TABLE STATUS");
        while($reg = $result->fetchObject()) {
            $table = new Table;

            // Estabelecendo as propriedades da tabela.
            $table->setName($reg->Name);
            $table->setEngine($reg->Engine);
            $table->setRow_format($reg->Row_format);
            $table->setCollation($reg->Collation);
            $table->setChecksum($reg->Checksum);

            // Estabelecendo  os campos da tabela.
            $subresult = $this->connection->query(sprintf("SHOW FULL FIELDS IN %s", $reg->Name));
            while($subreg = $subresult->fetchObject()) {
                $field = new Field;

                // Estabelecendo as propriedades do campo.
                $field->setField($subreg->Field);
                $field->setType($subreg->Type);

                $charset = explode("_", $subreg->Collation);
                $charset = $charset[0];
                $field->setCharset($charset);

                $field->setCollation($subreg->Collation);
                $field->setNull($subreg->Null);
                $field->setKey($subreg->Key);
                $field->setDefault($subreg->Default);
                $field->setExtra($subreg->Extra);
                $field->setComment($subreg->Comment);

                $table->getFields()->addItem($field);
            }
            $subresult->closeCursor();

            // Estabelecendo os indices da tabela.
            $subresult = $this->connection->query(sprintf("SHOW INDEXES IN %s", $reg->Name));
            while($subreg = $subresult->fetchObject()) {
                $index = new Index;

                // Estabelecendo as propriedades do indice.
                $index->setNon_unique($subreg->Non_unique);
                $index->setKey_name($subreg->Key_name);
                $index->setSeq_in_index($subreg->Seq_in_index);
                $index->setColumn_name($subreg->Column_name);
                $index->setCollation($subreg->Collation);
                $index->setSub_part($subreg->Sub_part);
                $index->setPacked($subreg->Packed);
                $index->setNull($subreg->Null);
                $index->setIndex_type($subreg->Index_type);
                $index->setComment($subreg->Comment);

                $table->getIndexes()->addItem($index, $subreg->Column_name);
            }
            $subresult->closeCursor();

            // Estabelecendo as fks da tabela.
            $subresult = $this->connection->query(sprintf("SHOW CREATE TABLE %s", $reg->Name));
            $subreg = $subresult->fetchAll(\PDO::FETCH_NUM);
            $subresult->closeCursor();

            if (!count($subreg)) {
                continue;
            }

            $subreg = $subreg[0];

            // A consulta retorna o código de criação da tabela separado por new lines
            if (!isset($subreg[1])) {
                continue;
            }

            $createtable = $subreg[1];
            $lines = explode("\n", $createtable);

            // Percorre cada linha em busca de relacionamentos existentes
            foreach($lines as $line) {

                // Verificando se em alguma linha do CREATE TABLE foi definido FK.
                if(preg_match('/^\s*CONSTRAINT `.*` FOREIGN KEY \(`[^\)]*`\) REFERENCES/', $line)) {
                    $fk = new FK;

                    // Estabelecendo as propriedades da fk
                    $symbol = preg_replace('/.*CONSTRAINT `(.+)` FOREIGN KEY.*/', '$1', $line);
                    $fk->setSymbol($symbol);

                    // Estabelecendo os indices da fk
                    $indexes = preg_replace('/.*FOREIGN KEY \(`(.+)`\) REFERENCES.*/', '$1', $line);
                    $indexes = str_replace("`", "", $indexes);
                    $indexes = explode(", ", $indexes);

                    foreach($indexes as $indexname) {
                        $index = new Index;
                        $index->setColumn_name($indexname);
                        $fk->getIndexes()->addItem($index);
                    }

                    // Define as referencias da fk
                    $referencetable = preg_replace('/.*REFERENCES `([^`]+)`.*/', '$1', $line);
                    $fk->getReferences()->setTable($referencetable); // Tabela a que se relaciona.

                    $referenceindexes = preg_replace('/.*REFERENCES `[^`]+` \(`([^\)]+)`\).*/', '$1', $line);
                    $referenceindexes = str_replace("`", "", $referenceindexes);
                    $referenceindexes = explode(", ", $referenceindexes);

                    foreach($referenceindexes as $indexname) {
                        $index = new Index;
                        $index->setColumn_name($indexname);
                        $fk->getReferences()->addItem($index);
                    }

                    $table->getFKs()->addItem($fk);
                }

            }

            $this->getTables()->addItem($table, $reg->Name);
        }
        $result->closeCursor();
    }

    public function backup()
    {} /* implementar backup depois. backup geral do banco atraves desse método */

    public function integrity(Model $model)
    {
        $ul = new UnconformanceList;
        $ul->initMessage(); // Iniciando o módulo para mensagens.

        // Se os objetos são iguais não há necessidade de verificação.
        if ($this != $model) {

            // Confere charset do banco.
            if($this->getCharset() != $model->getCharset()) {
                $desc = sprintf('Database CHARSET (%s) differs from the model.', $this->getCharset());
                $errorid = $ul->addMessage($desc);

                $sqllist = new SQLList;
                $sqllist->addItem(new SQL(sprintf("ALTER DATABASE CHARACTER SET %s", $model->getCharset())));

                $measure = sprintf('Changing the database CHARSET to %s', $model->getCharset());

                $ul->addItem(new Unconformance($sqllist, $measure));
                $ul->addSolution(sprintf("ALTER DATABASE CHARACTER SET %s", $model->getCharset()), $errorid);
            }

            // Confere collation do banco.
            if($this->getCollation() != $model->getCollation()) {
                $desc = sprintf('Database COLLATION (%s) differs from the model.', $this->getCollation());
                $errorid = $ul->addMessage($desc);

                $sqllist = new SQLList;
                $sqllist->addItem(new SQL(sprintf("ALTER DATABASE COLLATE %s", $model->getCollation())));

                $measure = sprintf('Changing the database COLLATION to %s', $model->getCollation());

                $ul->addItem(new Unconformance($sqllist, $measure));
                $ul->addSolution(sprintf("ALTER DATABASE COLLATE %s", $model->getCollation()), $errorid);
            }

            // Conferindo as tabelas.
            foreach($model->getTables()->getItens() as $modeltable) {
                foreach($this->getTables()->getItens() as $table) {

                    // Encontrando a tabela.
                    if($modeltable->getName() == $table->getName()) {
                        // Se a tabela é igual ao modelo não há necessidade de verificação. Continua na proxima tabela.
                        if($modeltable == $table) continue 2;

                        // Confere o engine.
                        if($table->getEngine() != $modeltable->getEngine()) {
                            $desc = sprintf('The engine (%s) of the table %s differs from the model.', $table->getEngine(), $table->getName());
                            $errorid = $ul->addMessage($desc);
                            $sqllist = new SQLList;

                            /* No caso abaixo é necessário percorrer o banco em busca de relacionamentos com
                               essa tabela e excluí-los, pois o engine MyISAM não permite relacionamentos,
                               enquanto o InnoDB permite. */
                            if($modeltable->getEngine() == "MyISAM" && $table->getEngine() == "InnoDB") {
                                /* Percorre todas as tabelas do banco em busca de relacionamentos com a tabela que se quer
                                   mudar o engine */
                                foreach($this->getTable()->getItens() as $subtable) {

                                    // Percorre todas as FKs da tabela corrente verificando se alguma se refere à tabela atual
                                    foreach($table->getFKs()->getItens() as $fk) {
                                        if($fk->getReferences()->getTable() == $table->getName()) {
                                            // Se encontrar uma referencia à tabela atual, prepara a SQL de exclusão da referência.
                                            $sqllist->addItem(new SQL(sprintf(
                                                "ALTER TABLE `%s` DROP FOREIGN KEY `%s`",
                                                $subtable->getName(),
                                                $fk->getSymbol()
                                            )));

                                            $ul->addSolution(sprintf(
                                                "ALTER TABLE `%s` DROP FOREIGN KEY `%s`",
                                                $subtable->getName(),
                                                $fk->getSymbol()
                                            ), $errorid);
                                        }
                                    }
                                }
                            }

                            // Pronto para mudar o engine.
                            $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` ENGINE = %s", $table->getName(), $modeltable->getEngine())));

                            $measure = sprintf('Changing the table engine of the table %s to %s', $table->getName(), $modeltable->getEngine());

                            $ul->addItem(new Unconformance($sqllist, $measure));
                            $ul->addSolution(sprintf("ALTER TABLE `%s` ENGINE = %s", $table->getName(), $modeltable->getEngine()), $errorid);
                        }

                        // Confere o Row_format da tabela.
                        if($table->getRow_format() != $modeltable->getRow_format()) {
                            $desc = sprintf('The row format (%s) of the table %s differs from the model.', $table->getRow_format(), $table->getName());
                            $errorid = $ul->addMessage($desc);
                            $sqllist = new SQLList;
                            $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` ROW_FORMAT = %s", $table->getName(), $modeltable->getRow_format())));

                            $measure = sprintf('Changing the row format of the table %s to %s', $table->getName, $modeltable->getRow_format());

                            $ul->addItem(new Unconformance($sqllist, $measure));
                            $ul->addSolution(sprintf("ALTER TABLE `%s` ROW_FORMAT = %s", $table->getName(), $modeltable->getRow_format()), $errorid);
                        }

                        // Confere o Collation da tabela.
                        if($table->getCollation() != $modeltable->getCollation()) {
                            $desc = sprintf('The collation (%s.%s) of the table %s differs from the model.', $modeltable->getCharset(), $table->getCollation(), $table->getName());
                            $errorid = $ul->addMessage($desc);
                            $sqllist = new SQLList;
                            $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` CHARACTER SET %s COLLATE %s", $table->getName(), $modeltable->getCharset(), $modeltable->getCollation())));

                            $measure = sprintf('Changing the collation of the table %s to %s.%s', $table->getName(), $modeltable->getCharset(), $modeltable->getCollation());

                            $ul->addItem(new Unconformance($sqllist, $measure));
                            $ul->addSolution(sprintf("ALTER TABLE `%s` CHARACTER SET %s COLLATE %s", $table->getName(), $modeltable->getCharset(), $modeltable->getCollation()), $errorid);
                        }

                        // Confere o Checksum da tabela.
                        if($table->getChecksum() != $modeltable->getChecksum()) {
                            $desc = sprintf('The checksum (%s) of the table %s differs from the model.', $table->getChecksum(), $table->getName());
                            $errorid = $ul->addMessage($desc);
                            $sqllist = new SQLList;
                            $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` CHECKSUM = %s", $table->getName(), $modeltable->getChecksum())));

                            $measure = sprintf('Changing the checksum of the table %s to %s', $table->getName(), $modeltable->getChecksum());

                            $ul->addItem(new Unconformance($sqllist, $measure));
                            $ul->addSolution(sprintf("ALTER TABLE `%s` CHECKSUM = %s", $table->getName(), $modeltable->getChecksum()), $errorid);
                        }

                        // Após o término das verificações da tabela, é necessário fazer verificações nos campos.

                        $i = -1; // Indicador do número de campos da tabela.

                        // Verificando se existe algum campo no modelo que não está na tabela.
                        foreach($modeltable->getFields()->getItens() as $modelfield) {

                            $i++;

                            foreach($table->getFields()->getItens() as $tablefield) {
                                // Ao encontrar o campo compara as propriedades.
                                if($tablefield->getField() == $modelfield->getField()) {

                                    // Excluir o valor default quando for o caso.
                                    if(!$modelfield->getDefault() && $tablefield->getDefault()) {
                                        $desc = sprintf('The field %s.%s should not have a default value. "%s" was found.', $table->getName(), $tablefield->getField(), $tablefield->getDefault());
                                        $errorid = $ul->addMessage($desc);
                                        $sqllist = new SQLList;
                                        $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` ALTER COLUMN `%s` DROP DEFAULT", $table->getName(), $tablefield->getField())));
                                        $ul->addSolution(sprintf("ALTER TABLE `%s` ALTER COLUMN `%s` DROP DEFAULT", $table->getName(), $tablefield->getField()));

                                        $measure = sprintf('Removing the default value of the field %s.%s', $table->getName(), $tablefield->getField());

                                        $ul->addItem(new Unconformance($sqllist, $measure));
                                    }

                                    // Se houver alguma diferença nos campos.
                                    if(
                                        $tablefield->getType() != $modelfield->getType() ||
                                        $tablefield->getCollation() != $modelfield->getCollation() ||
                                        $tablefield->getNull() != $modelfield->getNull() ||
                                        $tablefield->getDefault() != utf8_decode($modelfield->getDefault()) ||
                                        $tablefield->getExtra() != $modelfield->getExtra() ||
                                        $tablefield->getComment() != $modelfield->getComment()
                                    ) {
                                        $desc = sprintf('The declaration of the field %s.%s differs from the model.', $table->getName(), $tablefield->getField());
                                        $errorid = $ul->addMessage($desc);
                                        $sqllist = new SQLList;

                                        if(strtoupper($modelfield->getDefault()) <> "CURRENT_TIMESTAMP") {
                                            $default = "'".$modelfield->getDefault()."'";
                                        } else {
                                            $default = $modelfield->getDefault();
                                        }

                                        if ($modelfield->getExtra() == "auto_increment") {
                                            $result = $this->connection->query(sprintf("SELECT MAX(%s) AS cnt FROM %s", $tablefield->getField(), $table->getName()));
                                            $tablerows = $result->fetchObject();
                                            $auto_increment = $tablerows->cnt + 1;
                                        } else {
                                            $auto_increment = 1;
                                        }

                                        $sqllist->addItem(new SQL('SET FOREIGN_KEY_CHECKS = 0'));
                                        $sqllist->addItem(new SQL(sprintf(
                                            "ALTER TABLE `%s` MODIFY COLUMN `%s` %s%s %s%s%s%s",
                                            $table->getName(),
                                            $tablefield->getField(),
                                            $modelfield->getType(),
                                            $modelfield->getCollation() ? " CHARACTER SET " . $modelfield->getCharset() . " COLLATE " . $modelfield->getCollation() : "",
                                            $modelfield->getNull() == "YES" ? "NULL" : "NOT NULL",
                                            $modelfield->getDefault() != "" ? " DEFAULT " . utf8_decode($default) : "",
                                            $modelfield->getExtra() ? " AUTO_INCREMENT, AUTO_INCREMENT=" . $auto_increment : "",
                                            $modelfield->getComment() ? " COMMENT '" . $modelfield->getComment() . "'" : ""
                                        )));
                                        $sqllist->addItem(new SQL('SET FOREIGN_KEY_CHECKS = 1'));

                                        $ul->addSolution(sprintf(
                                            "ALTER TABLE `%s` MODIFY COLUMN `%s` %s%s %s%s%s%s",
                                            $table->getName(),
                                            $tablefield->getField(),
                                            $modelfield->getType(),
                                            $modelfield->getCollation() ? " CHARACTER SET " . $modelfield->getCharset() . " COLLATE " . $modelfield->getCollation() : "",
                                            $modelfield->getNull() == "YES" ? "NULL" : "NOT NULL",
                                            $modelfield->getDefault() != "" ? " DEFAULT " . $default : "",
                                            $modelfield->getExtra() ? " AUTO_INCREMENT, AUTO_INCREMENT=" . $auto_increment : "",
                                            $modelfield->getComment() ? " COMMENT '" . $modelfield->getComment() . "'" : ""
                                        ), $errorid);

                                        $measure = sprintf('Modifying the field %s.%s to comply to the model', $table->getName(), $tablefield->getField());

                                        $ul->addItem(new Unconformance($sqllist, $measure));
                                    }

                                    continue 2;
                                }

                            }

                            $desc = sprintf('The field %s.%s was not found.', $modeltable->getName(), $modelfield->getField());
                            $errorid = $ul->addMessage($desc);
                            $sqllist = new SQLList;

                            // Se não for o primeiro campo, adiciona o campo após o campo passado, senão, adiciona o campo no primeiro lugar da tabela.
                            if($i) {
                                $positionword = sprintf("AFTER `%s`", $modeltable->getFields()->item($i-1)->getField());
                            } else {
                                $positionword = "FIRST";
                            }

                            if(strtoupper($modelfield->getDefault()) <> "CURRENT_TIMESTAMP") {
                                $default = "'".$modelfield->getDefault()."'";
                            } else {
                                $default = $modelfield->getDefault();
                            }

                            $sqllist->addItem(new SQL(sprintf(
                                "ALTER TABLE `%s` ADD COLUMN `%s` %s%s %s%s%s%s %s",
                                $table->getName(),
                                $modelfield->getField(),
                                $modelfield->getType(),
                                $modelfield->getCollation() ? " CHARACTER SET " . $modelfield->getCharset() . " COLLATE " . $modelfield->getCollation() : "",
                                $modelfield->getNull() == "YES" ? "NULL" : "NOT NULL",
                                $modelfield->getDefault() ? " DEFAULT " . $default : "",
                                $modelfield->getExtra() ? " AUTO_INCREMENT" : "",
                                $modelfield->getComment() ? " COMMENT '" . $modelfield->getComment() . "'" : "",
                                $positionword
                            )));
                            $ul->addSolution(sprintf(
                                "ALTER TABLE `%s` ADD COLUMN `%s` %s%s %s%s%s%s %s",
                                $table->getName(),
                                $modelfield->getField(),
                                $modelfield->getType(),
                                $modelfield->getCollation() ? " CHARACTER SET " . $modelfield->getCharset() . " COLLATE " . $modelfield->getCollation() : "",
                                $modelfield->getNull() == "YES" ? "NULL" : "NOT NULL",
                                $modelfield->getDefault() ? " DEFAULT " . $default : "",
                                $modelfield->getExtra() ? " AUTO_INCREMENT" : "",
                                $modelfield->getComment() ? " COMMENT '" . $modelfield->getComment() . "'" : "",
                                $positionword
                            ), $errorid);

                            $measure = sprintf('Adding the field %s.%s acordingly to the model', $table->getName(), $modelfield->getField());

                            $ul->addItem(new Unconformance($sqllist, $measure));

                            if($primeiro) $primeiro = false;
                        }

                        // Verificando se existe algum campo na tabela que não está no modelo.
                        foreach($table->getFields()->getItens() as $tablefield) {

                            foreach($modeltable->getFields()->getItens() as $modelfield) {
                                if($tablefield->getField() == $modelfield->getField()) {
                                    continue 2;
                                }
                            }

                            $desc = sprintf('The field %s.%s should not exist.', $table->getName(), $tablefield->getField());
                            $errorid = $ul->addMessage($desc);
                            $sqllist = new SQLList;

                            /* Para excluir um campo, é necessário percorrer todo o banco em busca de relacionamentos com ele.
                               Para isso o foreach abaixo percorre todas as tabelas achadas no banco */
                            foreach($this->getTables()->getItens() as $datatable) {

                                /* Ao entrar em uma tabela, é necessário verificar todas as FKs existentes em busca de uma que
                                   faça referência com o campo a ser excluído. */
                                foreach($datatable->getFKs()->getItens() as $datatablefk) {

                                    // Se encontrar uma referencia à tabela onde se encontra o campo que se quer excluir
                                    if($datatablefk->getReferences()->getTable() == $table->getName()) {

                                        /* Então percorre todas as colunas em busca da referencia
                                           ao campo que se quer excluir */
                                        foreach($datatablefk->getReferences()->getItens() as $datatablefkreferenceindex) {

                                            // Se encontrar a referencia desejada
                                            if($datatablefkreferenceindex->getColumn_name() == $tablefield->getField()) {
                                                $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`", $datatable->getName(), $datatablefk->getSymbol())));
                                                $ul->addSolution(sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`", $datatable->getName(), $datatablefk->getSymbol()), $errorid);
                                            }
                                        }
                                    }
                                }
                            }

                            $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` DROP COLUMN `%s`", $table->getName(), $tablefield->getField())));
                            $ul->addSolution(sprintf("ALTER TABLE `%s` DROP COLUMN `%s`", $table->getName(), $tablefield->getField()), $errorid);

                            $measure = sprintf('Removing the field %s.%s to comply to the model', $table->getName(), $tablefield->getField());

                            $ul->addItem(new Unconformance($sqllist, $measure));
                        }

                        // Verificar a ordem dos campos de acordo com a ordem do modelo
                        foreach ($modeltable->getFields()->getItens() as $key => $modelfield) {
                            if ($table->getFields()->item($key)
                                && $modelfield
                                && $table->getFields()->item($key)->getField() != $modelfield->getField()
                                && $table->getFields()->length() == $modeltable->getFields()->length()) {

                                $desc = sprintf('The field %s.%s is not in the same order as in the model.', $table->getName(), $table->getFields()->item($key)->getField());
                                $errorid = $ul->addMessage($desc);
                                $sqllist = new SQLList;

                                if(strtoupper($modelfield->getDefault()) <> "CURRENT_TIMESTAMP") {
                                    $default = "'".$modelfield->getDefault()."'";
                                } else {
                                    $default = $modelfield->getDefault();
                                }

                                if($key) {
                                    $sqllist->addItem(new SQL(sprintf(
                                        "ALTER TABLE `%s` CHANGE `%s` `%s` %s%s %s%s%s%s AFTER `%s`",
                                        $modeltable->getName(),
                                        $modelfield->getField(),
                                        $modelfield->getField(),
                                        $modelfield->getType(),
                                        $modelfield->getCollation() ? " CHARACTER SET " . $modelfield->getCharset() . " COLLATE " . $modelfield->getCollation() : "",
                                        $modelfield->getNull() == "YES" ? "NULL" : "NOT NULL",
                                        $modelfield->getDefault() ? " DEFAULT " . $default : "",
                                        $modelfield->getExtra() ? " AUTO_INCREMENT" : "",
                                        $modelfield->getComment() ? " COMMENT '" . $modelfield->getComment() . "'" : "",
                                        $modeltable->getFields()->item($key-1)->getField()
                                    )));
                                    $ul->addSolution(sprintf(
                                        "ALTER TABLE `%s` CHANGE `%s` `%s` %s%s %s%s%s%s AFTER `%s`",
                                        $modeltable->getName(),
                                        $modelfield->getField(),
                                        $modelfield->getField(),
                                        $modelfield->getType(),
                                        $modelfield->getCollation() ? " CHARACTER SET " . $modelfield->getCharset() . " COLLATE " . $modelfield->getCollation() : "",
                                        $modelfield->getNull() == "YES" ? "NULL" : "NOT NULL",
                                        $modelfield->getDefault() ? " DEFAULT " . $default : "",
                                        $modelfield->getExtra() ? " AUTO_INCREMENT" : "",
                                        $modelfield->getComment() ? " COMMENT '" . $modelfield->getComment() . "'" : "",
                                        $modeltable->getFields()->item($key - 1)->getField()
                                    ), $errorid);
                                } else {
                                    $sqllist->addItem(new SQL(sprintf(
                                        "ALTER TABLE `%s` CHANGE `%s` `%s` %s%s %s%s%s%s FIRST",
                                        $modeltable->getName(),
                                        $modelfield->getField(),
                                        $modelfield->getField(),
                                        $modelfield->getType(),
                                        $modelfield->getCollation() ? " CHARACTER SET " . $modelfield->getCharset() . " COLLATE " . $modelfield->getCollation() : "",
                                        $modelfield->getNull() == "YES" ? "NULL" : "NOT NULL",
                                        $modelfield->getDefault() ? " DEFAULT " . $default : "",
                                        $modelfield->getExtra() ? " AUTO_INCREMENT" : "",
                                        $modelfield->getComment() ? " COMMENT '" . $modelfield->getComment() . "'" : ""
                                    )));
                                    $ul->addSolution(sprintf(
                                        "ALTER TABLE `%s` CHANGE `%s` `%s` %s%s %s%s%s%s FIRST",
                                        $modeltable->getName(),
                                        $modelfield->getField(),
                                        $modelfield->getField(),
                                        $modelfield->getType(),
                                        $modelfield->getCollation() ? " CHARACTER SET " . $modelfield->getCharset() . " COLLATE " . $modelfield->getCollation() : "",
                                        $modelfield->getNull() == "YES" ? "NULL" : "NOT NULL",
                                        $modelfield->getDefault() ? " DEFAULT " . $default : "",
                                        $modelfield->getExtra() ? " AUTO_INCREMENT" : "",
                                        $modelfield->getComment() ? " COMMENT '" . $modelfield->getComment() . "'" : ""
                                    ), $errorid);
                                }

                                $measure = sprintf('Changing the order of the field %s.%s to comply to the model', $table->getName(), $modelfield->getField());

                                $ul->addItem(new Unconformance($sqllist, $measure));
                            }
                        }

                        /* Verificar os índices da tabela. Se alguma chave estiver errada, é mais facil recriar todas as chaves.
                           Isso é verificado e feito após a conferência de todas as tabelas.
                        */
                        foreach($modeltable->getIndexes()->getItens() as $key => $modelindex) {
                            if($table->getIndexes()->item($key) != $modelindex) {
                                $wrongkey = true;
                                break;
                            }
                        }

                        /* Verificar os relacionamentos da tabela. Se algum estiver errado, é mais facil recriar todos os relacionamentos.
                           Isso é verificado e feito após a conferência de todas as tabelas.
                        */
                        // Verifica se todos os relacionamentos do modelo estão no banco.
                        foreach($modeltable->getFKs()->getItens() as $modelfk) {
                            foreach($table->getFKs()->getItens() as $fk) {
                                if($modelfk == $fk) {
                                    continue 2;
                                }
                            }
                            /*foreach($table->getFKs()->getItens() as $fk) {
                                printf("model:%s.%s\n",
                                        $modeltable->getName(),$modelfk->getSymbol()
                                        );
                                printf("imple:%s.%s\n",
                                        $table->getName(),$fk->getSymbol()
                                        );
                            }*/
                            $wrongkey = true;
                            break;
                        }

                        // Verifica se apenas os relacionamentos do modelo estão no banco.
                        foreach($table->getFKs()->getItens() as $fk) {
                            foreach($modeltable->getFKs()->getItens() as $modelfk) {
                                if($modelfk == $fk) continue 2;
                            }
                            $wrongkey = true;
                            break;
                        }

                        // Ao encontrar a tabela, não há necessidade de conferir as próximas. Encerra o loop chamando a próxima tabela(se houver).
                        continue 2;
                    }
                }

                // A tabela do modelo não existe no banco e precisa ser criada.
                $desc = sprintf('The table %s was not found.', $modeltable->getName());
                $errorid = $ul->addMessage($desc);
                $sqllist = new SQLList;

                $newfields = array();
                foreach($modeltable->getFields()->getItens() as $modelfield) {

                    if(strtoupper($modelfield->getDefault()) <> "CURRENT_TIMESTAMP") {
                        $default = "'".$modelfield->getDefault()."'";
                    } else {
                        $default = $modelfield->getDefault();
                    }

                    $newfields[] = sprintf(
                        "`%s` %s%s %s%s%s",
                        $modelfield->getField(),
                        $modelfield->getType(),
                        $modelfield->getCollation() ? " CHARACTER SET " . $modelfield->getCharset() . " COLLATE " . $modelfield->getCollation() : "",
                        $modelfield->getNull() == "YES" ? "NULL" : "NOT NULL",
                        $modelfield->getDefault() ? " DEFAULT " . $default : "",
                        $modelfield->getComment() ? " COMMENT '" . $modelfield->getComment() . "'" : ""
                    );
                }

                $sqllist->addItem (new SQL(sprintf(
                    "CREATE TABLE `%s` (%s) ENGINE = %s, ROW_FORMAT = %s, CHARACTER SET = %s, COLLATE = %s, CHECKSUM = %d",
                    $modeltable->getName(),
                    implode(",", $newfields),
                    $modeltable->getEngine(),
                    $modeltable->getRow_format(),
                    $modeltable->getCharset(),
                    $modeltable->getCollation(),
                    $modeltable->getChecksum()
                )));

                $ul->addSolution(sprintf(
                    "CREATE TABLE `%s` (%s) ENGINE = %s, ROW_FORMAT = %s, CHARACTER SET = %s, COLLATE = %s, CHECKSUM = %d",
                    $modeltable->getName(),
                    implode(",", $newfields),
                    $modeltable->getEngine(),
                    $modeltable->getRow_format(),
                    $modeltable->getCharset(),
                    $modeltable->getCollation(),
                    $modeltable->getChecksum()
                ), $errorid);

                if($modeltable->getIndexes()->length()) {
                    // Recriar os índices se essa tabela possui chave.
                    $wrongkey = true;
                }

                $measure = sprintf('Creating the table %s', $modeltable->getName());

                $ul->addItem(new Unconformance($sqllist, $measure));
            }

            // Verificando se as tabela do banco existem no modelo.
            foreach($this->getTables()->getItens() as $table) {
                foreach($model->getTables()->getItens() as $modeltable) {
                    if($table->getName() == $modeltable->getName()) {
                        continue 2;
                    }
                }

                // Preparando objeto de correção de problemas.
                $desc = sprintf('The table %s should not exist.', $table->getName());
                $errorid = $ul->addMessage($desc);
                $sqllist = new SQLList;

                // Para excluir uma tabela é necessário excluir todos os relacionamentos com ela.
                // Procura em outras tabelas, relacionamentos com esta e prepara para excluir.
                foreach($this->getTables()->getItens() as $subtable) {

                    $result = $this->connection->query(sprintf("SHOW CREATE TABLE %s", $subtable->getName()));
                    $registro = $result->fetchAll(\PDO::FETCH_NUM);
                    $result->closeCursor();

                    // Obtem o código de criação da tabela.
                    $createtable = $registro[1];

                    // Separa o código por linhas para verificar se há relacionamentos.
                    $eachline = explode("\n", $createtable);

                    foreach($eachline as $line) {
                        // Encontrando um relacionamento com a tabela a ser excluida
                        if(preg_match(sprintf('/^\s*CONSTRAINT `[^`]*` FOREIGN KEY \(`[^\)]*`\) REFERENCES `%s` \(`[^\)]*`\)/', $table->getName()), $line)) {
                            $fksymbol = preg_replace('/.*CONSTRAINT `(.+)` FOREIGN KEY.*/', '$1', $line);

                            // Ao encontrar, cria a SQL de exclusão do relacionamento.
                            $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`", $subtable->getName(), $fksymbol)));
                            $ul->addSolution(sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`", $subtable->getName(), $fksymbol), $errorid);
                        }
                    }
                }

                // Após verificar por relacionamentos em todo o banco, cria a SQL de exclusão da tabela.
                $sqllist->addItem(new SQL(sprintf("DROP TABLE `%s`", $table->getName())));
                $ul->addSolution(sprintf("DROP TABLE `%s`", $table->getName()), $errorid);

                $measure = sprintf('Removing the table %s to comply to the model', $table->getName());

                // Adicionado à lista de informidades a serem corrigidas.
                $ul->addItem(new Unconformance($sqllist, $measure));
            }

            // Wrongkey é true quando alguma chave em alguma tabela apresentou erro ou uma nova tabela com chaves foi criada.
            // Recria todas as chaves do banco.
            if($wrongkey) {
                $desc = 'Index or relationship problems';
                $errorid = $ul->addMessage($desc);
                $sqllist = new SQLList;

                // Exclui todos as chaves estrangeiras de cada tabela do banco.
                foreach($this->getTables()->getItens() as $table) {
                    foreach($table->getFKs()->getItens() as $fk) {
                        $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`", $table->getName(), $fk->getSymbol())));
                        $ul->addSolution(sprintf("ALTER TABLE `%s` DROP FOREIGN KEY `%s`", $table->getName(), $fk->getSymbol()), $errorid);
                    }
                }

                // Excluir todos os índices de cada tabela do banco.
                foreach($this->getTables()->getItens() as $table) {
                    foreach($table->getIndexes()->getItens() as $index) {
                        if($index->getKey_name() == "PRIMARY") {
                            foreach($table->getFields()->getItens() as $field) {
                                // Remove auto increment, para não dar problema na exclusão da chave.
                                if($field->getExtra()) {
                                    $sqllist->addItem(new SQL(sprintf(
                                        "ALTER TABLE `%s` MODIFY COLUMN `%s` %s%s %s%s%s",
                                        $table->getName(),
                                        $field->getField(),
                                        $field->getType(),
                                        $field->getCollation() ? " CHARACTER SET " . $field->getCharset() . " COLLATE " . $field->getCollation() : "",
                                        $field->getNull() == "YES" ? "NULL" : "NOT NULL",
                                        $field->getDefault() ? " DEFAULT " . $field->getDefault() : "",
                                        $field->getComment() ? " COMMENT '" . $field->getComment() . "'" : ""
                                    )));
                                    $ul->addSolution(sprintf(
                                        "ALTER TABLE `%s` MODIFY COLUMN `%s` %s%s %s%s%s",
                                        $table->getName(),
                                        $field->getField(),
                                        $field->getType(),
                                        $field->getCollation() ? " CHARACTER SET " . $field->getCharset() . " COLLATE " . $field->getCollation() : "",
                                        $field->getNull() == "YES" ? "NULL" : "NOT NULL",
                                        $field->getDefault() ? " DEFAULT " . $field->getDefault() : "",
                                        $field->getComment() ? " COMMENT '" . $field->getComment() . "'" : ""
                                    ), $errorid);
                                }
                            }
                            $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` DROP PRIMARY KEY", $table->getName())));
                            $ul->addSolution(sprintf("ALTER TABLE `%s` DROP PRIMARY KEY", $table->getName()), $errorid);
                        } else {
                            $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` DROP INDEX `%s`", $table->getName(), $index->getKey_name())));
                            $ul->addSolution(sprintf("ALTER TABLE `%s` DROP INDEX `%s`", $table->getName(), $index->getKey_name()), $errorid);
                        }
                    }
                }

                // Cria todos os índices em todas as tabelas de acordo com o modelo.
                foreach($model->getTables()->getItens() as $table) {

                    $indexList = array();
                    foreach($table->getIndexes()->getItens() as $index) {
                        $indexList[$index->getKey_name()][] = $index->getColumn_name();
                    }

                    foreach($indexList as $indexname => $makeindex) {
                        if($indexname == "PRIMARY") {
                            $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` ADD PRIMARY KEY (`%s`)", $table->getName(), implode("`,`", $makeindex))));
                            $ul->addSolution(sprintf("ALTER TABLE `%s` ADD PRIMARY KEY (`%s`)", $table->getName(), implode("`,`", $makeindex)), $errorid);
                        } else {
                            $sqllist->addItem(new SQL(sprintf("ALTER TABLE `%s` ADD KEY `%s` (`%s`)", $table->getName(), $indexname, implode("`,`", $makeindex))));
                            $ul->addSolution(sprintf("ALTER TABLE `%s` ADD KEY `%s` (`%s`)", $table->getName(), $indexname, implode("`,`", $makeindex)), $errorid);
                        }
                    }
                }

                // Cria todos os relacionamentos em todas as tabelas de acordo com o modelo.
                foreach($model->getTables()->getItens() as $table) {
                    foreach($table->getFKs()->getItens() as $fk) {
                        $sqllist->addItem(new SQL(sprintf(
                            "ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`)",
                            $table->getName(),
                            $fk->getSymbol(),
                            $fk->getIndexes()->join("`,`"),
                            $fk->getReferences()->getTable(),
                            $fk->getReferences()->join("`,`")
                        )));
                        $ul->addSolution(sprintf(
                            "ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`)",
                            $table->getName(),
                            $fk->getSymbol(),
                            $fk->getIndexes()->join("`,`"),
                            $fk->getReferences()->getTable(),
                            $fk->getReferences()->join("`,`")
                        ), $errorid);
                    }
                }

                $measure = 'Lining up the relationships and indexes';

                $ul->addItem(new Unconformance($sqllist, $measure));
            }

        }
        return $ul;
    }
}

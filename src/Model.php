<?php
/**
 * Squille Cave (https://github.com/jairhumberto/Cave)
 * 
 * @copyright Copyright (c) 2018 Squille
 * @license   this software is distributed under MIT license, see the
 *            LICENSE file.
 */

namespace Squille\Cave;

class Model extends Database
{
    protected $xml;
    
    public function getEncoding()
    {
        return $this->xml->actualEncoding;
    }

    public function __construct(\DOMDocument $xml)
    {
        // Carregando o modelo.
        $this->xml = $xml;

        // Preparando o banco.
        parent::__construct();

        // Estabelecendo as propriedades do banco.
        $this->setCharset($this->xml->firstChild->getAttribute("charset"));
        $this->setCollation($this->xml->firstChild->getAttribute("collation"));

        // Estabelecendo as tabelas.
        foreach($this->xml->getElementsByTagName("table") as $nodetable) {
            $newtable = new Table;

            // Estabelecendo as propriedades da tabela.
            $newtable->setName($nodetable->getAttribute("Name"));
            $newtable->setEngine($nodetable->getAttribute("Engine"));
            $newtable->setRow_format($nodetable->getAttribute("Row_format"));
            $newtable->setCharset($nodetable->getAttribute("Charset"));
            $newtable->setCollation($nodetable->getAttribute("Collation"));
            $newtable->setChecksum($nodetable->getAttribute("Checksum"));

            // Carregando  os campos da tabela.
            foreach($nodetable->getElementsByTagName("field") as $nodetable_nodefield) {
                $field = new Field;

                // Estabelecendo as propriedades do campo.
                $field->setField($nodetable_nodefield->getAttribute("Field"));
                $field->setType($nodetable_nodefield->getAttribute("Type"));
                $field->setCharset($nodetable_nodefield->getAttribute("Charset"));
                $field->setCollation($nodetable_nodefield->getAttribute("Collation"));
                $field->setNull($nodetable_nodefield->getAttribute("Null"));
                $field->setKey($nodetable_nodefield->getAttribute("Key"));

                $field->setDefault($nodetable_nodefield->getAttribute("Default"));
                
                $field->setExtra($nodetable_nodefield->getAttribute("Extra"));
                $field->setComment($nodetable_nodefield->getAttribute("Comment"));

                $newtable->getFields()->addItem($field);
            }

            // Obtendo o nó "indexes" onde estão contidos os índices da tabela
            $nodetable_nodeindexes = $nodetable->getElementsByTagName("indexes")->item(0);

            // Há tabelas que não têm índices, então nodetable_nodeindexes teria valor nulo e a chamada a foreachs nessas condições ocasionaria erro fatal.
            if(get_class($nodetable_nodeindexes) == "DOMElement") {

                foreach($nodetable_nodeindexes->getElementsByTagName("index") as $nodetable_nodeindexes_nodeindex) {
                    $newindex = new Index;

                    // Estabelecendo as propriedades do índice.
                    $newindex->setNon_unique($nodetable_nodeindexes_nodeindex->getAttribute("Non_unique"));
                    $newindex->setKey_name($nodetable_nodeindexes_nodeindex->getAttribute("Key_name"));
                    $newindex->setSeq_in_index($nodetable_nodeindexes_nodeindex->getAttribute("Seq_in_index"));
                    $newindex->setColumn_name($nodetable_nodeindexes_nodeindex->getAttribute("Column_name"));
                    $newindex->setCollation($nodetable_nodeindexes_nodeindex->getAttribute("Collation")); // Collation aqui é A (asc) ou NULL. (não é o caso do char set);
                    $newindex->setSub_part($nodetable_nodeindexes_nodeindex->getAttribute("Sub_part"));
                    $newindex->setPacked($nodetable_nodeindexes_nodeindex->getAttribute("Packed"));
                    $newindex->setNull($nodetable_nodeindexes_nodeindex->getAttribute("Null"));
                    $newindex->setIndex_type($nodetable_nodeindexes_nodeindex->getAttribute("Index_type"));
                    $newindex->setComment($nodetable_nodeindexes_nodeindex->getAttribute("Comment"));

                    $newtable->getIndexes()->addItem($newindex);
                }

                // As FKs só podem existir se existem índices para as tais, por isso a instrução abaixo está contida nesta condição.

                // Estabelecendo as fks da tabela.
                foreach($nodetable->getElementsByTagName("fk") as $nodetable_nodeindexes_nodefk) {
                    $newfk = new FK;

                    // Symbol da fk.
                    $newfk->setSymbol($nodetable_nodeindexes_nodefk->getAttribute("symbol"));

                    // Estabelecendo os índices da fk
                    $nodetable_nodeindexes_nodefk_nodeindexes = $nodetable_nodeindexes_nodefk->getElementsByTagName("indexes")->item(0);

                    foreach($nodetable_nodeindexes_nodefk_nodeindexes->getElementsByTagName("index") as $nodetable_nodeindexes_nodefk_nodeindexes_nodeindex) {

                        // Buscar o índice a que se refere esta fk no nó da tabela atual.
                        $nodetable_nodeindexes = $nodetable->getElementsByTagName("indexes")->item(0);
                        $referenceindex = "";
                        
                        // Percorrendo todos os índices do nó da tabela atual.
                        foreach($nodetable_nodeindexes->getElementsByTagName("index") as $nodetable_nodeindexes_nodeindex) {
                            if($nodetable_nodeindexes_nodeindex->getAttribute("Column_name") == $nodetable_nodeindexes_nodefk_nodeindexes_nodeindex->getAttribute("Column_name")) {
                                $referenceindex = $nodetable_nodeindexes_nodeindex;
                                break;
                            }
                        }

                        // Uma fk sempre está relacionada a um índice existente, então nesse ponto referenceindex deverá ser um objeto da classe Index

                        $newindex = new Index;
                        $newindex->setColumn_name($referenceindex->getAttribute("Column_name"));
                        $newfk->getIndexes()->addItem($newindex);

                    }


                    // A propriedade table do nó references é a tabela a que se relaciona essa fk
                    $nodereferences = $nodetable_nodeindexes_nodefk->getElementsByTagName("references")->item(0);
                    $newfk->getReferences()->setTable($nodereferences->getAttribute("table"));

                    foreach($nodereferences->getElementsByTagName("index") as $nodereference_nodeindex) {
                        // Buscar no xml qual é a tabela com a qual o nó da tabela atual (nodetable) se relaciona
                        foreach($this->xml->getElementsByTagName("table") as $subnodetable) {

                            // Ao encontrar a tabela
                            if($subnodetable->getAttribute("Name") == $nodereferences->getAttribute("table")) {

                                // Percorrendo todos os índices da tabela a que a tabela atual se relaciona
                                $subnodetable_indexes = $subnodetable->getElementsByTagName("indexes")->item(0);

                                foreach($subnodetable_indexes->getElementsByTagName("index") as $referenceindex) {
                                    // Encontrando o índice na tabela a que se relaciona a fk atual.
                                    if($referenceindex->getAttribute("Column_name") == $nodereference_nodeindex->getAttribute("Column_name")) {

                                        /* Ao encontrar o índice, para este foreach e o próximo, fazendo com que a variável referenceindex mantenha
                                           o valor desejado, que é uma instância do índice a que se relaciona a fk atual */
                                        break 2; 
                                    }
                                }

                                /* Se esse loop não foi parado, quer dizer que encontrou a tabela a que se refere o relacionamento,
                                   mas não encontrou o índice. Isso só ocorrerá se houver uma falha no modelo. Nesse caso é necessário
                                   parar esse foreach nesse ponto e fazer com que o foreach continue na verificação da próxima tabela (se
                                   houver). O objeto fk desta tabela ficará incompleto, pois o problema deve ser corrigido. */
                                continue 2;
                            }

                        }

                        // Carregando as propriedades da referência
                        $newindex = new Index;
                        $newindex->setColumn_name($referenceindex->getAttribute("Column_name"));
                        $newfk->getReferences()->addItem($newindex);
                    }

                    $newtable->getFKs()->addItem($newfk);

                }
            }

            $this->getTables()->addItem($newtable);
        }
    }
}

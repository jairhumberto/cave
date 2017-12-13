<?php
/**
*SquilleCave(https://github.com/jairhumberto/Cave)
*
*@copyrightCopyright(c)2018Squille
*@licensethissoftwareisdistributedunderMITlicense,seethe
*LICENSEfile.
*/

namespaceSquille\Cave;

classDatabaseImplementationextendsDatabase
{
protected$connection;

publicfunction__construct(\PDO$connection)
{
$this->connection=$connection;
parent::__construct();

//Estabelecendoaspropriedadesdobanco.
$result=$this->connection->query("SHOWVARIABLESLIKE'character_set_database'");
$this->setCharset($result->fetchObject()->Value);
$result->closeCursor();

$result=$this->connection->query("SHOWVARIABLESLIKE'collation_database'");
$this->setCollation($result->fetchObject()->Value);
$result->closeCursor();

//Estabelecendoastabelasdobanco.
$result=$this->connection->query("SHOWTABLESTATUS");
while($reg=$result->fetchObject()){
$table=newTable;

//Estabelecendoaspropriedadesdatabela.
$table->setName($reg->Name);
$table->setEngine($reg->Engine);
$table->setRow_format($reg->Row_format);
$table->setCollation($reg->Collation);
$table->setChecksum($reg->Checksum);

//Estabelecendooscamposdatabela.
$subresult=$this->connection->query(sprintf("SHOWFULLFIELDSIN%s",$reg->Name));
while($subreg=$subresult->fetchObject()){
$field=newField;

//Estabelecendoaspropriedadesdocampo.
$field->setField($subreg->Field);
$field->setType($subreg->Type);

$charset=explode("_",$subreg->Collation);
$charset=$charset[0];
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

//Estabelecendoosindicesdatabela.
$subresult=$this->connection->query(sprintf("SHOWINDEXESIN%s",$reg->Name));
while($subreg=$subresult->fetchObject()){
$index=newIndex;

//Estabelecendoaspropriedadesdoindice.
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

$table->getIndexes()->addItem($index,$subreg->Column_name);
}
$subresult->closeCursor();

//Estabelecendoasfksdatabela.
$subresult=$this->connection->query(sprintf("SHOWCREATETABLE%s",$reg->Name));
$subreg=$subresult->fetchAll(\PDO::FETCH_NUM);
$subresult->closeCursor();

if(!count($subreg)){
continue;
}

$subreg=$subreg[0];

//Aconsultaretornaocódigodecriaçãodatabelaseparadopornewlines
if(!isset($subreg[1])){
continue;
}

$createtable=$subreg[1];
$lines=explode("\n",$createtable);

//Percorrecadalinhaembuscaderelacionamentosexistentes
foreach($linesas$line){

//VerificandoseemalgumalinhadoCREATETABLEfoidefinidoFK.
if(preg_match('/^\s*CONSTRAINT`.*`FOREIGNKEY\(`[^\)]*`\)REFERENCES/',$line)){
$fk=newFK;

//Estabelecendoaspropriedadesdafk
$symbol=preg_replace('/.*CONSTRAINT`(.+)`FOREIGNKEY.*/','$1',$line);
$fk->setSymbol($symbol);

//Estabelecendoosindicesdafk
$indexes=preg_replace('/.*FOREIGNKEY\(`(.+)`\)REFERENCES.*/','$1',$line);
$indexes=str_replace("`","",$indexes);
$indexes=explode(",",$indexes);

foreach($indexesas$indexname){
$index=newIndex;
$index->setColumn_name($indexname);
$fk->getIndexes()->addItem($index);
}

//Defineasreferenciasdafk
$referencetable=preg_replace('/.*REFERENCES`([^`]+)`.*/','$1',$line);
$fk->getReferences()->setTable($referencetable);//Tabelaaqueserelaciona.

$referenceindexes=preg_replace('/.*REFERENCES`[^`]+`\(`([^\)]+)`\).*/','$1',$line);
$referenceindexes=str_replace("`","",$referenceindexes);
$referenceindexes=explode(",",$referenceindexes);

foreach($referenceindexesas$indexname){
$index=newIndex;
$index->setColumn_name($indexname);
$fk->getReferences()->addItem($index);
}

$table->getFKs()->addItem($fk);
}

}

$this->getTables()->addItem($table,$reg->Name);
}
$result->closeCursor();
}

publicfunctionbackup()
{}/*implementarbackupdepois.backupgeraldobancoatravesdessemétodo*/

publicfunctionintegrity(Model$model)
{
$ul=newUnconformanceList;
$ul->initMessage();//Iniciandoomóduloparamensagens.

//Seosobjetossãoiguaisnãohánecessidadedeverificação.
if($this!=$model){

//Conferecharsetdobanco.
if($this->getCharset()!=$model->getCharset()){
$desc=sprintf('DatabaseCHARSET(%s)differsfromthemodel.',$this->getCharset());
$errorid=$ul->addMessage($desc);

$sqllist=newSQLList;
$sqllist->addItem(newSQL(sprintf("ALTERDATABASECHARACTERSET%s",$model->getCharset())));

$measure=sprintf('ChangingthedatabaseCHARSETto%s',$model->getCharset());

$ul->addItem(newUnconformance($sqllist,$measure));
$ul->addSolution(sprintf("ALTERDATABASECHARACTERSET%s",$model->getCharset()),$errorid);
}

//Conferecollationdobanco.
if($this->getCollation()!=$model->getCollation()){
$desc=sprintf('DatabaseCOLLATION(%s)differsfromthemodel.',$this->getCollation());
$errorid=$ul->addMessage($desc);

$sqllist=newSQLList;
$sqllist->addItem(newSQL(sprintf("ALTERDATABASECOLLATE%s",$model->getCollation())));

$measure=sprintf('ChangingthedatabaseCOLLATIONto%s',$model->getCollation());

$ul->addItem(newUnconformance($sqllist,$measure));
$ul->addSolution(sprintf("ALTERDATABASECOLLATE%s",$model->getCollation()),$errorid);
}

//Conferindoastabelas.
foreach($model->getTables()->getItens()as$modeltable){
foreach($this->getTables()->getItens()as$table){

//Encontrandoatabela.
if($modeltable->getName()==$table->getName()){
//Seatabelaéigualaomodelonãohánecessidadedeverificação.Continuanaproximatabela.
if($modeltable==$table)continue2;

//Confereoengine.
if($table->getEngine()!=$modeltable->getEngine()){
$desc=sprintf('Theengine(%s)ofthetable%sdiffersfromthemodel.',$table->getEngine(),$table->getName());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;

/*Nocasoabaixoénecessáriopercorrerobancoembuscaderelacionamentoscom
essatabelaeexcluí-los,poisoengineMyISAMnãopermiterelacionamentos,
enquantooInnoDBpermite.*/
if($modeltable->getEngine()=="MyISAM"&&$table->getEngine()=="InnoDB"){
/*Percorretodasastabelasdobancoembuscaderelacionamentoscomatabelaquesequer
mudaroengine*/
foreach($this->getTable()->getItens()as$subtable){

//PercorretodasasFKsdatabelacorrenteverificandosealgumaserefereàtabelaatual
foreach($table->getFKs()->getItens()as$fk){
if($fk->getReferences()->getTable()==$table->getName()){
//Seencontrarumareferenciaàtabelaatual,preparaaSQLdeexclusãodareferência.
$sqllist->addItem(newSQL(sprintf(
"ALTERTABLE`%s`DROPFOREIGNKEY`%s`",
$subtable->getName(),
$fk->getSymbol()
)));

$ul->addSolution(sprintf(
"ALTERTABLE`%s`DROPFOREIGNKEY`%s`",
$subtable->getName(),
$fk->getSymbol()
),$errorid);
}
}
}
}

//Prontoparamudaroengine.
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`ENGINE=%s",$table->getName(),$modeltable->getEngine())));

$measure=sprintf('Changingthetableengineofthetable%sto%s',$table->getName(),$modeltable->getEngine());

$ul->addItem(newUnconformance($sqllist,$measure));
$ul->addSolution(sprintf("ALTERTABLE`%s`ENGINE=%s",$table->getName(),$modeltable->getEngine()),$errorid);
}

//ConfereoRow_formatdatabela.
if($table->getRow_format()!=$modeltable->getRow_format()){
$desc=sprintf('Therowformat(%s)ofthetable%sdiffersfromthemodel.',$table->getRow_format(),$table->getName());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`ROW_FORMAT=%s",$table->getName(),$modeltable->getRow_format())));

$measure=sprintf('Changingtherowformatofthetable%sto%s',$table->getName,$modeltable->getRow_format());

$ul->addItem(newUnconformance($sqllist,$measure));
$ul->addSolution(sprintf("ALTERTABLE`%s`ROW_FORMAT=%s",$table->getName(),$modeltable->getRow_format()),$errorid);
}

//ConfereoCollationdatabela.
if($table->getCollation()!=$modeltable->getCollation()){
$desc=sprintf('Thecollation(%s.%s)ofthetable%sdiffersfromthemodel.',$modeltable->getCharset(),$table->getCollation(),$table->getName());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`CHARACTERSET%sCOLLATE%s",$table->getName(),$modeltable->getCharset(),$modeltable->getCollation())));

$measure=sprintf('Changingthecollationofthetable%sto%s.%s',$table->getName(),$modeltable->getCharset(),$modeltable->getCollation());

$ul->addItem(newUnconformance($sqllist,$measure));
$ul->addSolution(sprintf("ALTERTABLE`%s`CHARACTERSET%sCOLLATE%s",$table->getName(),$modeltable->getCharset(),$modeltable->getCollation()),$errorid);
}

//ConfereoChecksumdatabela.
if($table->getChecksum()!=$modeltable->getChecksum()){
$desc=sprintf('Thechecksum(%s)ofthetable%sdiffersfromthemodel.',$table->getChecksum(),$table->getName());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`CHECKSUM=%s",$table->getName(),$modeltable->getChecksum())));

$measure=sprintf('Changingthechecksumofthetable%sto%s',$table->getName(),$modeltable->getChecksum());

$ul->addItem(newUnconformance($sqllist,$measure));
$ul->addSolution(sprintf("ALTERTABLE`%s`CHECKSUM=%s",$table->getName(),$modeltable->getChecksum()),$errorid);
}

//Apósotérminodasverificaçõesdatabela,énecessáriofazerverificaçõesnoscampos.

$i=-1;//Indicadordonúmerodecamposdatabela.

//Verificandoseexistealgumcamponomodeloquenãoestánatabela.
foreach($modeltable->getFields()->getItens()as$modelfield){

$i++;

foreach($table->getFields()->getItens()as$tablefield){
//Aoencontrarocampocomparaaspropriedades.
if($tablefield->getField()==$modelfield->getField()){

//Excluirovalordefaultquandoforocaso.
if(!$modelfield->getDefault()&&$tablefield->getDefault()){
$desc=sprintf('Thefield%s.%sshouldnothaveadefaultvalue."%s"wasfound.',$table->getName(),$tablefield->getField(),$tablefield->getDefault());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`ALTERCOLUMN`%s`DROPDEFAULT",$table->getName(),$tablefield->getField())));
$ul->addSolution(sprintf("ALTERTABLE`%s`ALTERCOLUMN`%s`DROPDEFAULT",$table->getName(),$tablefield->getField()));

$measure=sprintf('Removingthedefaultvalueofthefield%s.%s',$table->getName(),$tablefield->getField());

$ul->addItem(newUnconformance($sqllist,$measure));
}

//Sehouveralgumadiferençanoscampos.
if(
$tablefield->getType()!=$modelfield->getType()||
$tablefield->getCollation()!=$modelfield->getCollation()||
$tablefield->getNull()!=$modelfield->getNull()||
$tablefield->getDefault()!=utf8_decode($modelfield->getDefault())||
$tablefield->getExtra()!=$modelfield->getExtra()||
$tablefield->getComment()!=$modelfield->getComment()
){
$desc=sprintf('Thedeclarationofthefield%s.%sdiffersfromthemodel.',$table->getName(),$tablefield->getField());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;

if(strtoupper($modelfield->getDefault())<>"CURRENT_TIMESTAMP"){
$default="'".$modelfield->getDefault()."'";
}else{
$default=$modelfield->getDefault();
}

if($modelfield->getExtra()=="auto_increment"){
$result=$this->connection->query(sprintf("SELECTMAX(%s)AScntFROM%s",$tablefield->getField(),$table->getName()));
$tablerows=$result->fetchObject();
$auto_increment=$tablerows->cnt+1;
}else{
$auto_increment=1;
}

$sqllist->addItem(newSQL('SETFOREIGN_KEY_CHECKS=0'));
$sqllist->addItem(newSQL(sprintf(
"ALTERTABLE`%s`MODIFYCOLUMN`%s`%s%s%s%s%s%s",
$table->getName(),
$tablefield->getField(),
$modelfield->getType(),
$modelfield->getCollation()?"CHARACTERSET".$modelfield->getCharset()."COLLATE".$modelfield->getCollation():"",
$modelfield->getNull()=="YES"?"NULL":"NOTNULL",
$modelfield->getDefault()!=""?"DEFAULT".utf8_decode($default):"",
$modelfield->getExtra()?"AUTO_INCREMENT,AUTO_INCREMENT=".$auto_increment:"",
$modelfield->getComment()?"COMMENT'".$modelfield->getComment()."'":""
)));
$sqllist->addItem(newSQL('SETFOREIGN_KEY_CHECKS=1'));

$ul->addSolution(sprintf(
"ALTERTABLE`%s`MODIFYCOLUMN`%s`%s%s%s%s%s%s",
$table->getName(),
$tablefield->getField(),
$modelfield->getType(),
$modelfield->getCollation()?"CHARACTERSET".$modelfield->getCharset()."COLLATE".$modelfield->getCollation():"",
$modelfield->getNull()=="YES"?"NULL":"NOTNULL",
$modelfield->getDefault()!=""?"DEFAULT".$default:"",
$modelfield->getExtra()?"AUTO_INCREMENT,AUTO_INCREMENT=".$auto_increment:"",
$modelfield->getComment()?"COMMENT'".$modelfield->getComment()."'":""
),$errorid);

$measure=sprintf('Modifyingthefield%s.%stocomplytothemodel',$table->getName(),$tablefield->getField());

$ul->addItem(newUnconformance($sqllist,$measure));
}

continue2;
}

}

$desc=sprintf('Thefield%s.%swasnotfound.',$modeltable->getName(),$modelfield->getField());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;

//Senãoforoprimeirocampo,adicionaocampoapósocampopassado,senão,adicionaocamponoprimeirolugardatabela.
if($i){
$positionword=sprintf("AFTER`%s`",$modeltable->getFields()->item($i-1)->getField());
}else{
$positionword="FIRST";
}

if(strtoupper($modelfield->getDefault())<>"CURRENT_TIMESTAMP"){
$default="'".$modelfield->getDefault()."'";
}else{
$default=$modelfield->getDefault();
}

$sqllist->addItem(newSQL(sprintf(
"ALTERTABLE`%s`ADDCOLUMN`%s`%s%s%s%s%s%s%s",
$table->getName(),
$modelfield->getField(),
$modelfield->getType(),
$modelfield->getCollation()?"CHARACTERSET".$modelfield->getCharset()."COLLATE".$modelfield->getCollation():"",
$modelfield->getNull()=="YES"?"NULL":"NOTNULL",
$modelfield->getDefault()?"DEFAULT".$default:"",
$modelfield->getExtra()?"AUTO_INCREMENT":"",
$modelfield->getComment()?"COMMENT'".$modelfield->getComment()."'":"",
$positionword
)));
$ul->addSolution(sprintf(
"ALTERTABLE`%s`ADDCOLUMN`%s`%s%s%s%s%s%s%s",
$table->getName(),
$modelfield->getField(),
$modelfield->getType(),
$modelfield->getCollation()?"CHARACTERSET".$modelfield->getCharset()."COLLATE".$modelfield->getCollation():"",
$modelfield->getNull()=="YES"?"NULL":"NOTNULL",
$modelfield->getDefault()?"DEFAULT".$default:"",
$modelfield->getExtra()?"AUTO_INCREMENT":"",
$modelfield->getComment()?"COMMENT'".$modelfield->getComment()."'":"",
$positionword
),$errorid);

$measure=sprintf('Addingthefield%s.%sacordinglytothemodel',$table->getName(),$modelfield->getField());

$ul->addItem(newUnconformance($sqllist,$measure));

if($primeiro)$primeiro=false;
}

//Verificandoseexistealgumcamponatabelaquenãoestánomodelo.
foreach($table->getFields()->getItens()as$tablefield){

foreach($modeltable->getFields()->getItens()as$modelfield){
if($tablefield->getField()==$modelfield->getField()){
continue2;
}
}

$desc=sprintf('Thefield%s.%sshouldnotexist.',$table->getName(),$tablefield->getField());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;

/*Paraexcluirumcampo,énecessáriopercorrertodoobancoembuscaderelacionamentoscomele.
Paraissooforeachabaixopercorretodasastabelasachadasnobanco*/
foreach($this->getTables()->getItens()as$datatable){

/*Aoentraremumatabela,énecessárioverificartodasasFKsexistentesembuscadeumaque
façareferênciacomocampoaserexcluído.*/
foreach($datatable->getFKs()->getItens()as$datatablefk){

//Seencontrarumareferenciaàtabelaondeseencontraocampoquesequerexcluir
if($datatablefk->getReferences()->getTable()==$table->getName()){

/*Entãopercorretodasascolunasembuscadareferencia
aocampoquesequerexcluir*/
foreach($datatablefk->getReferences()->getItens()as$datatablefkreferenceindex){

//Seencontrarareferenciadesejada
if($datatablefkreferenceindex->getColumn_name()==$tablefield->getField()){
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`DROPFOREIGNKEY`%s`",$datatable->getName(),$datatablefk->getSymbol())));
$ul->addSolution(sprintf("ALTERTABLE`%s`DROPFOREIGNKEY`%s`",$datatable->getName(),$datatablefk->getSymbol()),$errorid);
}
}
}
}
}

$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`DROPCOLUMN`%s`",$table->getName(),$tablefield->getField())));
$ul->addSolution(sprintf("ALTERTABLE`%s`DROPCOLUMN`%s`",$table->getName(),$tablefield->getField()),$errorid);

$measure=sprintf('Removingthefield%s.%stocomplytothemodel',$table->getName(),$tablefield->getField());

$ul->addItem(newUnconformance($sqllist,$measure));
}

//Verificaraordemdoscamposdeacordocomaordemdomodelo
foreach($modeltable->getFields()->getItens()as$key=>$modelfield){
if($table->getFields()->item($key)
&&$modelfield
&&$table->getFields()->item($key)->getField()!=$modelfield->getField()
&&$table->getFields()->length()==$modeltable->getFields()->length()){

$desc=sprintf('Thefield%s.%sisnotinthesameorderasinthemodel.',$table->getName(),$table->getFields()->item($key)->getField());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;

if(strtoupper($modelfield->getDefault())<>"CURRENT_TIMESTAMP"){
$default="'".$modelfield->getDefault()."'";
}else{
$default=$modelfield->getDefault();
}

if($key){
$sqllist->addItem(newSQL(sprintf(
"ALTERTABLE`%s`CHANGE`%s``%s`%s%s%s%s%s%sAFTER`%s`",
$modeltable->getName(),
$modelfield->getField(),
$modelfield->getField(),
$modelfield->getType(),
$modelfield->getCollation()?"CHARACTERSET".$modelfield->getCharset()."COLLATE".$modelfield->getCollation():"",
$modelfield->getNull()=="YES"?"NULL":"NOTNULL",
$modelfield->getDefault()?"DEFAULT".$default:"",
$modelfield->getExtra()?"AUTO_INCREMENT":"",
$modelfield->getComment()?"COMMENT'".$modelfield->getComment()."'":"",
$modeltable->getFields()->item($key-1)->getField()
)));
$ul->addSolution(sprintf(
"ALTERTABLE`%s`CHANGE`%s``%s`%s%s%s%s%s%sAFTER`%s`",
$modeltable->getName(),
$modelfield->getField(),
$modelfield->getField(),
$modelfield->getType(),
$modelfield->getCollation()?"CHARACTERSET".$modelfield->getCharset()."COLLATE".$modelfield->getCollation():"",
$modelfield->getNull()=="YES"?"NULL":"NOTNULL",
$modelfield->getDefault()?"DEFAULT".$default:"",
$modelfield->getExtra()?"AUTO_INCREMENT":"",
$modelfield->getComment()?"COMMENT'".$modelfield->getComment()."'":"",
$modeltable->getFields()->item($key-1)->getField()
),$errorid);
}else{
$sqllist->addItem(newSQL(sprintf(
"ALTERTABLE`%s`CHANGE`%s``%s`%s%s%s%s%s%sFIRST",
$modeltable->getName(),
$modelfield->getField(),
$modelfield->getField(),
$modelfield->getType(),
$modelfield->getCollation()?"CHARACTERSET".$modelfield->getCharset()."COLLATE".$modelfield->getCollation():"",
$modelfield->getNull()=="YES"?"NULL":"NOTNULL",
$modelfield->getDefault()?"DEFAULT".$default:"",
$modelfield->getExtra()?"AUTO_INCREMENT":"",
$modelfield->getComment()?"COMMENT'".$modelfield->getComment()."'":""
)));
$ul->addSolution(sprintf(
"ALTERTABLE`%s`CHANGE`%s``%s`%s%s%s%s%s%sFIRST",
$modeltable->getName(),
$modelfield->getField(),
$modelfield->getField(),
$modelfield->getType(),
$modelfield->getCollation()?"CHARACTERSET".$modelfield->getCharset()."COLLATE".$modelfield->getCollation():"",
$modelfield->getNull()=="YES"?"NULL":"NOTNULL",
$modelfield->getDefault()?"DEFAULT".$default:"",
$modelfield->getExtra()?"AUTO_INCREMENT":"",
$modelfield->getComment()?"COMMENT'".$modelfield->getComment()."'":""
),$errorid);
}

$measure=sprintf('Changingtheorderofthefield%s.%stocomplytothemodel',$table->getName(),$modelfield->getField());

$ul->addItem(newUnconformance($sqllist,$measure));
}
}

/*Verificarosíndicesdatabela.Sealgumachaveestivererrada,émaisfacilrecriartodasaschaves.
Issoéverificadoefeitoapósaconferênciadetodasastabelas.
*/
foreach($modeltable->getIndexes()->getItens()as$key=>$modelindex){
if($table->getIndexes()->item($key)!=$modelindex){
$wrongkey=true;
break;
}
}

/*Verificarosrelacionamentosdatabela.Sealgumestivererrado,émaisfacilrecriartodososrelacionamentos.
Issoéverificadoefeitoapósaconferênciadetodasastabelas.
*/
//Verificasetodososrelacionamentosdomodeloestãonobanco.
foreach($modeltable->getFKs()->getItens()as$modelfk){
foreach($table->getFKs()->getItens()as$fk){
if($modelfk==$fk){
continue2;
}
}
/*foreach($table->getFKs()->getItens()as$fk){
printf("model:%s.%s\n",
$modeltable->getName(),$modelfk->getSymbol()
);
printf("imple:%s.%s\n",
$table->getName(),$fk->getSymbol()
);
}*/
$wrongkey=true;
break;
}

//Verificaseapenasosrelacionamentosdomodeloestãonobanco.
foreach($table->getFKs()->getItens()as$fk){
foreach($modeltable->getFKs()->getItens()as$modelfk){
if($modelfk==$fk)continue2;
}
$wrongkey=true;
break;
}

//Aoencontraratabela,nãohánecessidadedeconferiraspróximas.Encerraoloopchamandoapróximatabela(sehouver).
continue2;
}
}

//Atabeladomodelonãoexistenobancoeprecisasercriada.
$desc=sprintf('Thetable%swasnotfound.',$modeltable->getName());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;

$newfields=array();
foreach($modeltable->getFields()->getItens()as$modelfield){

if(strtoupper($modelfield->getDefault())<>"CURRENT_TIMESTAMP"){
$default="'".$modelfield->getDefault()."'";
}else{
$default=$modelfield->getDefault();
}

$newfields[]=sprintf(
"`%s`%s%s%s%s%s",
$modelfield->getField(),
$modelfield->getType(),
$modelfield->getCollation()?"CHARACTERSET".$modelfield->getCharset()."COLLATE".$modelfield->getCollation():"",
$modelfield->getNull()=="YES"?"NULL":"NOTNULL",
$modelfield->getDefault()?"DEFAULT".$default:"",
$modelfield->getComment()?"COMMENT'".$modelfield->getComment()."'":""
);
}

$sqllist->addItem(newSQL(sprintf(
"CREATETABLE`%s`(%s)ENGINE=%s,ROW_FORMAT=%s,CHARACTERSET=%s,COLLATE=%s,CHECKSUM=%d",
$modeltable->getName(),
implode(",",$newfields),
$modeltable->getEngine(),
$modeltable->getRow_format(),
$modeltable->getCharset(),
$modeltable->getCollation(),
$modeltable->getChecksum()
)));

$ul->addSolution(sprintf(
"CREATETABLE`%s`(%s)ENGINE=%s,ROW_FORMAT=%s,CHARACTERSET=%s,COLLATE=%s,CHECKSUM=%d",
$modeltable->getName(),
implode(",",$newfields),
$modeltable->getEngine(),
$modeltable->getRow_format(),
$modeltable->getCharset(),
$modeltable->getCollation(),
$modeltable->getChecksum()
),$errorid);

if($modeltable->getIndexes()->length()){
//Recriarosíndicesseessatabelapossuichave.
$wrongkey=true;
}

$measure=sprintf('Creatingthetable%s',$modeltable->getName());

$ul->addItem(newUnconformance($sqllist,$measure));
}

//Verificandoseastabeladobancoexistemnomodelo.
foreach($this->getTables()->getItens()as$table){
foreach($model->getTables()->getItens()as$modeltable){
if($table->getName()==$modeltable->getName()){
continue2;
}
}

//Preparandoobjetodecorreçãodeproblemas.
$desc=sprintf('Thetable%sshouldnotexist.',$table->getName());
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;

//Paraexcluirumatabelaénecessárioexcluirtodososrelacionamentoscomela.
//Procuraemoutrastabelas,relacionamentoscomestaepreparaparaexcluir.
foreach($this->getTables()->getItens()as$subtable){

$result=$this->connection->query(sprintf("SHOWCREATETABLE%s",$subtable->getName()));
$registro=$result->fetchAll(\PDO::FETCH_NUM);
$result->closeCursor();

if(!count($registro)){
continue;
}

$registro=$registro[0];

//Aconsultaretornaocódigodecriaçãodatabelaseparadopornewlines
if(!isset($registro[1])){
continue;
}

//Obtemocódigodecriaçãodatabela.
$createtable=$registro[1];

//Separaocódigoporlinhasparaverificarsehárelacionamentos.
$eachline=explode("\n",$createtable);

foreach($eachlineas$line){
//Encontrandoumrelacionamentocomatabelaaserexcluida
if(preg_match(sprintf('/^\s*CONSTRAINT`[^`]*`FOREIGNKEY\(`[^\)]*`\)REFERENCES`%s`\(`[^\)]*`\)/',$table->getName()),$line)){
$fksymbol=preg_replace('/.*CONSTRAINT`(.+)`FOREIGNKEY.*/','$1',$line);

//Aoencontrar,criaaSQLdeexclusãodorelacionamento.
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`DROPFOREIGNKEY`%s`",$subtable->getName(),$fksymbol)));
$ul->addSolution(sprintf("ALTERTABLE`%s`DROPFOREIGNKEY`%s`",$subtable->getName(),$fksymbol),$errorid);
}
}
}

//Apósverificarporrelacionamentosemtodoobanco,criaaSQLdeexclusãodatabela.
$sqllist->addItem(newSQL(sprintf("DROPTABLE`%s`",$table->getName())));
$ul->addSolution(sprintf("DROPTABLE`%s`",$table->getName()),$errorid);

$measure=sprintf('Removingthetable%stocomplytothemodel',$table->getName());

//Adicionadoàlistadeinformidadesaseremcorrigidas.
$ul->addItem(newUnconformance($sqllist,$measure));
}

//Wrongkeyétruequandoalgumachaveemalgumatabelaapresentouerroouumanovatabelacomchavesfoicriada.
//Recriatodasaschavesdobanco.
if(isset($wrongkey)&&$wrongkey){
$desc='Indexorrelationshipproblems';
$errorid=$ul->addMessage($desc);
$sqllist=newSQLList;

//Excluitodosaschavesestrangeirasdecadatabeladobanco.
foreach($this->getTables()->getItens()as$table){
foreach($table->getFKs()->getItens()as$fk){
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`DROPFOREIGNKEY`%s`",$table->getName(),$fk->getSymbol())));
$ul->addSolution(sprintf("ALTERTABLE`%s`DROPFOREIGNKEY`%s`",$table->getName(),$fk->getSymbol()),$errorid);
}
}

//Excluirtodososíndicesdecadatabeladobanco.
foreach($this->getTables()->getItens()as$table){
foreach($table->getIndexes()->getItens()as$index){
if($index->getKey_name()=="PRIMARY"){
foreach($table->getFields()->getItens()as$field){
//Removeautoincrement,paranãodarproblemanaexclusãodachave.
if($field->getExtra()){
$sqllist->addItem(newSQL(sprintf(
"ALTERTABLE`%s`MODIFYCOLUMN`%s`%s%s%s%s%s",
$table->getName(),
$field->getField(),
$field->getType(),
$field->getCollation()?"CHARACTERSET".$field->getCharset()."COLLATE".$field->getCollation():"",
$field->getNull()=="YES"?"NULL":"NOTNULL",
$field->getDefault()?"DEFAULT".$field->getDefault():"",
$field->getComment()?"COMMENT'".$field->getComment()."'":""
)));
$ul->addSolution(sprintf(
"ALTERTABLE`%s`MODIFYCOLUMN`%s`%s%s%s%s%s",
$table->getName(),
$field->getField(),
$field->getType(),
$field->getCollation()?"CHARACTERSET".$field->getCharset()."COLLATE".$field->getCollation():"",
$field->getNull()=="YES"?"NULL":"NOTNULL",
$field->getDefault()?"DEFAULT".$field->getDefault():"",
$field->getComment()?"COMMENT'".$field->getComment()."'":""
),$errorid);
}
}
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`DROPPRIMARYKEY",$table->getName())));
$ul->addSolution(sprintf("ALTERTABLE`%s`DROPPRIMARYKEY",$table->getName()),$errorid);
}else{
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`DROPINDEX`%s`",$table->getName(),$index->getKey_name())));
$ul->addSolution(sprintf("ALTERTABLE`%s`DROPINDEX`%s`",$table->getName(),$index->getKey_name()),$errorid);
}
}
}

//Criatodososíndicesemtodasastabelasdeacordocomomodelo.
foreach($model->getTables()->getItens()as$table){

$indexList=array();
foreach($table->getIndexes()->getItens()as$index){
$indexList[$index->getKey_name()][]=$index->getColumn_name();
}

foreach($indexListas$indexname=>$makeindex){
if($indexname=="PRIMARY"){
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`ADDPRIMARYKEY(`%s`)",$table->getName(),implode("`,`",$makeindex))));
$ul->addSolution(sprintf("ALTERTABLE`%s`ADDPRIMARYKEY(`%s`)",$table->getName(),implode("`,`",$makeindex)),$errorid);
}else{
$sqllist->addItem(newSQL(sprintf("ALTERTABLE`%s`ADDKEY`%s`(`%s`)",$table->getName(),$indexname,implode("`,`",$makeindex))));
$ul->addSolution(sprintf("ALTERTABLE`%s`ADDKEY`%s`(`%s`)",$table->getName(),$indexname,implode("`,`",$makeindex)),$errorid);
}
}
}

//Criatodososrelacionamentosemtodasastabelasdeacordocomomodelo.
foreach($model->getTables()->getItens()as$table){
foreach($table->getFKs()->getItens()as$fk){
$sqllist->addItem(newSQL(sprintf(
"ALTERTABLE`%s`ADDCONSTRAINT`%s`FOREIGNKEY(`%s`)REFERENCES`%s`(`%s`)",
$table->getName(),
$fk->getSymbol(),
$fk->getIndexes()->join("`,`"),
$fk->getReferences()->getTable(),
$fk->getReferences()->join("`,`")
)));
$ul->addSolution(sprintf(
"ALTERTABLE`%s`ADDCONSTRAINT`%s`FOREIGNKEY(`%s`)REFERENCES`%s`(`%s`)",
$table->getName(),
$fk->getSymbol(),
$fk->getIndexes()->join("`,`"),
$fk->getReferences()->getTable(),
$fk->getReferences()->join("`,`")
),$errorid);
}
}

$measure='Lininguptherelationshipsandindexes';

$ul->addItem(newUnconformance($sqllist,$measure));
}

}
return$ul;
}
}

<?php

//require_once "dbtables.php";

class generic_db{

    private $upsertForbiddenFields = array("inserted_at", "inserted_by","updated_at","updated_by");
    private $db_connection;
    private $config;

    private $escapeCharacter="\"";

    public function getDBConnection(){
        return $this->db_connection;
    }

    private function connectToDB(){
        // uses PDO
        
//        print_r($this->config);

        if($this->config['db_type'] == "pgsql"){
            $this->escapeCharacter="\"";
        }else if($this->config['db_type'] = "mysql"){
            $this->escapeCharacter="`";
        }else{
            $this->escapeCharacter="\"";
        }

        $dsn=$this->config['db_type'].":host=".$this->config['db_host'].";port=".$this->config['db_port'].";dbname=".$this->config['db_databasename'].";";
        $this->db_connection = new PDO($dsn,$this->config['db_user'],$this->config['db_pass'],[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

//        if($this->config->db_type == "pgsql"){
//            $this->db_connection = pg_connect("host=".$this->config->db_host." port=".$this->config->db_port." dbname=".$this->config->db_databasename." user=".$this->config->db_user." password=".$this->config->db_pass." options='--client_encoding=UTF8'");
//            if($this->db_connection == false){
//                echo "error building connection";
//                exit(1);
//            }
//        }
    }

    public function __construct($dbconfig)
    {
        $this->config = $dbconfig;

//        print_r($this->config);
//        exit();
        // init db connection
        // we use postgres -> in config.php
        $this->connectToDB();
    }

    public function getObjectFromDbTableById($row_id, $tablename){
        if(!is_subclass_of($tablename, "dbtables")){
            new Error("wrong class type for db");
        }
        $actual_table_name = $tablename::__tableName;
        $sql = "SELECT $actual_table_name.* FROM $actual_table_name WHERE $actual_table_name.".$tablename::__id_field." = :".$tablename::__id_field;
        $stmt = $this->db_connection->prepare($sql);
        $stmt->bindValue(':id',$row_id);//fixme id field
        $stmt->execute();
        return $stmt->fetchObject($tablename);
    }

    public function updateRow($row_object){

        $dateNOW = DateTime::createFromFormat('U.u', microtime(TRUE))->format('Y-m-d H:i:s.u');

        $class = get_class($row_object);
        if(!is_subclass_of($class, "dbtables")){
            new Error("wrong class type for db");
        }
//        echo "update record<br>";
        // als we er van uitgaan dat 'id' de primary key is, en al de rest dynamisch kan
        $row_objectArr = get_object_vars($row_object);
//        print_r($row_objectArr);
//        print_r($row_object);

        $fieldsarray = array();

        $sql = "UPDATE ".$row_object::__tableName." SET ";
        foreach ($row_objectArr as $key => $value){
            if(! in_array($key, $this->upsertForbiddenFields)){
                if(empty($row_object::__allowed_fields)){
                    $sql.= "$this->escapeCharacter$key$this->escapeCharacter=:".$key.","; // FIXME double quotes
                    $fieldsarray[$key]=$value;
                } else {
                    if (in_array($key, $row_object::__allowed_fields)){
                        $sql.= "$this->escapeCharacter$key$this->escapeCharacter=:".$key.",";
                        $fieldsarray[$key]=$value;
                    }
                }
            }
        }
//        unset($fieldsarray[$row_object::__id_field]);
        if($row_object::__timestamps){
            $sql.= " $this->escapeCharacter"."updated_at$this->escapeCharacter=:updated_at,";
            $fieldsarray['updated_at']=$dateNOW;
        }

        $sql = substr($sql,0,-1); // remove trailing ','
        $sql .= " WHERE $this->escapeCharacter".$row_object::__id_field."$this->escapeCharacter = :".$row_object::__id_field;

        $stmt = $this->db_connection->prepare($sql);
        // execute sql
//        print_r($fieldsarray);
        $stmt->execute($fieldsarray); //FIXME
        $idfieldname=$row_object::__id_field;
        $id = $row_object->$idfieldname;
        if(empty($id)) {
            $id = $stmt->fetchAll();
        }
        return $this->getObjectFromDbTableById($id,$class);//fixme id field
    }

    public function insertRow($row_object){

        $dateNOW = DateTime::createFromFormat('U.u', microtime(TRUE))->format('Y-m-d H:i:s.u');

        $class = get_class($row_object);
        if(!is_subclass_of($class, "dbtables")){
            new Error("wrong class type for db");
        }
//        echo "insert<br>";
        // als we er van uitgaan dat 'id' de primary key is, en al de rest dynamisch kan
        $row_objectArr = get_object_vars($row_object);
//        print_r($row_objectArr);
//        print_r($row_object);

        $fieldsarray = array();
        if(empty($row_object->id)){ //fixme id field
            $fieldsstring = "";
            $valuesstring = "";
            // INSERT
            // insert into <table> (fields) values (<values>
            foreach ($row_objectArr as $key => $value){
                if(! in_array($key, $this->upsertForbiddenFields)){
                    $fieldsstring .= " $this->escapeCharacter".$key."$this->escapeCharacter,";
                    $valuesstring .= " :".$key.",";
                    $fieldsarray[$key] = $value;
                }
            }
            // TODO add inserted_at & inserted_by
            if($row_object::__timestamps){
                unset($fieldsarray['inserted_at']);
                unset($fieldsarray['updated_at']);
                $fieldsstring .= " inserted_at,";
                $valuesstring .= " :inserted_at,";
            }
            // FIXME field & value als parameters doen KAN NIET, we moeten direct inserten -> hoe evt sanitizen? object dat binnenkomt kan 'vreemd' zijn
            $fieldsstring = substr($fieldsstring, 0,-1); // remove trailing ','
            $valuesstring = substr($valuesstring,0,-1); // remove trailing ','
            $sql = "INSERT INTO ".$row_object::__tableName." (".$fieldsstring." ) VALUES (".$valuesstring." ) RETURNING id";//fixme id field

//            echo $sql."<br>";
        }


        $stmt = $this->db_connection->prepare($sql);
        // bind ALL vars to template
//        print_r($row_objectArr);

        if($row_object::__timestamps) {
            $fieldsarray['inserted_at'] =  $dateNOW;
        }


        // execute sql
        $stmt->execute($fieldsarray);
        if(isset($row_object->id)) {//fixme id field
            $id = $row_object->id;//fixme id field
        }
        else{
            $id = $stmt->fetchAll();
//            print_r($id);
        }
        return $this->getObjectFromDbTableById($id[0]['id'],$class);//fixme id field
    }
    
    /**
     * @param $row_object
     * $row_obejct heeft een ingevulde id-parameter om een update te doen, deze parameter hoort leeg te zijn voor een insert te doen
     * @return row-object met ingevulde id-parameter (in geval van insert)
     */
    public function upsertRow($row_object){
        $class = get_class($row_object);
        if(!is_subclass_of($class, "dbtables")){
            new Error("wrong class type for db");
        }
//        echo "upsert<br>";
        // als we er van uitgaan dat 'id' de primary key is, en al de rest dynamisch kan
        $row_objectArr = get_object_vars($row_object);
//        print_r($row_objectArr);
//        print_r($row_object);

        if(empty($row_object->id)){
            $result = $this->insertRow($row_object);
        }else{
            $result = $this->updateRow($row_object);
        }
        return $result;
    }

    public function deleteFromDbById($row_id,$tablename){
        if(!is_subclass_of($tablename, "dbtables")){
            new Error("wrong class type for db");
        }
        $sql = "DELETE FROM :tablename WHERE id = :id";//fixme id field
        $stmt = $this->db_connection->prepare($sql);
        $stmt->bindValue(':id',$row_id);//fixme id field
        $stmt->bindValue(':tablename',$tablename);
//        $stmt->execute(); //FIXME
    }
    
    /**
     * returns by default an array of arrays with both integer and column-name based indexes (data transfer per row doubles)
    */
    public function executeSqlQuery($query, $mode= PDO::FETCH_DEFAULT){
        // $sql = 'SELECT name, color, calories FROM fruit ORDER BY name';
        return $this->db_connection->query($query,$mode);
    }

}

<?php

abstract class dbtables
{
    public const __tableName = "bloep";
    public const __allowed_fields = array();
    public const __id_field = "id"; // fixme niet overal doorgevoerd
    public const __timestamps = false;
    public const __readonly = false;
    
    private $dbConnection = null;
    private $queryOrderBy = array();
    private $queryWhere = array();

    public function __construct($dbConnection){
        $this->dbConnection = $dbConnection;
    }
    
    public function orderBy($orderClause){
        $this->queryOrderBy[] = $orderClause;
        return $this;
    }
    
    public function where($whereClause){
        $this->whereClause[] = $whereClause;
        return $this;
    }
    
    public function resetQueryParams(){
        $this->queryOrderBy[] = array();
        $this->whereClause[] = array();
    }
    
    public function buildQueryString(){

        $tableName = get_class($this)::__tableName;
        $result = "SELECT $tableName.* FROM $tableName ";
        if(! empty($this->queryWhere)){
                $result .= " WHERE ";
            foreach($this->queryWhere as $where){
                $result .= $where ." AND ";
            }
            $result = substr($result,0,-5);
        }
        if(! empty($this->queryOrderBy)){
                $result .= " ORDER BY ";
            foreach($this->queryOrderBy as $order){
                $result .= $order .", ";
            }
            $result = substr($result,0,-2);
        }
        return $result;
    }
    
    public function get($limit = 50, $offset = null){
        $result = array();
        $limitString = "";
        if($limit != null) {
            $limitString .=" LIMIT $limit";
        }
        if($offset != null){
            $limitString .=" OFFSET $offset";
        }
        $queryString =$this->buildQueryString().$limitString;
        // echo $queryString;
        // echo "<br>\n";

        $this->resetQueryParams();
        
        foreach($this->dbConnection->executeSqlQuery($queryString) as $row){
            $result[] = $row;
        }
        
        return $result;
        
    }
    
    public function first(){
        return $this->get(1);
    }
}

class demo_table extends dbtables{

    public const __tableName = "demo_database.demo_table";
    public const __allowed_fields = array();
    public const __timestamps = false;
    
    public function __construct($db=null) {
        if($db != null){
            parent::__construct($db);
        }
    }
    
}

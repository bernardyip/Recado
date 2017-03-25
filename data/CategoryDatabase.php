<?php
include_once '/data/Database.php';
include_once '/model/Category.php';

class CategoryDatabaseResult extends DatabaseResult {
    const CATEGORY_FIND_SUCCESS = 10;
    const CATEGORY_FIND_FAIL = 19;
    const CATEGORY_CREATE_SUCCESS = 11;
    const CATEGORY_CREATE_FAIL = 18;
    const CATEGORY_UPDATE_SUCCESS = 12;
    const CATEGORY_UPDATE_FAIL = 17;
    const CATEGORY_DELETE_SUCCESS = 13;
    const CATEGORY_DELETE_FAIL = 16;
    
    public $categories;
    public $count;
    
    public function __construct($status, $categories, $count) {
        $this->status = $status;
        $this->categories = $categories;
        $this->count = $count;
    }
}


class CategoryDatabase extends Database {
    
    // SQL Queries
    const SQL_FIND_CATEGORY_WITH_LIMIT = "SELECT * FROM public.category c LIMIT $1;";
    const SQL_FIND_CATEGORY = "SELECT * FROM public.category c;";
    const SQL_FIND_CATEGORY_COUNT = "SELECT COUNT(*) AS count FROM public.category c;";
    
    public function  __construct() {
        parent::__construct();
        pg_prepare ( $this->dbcon, 'SQL_FIND_CATEGORY_WITH_LIMIT', CategoryDatabase::SQL_FIND_CATEGORY_WITH_LIMIT );
        pg_prepare ( $this->dbcon, 'SQL_FIND_CATEGORY', CategoryDatabase::SQL_FIND_CATEGORY );
        pg_prepare ( $this->dbcon, 'SQL_FIND_CATEGORY_COUNT', CategoryDatabase::SQL_FIND_CATEGORY_COUNT );
    }
    
    public function findCategoriesLimitTo($count = 0) {
        
        $dbResult = null;
        if ($count > 0) {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_CATEGORY_WITH_LIMIT', array (
                    $count
            ) );
        } else {
            $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_CATEGORY', array (
            ) );
        }

        $categories = array();
        $nrRows = 0;
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $nrRows = pg_affected_rows ( $dbResult );
            for ($i = 0; $i < $nrRows; $i++) {
                $category = pg_fetch_array( $dbResult );
                $categories[$i] = new Category($category['id'], $category['photo'], $category['name'], 
                        $category['description']);
            }
        }
        
        return new CategoryDatabaseResult(CategoryDatabaseResult::CATEGORY_FIND_SUCCESS, $categories, $nrRows);
    }
    
    public function findCategoryCount() {
        $dbResult = pg_execute ( $this->dbcon, 'SQL_FIND_CATEGORY_COUNT', array (
        ) );

        $count = 0;
        if (pg_affected_rows ( $dbResult ) >= 1) {
            $result = pg_fetch_array( $dbResult );
            $count = $result['count'];
        }
        
        return new CategoryDatabaseResult(CategoryDatabaseResult::CATEGORY_FIND_SUCCESS, array(), $count);
    }
}

?>
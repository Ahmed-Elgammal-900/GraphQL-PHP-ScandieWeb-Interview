<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

class Config {
    const DB_HOST = 'ecommerce-scandiweb-ecommerce-database.c.aivencloud.com';
    const DB_NAME = 'defaultdb';
    const DB_USER = 'avnadmin';
    const DB_PASS = 'AVNS_n3L7nRXXuFKhnaQ1Qk4';
    const DB_PORT = 28703;
    const ssl_CA = '../ca.pem';
}

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connection = new mysqli(Config::DB_HOST, Config::DB_USER, Config::DB_PASS, Config::DB_NAME, Config::DB_PORT);
        $this->connection->ssl_set(null, null, Config::ssl_CA, null, null);
        $this->connection->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
        $this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1000);

        if (!$this->connection->real_connect(
            Config::DB_HOST,
            Config::DB_USER,
            Config::DB_PASS,
            Config::DB_NAME,
            Config::DB_PORT,
            null,
            MYSQLI_CLIENT_SSL
        )) {
            die("Connection failed with SSL: " . $this->connection->connect_error);
        }

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        $result = $this->connection->query($sql);

        if (!$result) {
            die("Query failed. Error ({$this->connection->errno}): {$this->connection->error}");
        }

        return $result;
    }

}

class contextData{
    public $type;
}

abstract class CategoryModel{
    protected $db;
    protected $type;

    public function __construct($type1 = null, $type2 = null){
        $this->db = Database::getInstance();
        $this->type = $type1 ?? $type2;
    }

    abstract function getType();

}

abstract class ProductsModel{
    protected $db;
    protected $id;

    public function __construct($id = null){
        $this->db = Database::getInstance();
        $this->id = $id;
    }

    abstract function getByType($type);
    abstract function getByID();
    abstract function getGallery();
    abstract function getCurrency();
    abstract function getPrice();

}

abstract class AttributesModel{
    protected $db;
    protected $id;
    protected $type;

    public function __construct($id, $type = null){
        $this->db = Database::getInstance();
        $this->id = $id;
        $this->type = $type;
    }

    abstract function getAttribute();
    abstract function getAllItems();
}


class getCategroy extends CategoryModel{
    function getType(){
        $sql = "SELECT * FROM category where name ='{$this->type}' or '{$this->type}' = 'all' or '{$this->type}' = '' ";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}


class getProduct extends ProductsModel{
    function getByType($type){
        $type = strtolower($type);
        $sql = "SELECT * FROM products where category ='{$type}' or '{$type}' = 'all' ORDER BY category";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getByID(){
        $sql = "SELECT * FROM products WHERE id='{$this->id}'";
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    public function getGallery(){
        $sql = "SELECT gallery FROM gallery WHERE id ='{$this->id}'";
        $result = $this->db->query($sql);
        $all = $result->fetch_all(MYSQLI_ASSOC);
        $images = array();
        for ($i=0; $i < count($all) ; $i++){
            $images[] = $all[$i]['gallery'];
        }
        return $images;
    }

    public function getCurrency(){
        $sql = "SELECT label, sympol FROM products WHERE id = '{$this->id}'";
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }

    public function getPrice(){
        $sql = "SELECT id, amount FROM products WHERE id = '{$this->id}'";
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }
}

class getAtrributes extends AttributesModel{

    public function getAttribute(){
        $sql = "SELECT productid, type as id, type as name, role as type FROM productsattr WHERE productid = '{$this->id}' GROUP BY type, role";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllItems(){
        $sql = "SELECT displayValue, value,  id FROM productsattr WHERE productid ='{$this->id}' AND type = '{$this->type}'";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

class createOrders{
    protected $db;

    public function __construct(){
        $this->db = Database::getInstance();
    }

    public function create($items){
       for ($i=0; $i < count($items['items']) ; $i++) { 
            $type = $items['items'][$i]['type'];
            unset($items['items'][$i]['type']);
            $keys = array_keys($items['items'][$i]);
            $values = array_values($items['items'][$i]);
            $keys = implode(', ', $keys);
            $values = implode(', ', $values);
            $sql = "INSERT INTO ". $type ."orders ({$keys}) VALUES ({$values})";
            $result = $this->db->query($sql);
        }

        return 'Your Orders Have been received';
    }

}
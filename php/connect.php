<?php
class DB {
    private $conn;
    public function __construct($h="localhost",$u="root",$p="",$d="primeri",$port=3308) {
        $this->conn = new mysqli($h,$u,$p,$d,$port);
        if ($this->conn->connect_error) die("DB Error: ".$this->conn->connect_error);
    }
    function get() { return $this->conn; }
    function q($sql) { return $this->conn->query($sql); }
    function p($sql) { return $this->conn->prepare($sql); }
    function __destruct() { $this->conn->close(); }
}
$db = new DB();
$conn = $db->get();
?>


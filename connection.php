<?php

class Connection
{
    private $dbHost = "localhost";
    private $dbUsername = "root";
    private $dbPassword = "";
    private $dbName = "products";
    private $query = "";

    /*const DB_HOST = "localhost";
    const DB_USER = "root";
    const DB_PASS = "";
    const DB_BASE = "products";*/

    public function __construct()
    {
        if (!isset($this->db)) {
            // Connect to the database
            $conn = new mysqli($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
            if ($conn->connect_error) {
                die("Failed to connect with MySQL: " . $conn->connect_error);
            } else {
                $this->db = $conn;
            }
        }
        echo "Connected successfully";
    }

    public function getResult($conn, $barodes)
    {
        $sql = "SELECT `uuid` ,`name` FROM ms_products WHERE `barcodes`='$barodes'";

        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "id: " . $row["uuid"] . " - Name: " . $row["name"] . " " . $row["barcodes"] . "<br>";
            }
        } else {
            echo "0 results";
        }
        $conn->close();
    }

    /*function dbQuery($query = '')
    {
        $link = mysqli_connect(DB_HOST, DB_USER, DB_PASS) or die("Couldn't connect to the MySQL server\n");
        mysqli_query($link, 'SET NAMES utf8') or die("Invalid set utf8 " . mysqli_error($link) . "\n");
        $db = mysqli_select_db($link, DB_BASE) or die("db can't be selected\n");

        $result = mysqli_query($link, $query) or die("Query error: " . mysqli_error($link) . '[' . $query . ']' . "\n");
        mysqli_close($link);
        return $result;
    }

    function dbQueryArray($query = '')
    {
        $result = dbQuery($query);
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_free_result($result);

        return $data;
    }*/
}
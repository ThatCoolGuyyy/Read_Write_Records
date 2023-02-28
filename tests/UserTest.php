<?php

require_once '/Users/mac/Desktop/Projects/Read-Write-System/run.php';

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {

    public function testAggregate() {
        $aggregator = new AggregateData();
        $merged_data = $aggregator->aggregate();
        $this->assertNotEmpty($merged_data);
    }

    public function testSaveToDatabase($merged_data) {
        
        $database = new PDO('sqlite::memory:');
        $database->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL,
            name TEXT NOT NULL,
            country TEXT
        )');
         foreach($merged_data as $data) {
            
            if (!isset($data['name']) || !isset($data['location'])) {
                continue;
            }
            $sql = "INSERT INTO users ( email, name, country) VALUES ( :email, :name, :country)";
            $stmt = $database->prepare($sql);
            $email = $data['email'] ?? '';
            $name = ($data['name']['first'] ?? '') . ' ' . ($data['name']['last'] ?? '');
            $country = $data['location']['country'] ?? '';
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':country', $country);
            $stmt->execute();
        }
        $aggregator = new AggregateData();
        $merged_data = $aggregator->aggregate();
        $aggregator->saveToDatabase($merged_data);
        $stmt = $database->prepare('SELECT COUNT(*) FROM users');
        $stmt->execute();
        $result = $stmt->fetchColumn();
        $this->assertEquals(count($merged_data), $result);
    }

}

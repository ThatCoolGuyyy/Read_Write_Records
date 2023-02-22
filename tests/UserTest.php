<?php

require_once '/Users/mac/Desktop/Projects/Read-Write-System/run.php';
// require_once __DIR__ . '/vendor/autoload.php';
// use Symfony\Component\Dotenv\Dotenv;
// $dotenv = new Dotenv();
// $dotenv->load(__DIR__.'/env.env');
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {

    use ReadDataTrait;

    public function testReadCsv()
    {
        
        $csv_data = $this->readCsv($_ENV['CSV_PATH']);
        $csv_data;
        // $this->assertEquals(['id' => '200189617246', 'gender' => 'male'], $csv_data);
        $this->assertIsArray($csv_data);
        $this->assertArrayHasKey('id', $csv_data[0]);
    }
    
    public function testReadNetwork()
    {
        $network_data = $this->readNetwork($_ENV['USER_URL']);
        $this->assertIsArray($network_data);
    }

}

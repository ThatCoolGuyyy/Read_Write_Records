<?php
declare(strict_types=1);

const USER_URL = 'https://randomuser.me/api/?inc=gender,name,email,location&results=5&seed=a9b25cd955e2035f';

trait ReadDataTrait {

    public function readNetwork($url) {
        $data = file_get_contents($url);
        return json_decode($data, true)['results'];
    }

    public function readCsv($dir) {
        $csv_data = [];
        if (($handle = fopen($dir, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $csv_data[] = $data;
            }
            fclose($handle);
        }
        array_shift($csv_data); // remove header row
        return $csv_data;
    }
}

class AggregateData {

    use ReadDataTrait;

    public function aggregate() {
        $csv_data = $this->readCsv(__DIR__.'/data/users.csv');
        $network_data = $this->readNetwork(USER_URL);
        $merged_data = array_merge($csv_data, $network_data);
        return $merged_data;
    }

    public function saveToDatabase($merged_data) {
        try {
            $database = new PDO('sqlite:' . __DIR__ . '/database/test.db');
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        $database->exec('CREATE TABLE IF NOT EXISTS users (
            id TEXT PRIMARY KEY AUTOINCREMENT,
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
            // Changed bindParam to bindValue to avoid passing variables by reference
            // $stmt->bindValue(':id', $email);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':country', $country);
            $stmt->execute();
        }
        $database = null;
        echo 'users imported'. PHP_EOL;
    }
}

$aggregator = new AggregateData();
$merged_data = $aggregator->aggregate();
$aggregator->saveToDatabase($merged_data);

        
    // function read_csv()
    // {
        // $getcurrentworkingDirectory = getcwd();
        // $csv_provider = array_map('str_getcsv', file($getcurrentworkingDirectory . '/data/users.csv'));
        // $csvProviders = []; // possible fix
        // array_walk($csvProviders, function (&$a) use ($csv_provider) {
        //     $a = array_combine($csv_provider[0], $a);
        // });
        // array_shift($csv_provider); # Remove header column
        // return $csv_provider;
    // }
    // $csv_provider = array_map('str_getcsv', file($getcurrentworkingDirectory . '/data/users.csv'));
    // $csvProviders = []; // possible fix
    // array_walk($csvProviders, function (&$a) use ($csv_provider) {
    //     $a = array_combine($csv_provider[0], $a);
    // });
    // array_shift($csv_provider); # Remove header column

    // function read_network()
    // {
        // $getcurrentworkingDirectory = getcwd();
        // $url = USER_URL;
        // $web_provider = json_decode(file_get_contents($getcurrentworkingDirectory . '/data/network.json'))->results;
        // $pr = []; // possible fix 
        // array_walk($pr, function (&$a) use ($web_provider) {
        //     $a = array_combine($web_provider[0], $a);
        // }); // possible fix
        
    // }
    // $url = USER_URL;
    // $web_provider = json_decode(file_get_contents($getcurrentworkingDirectory . '/data/network.json'))->results;
    // $pr = []; // possible fix 
    // array_walk($pr, function (&$a) use ($web_provider) {
    //     $a = array_combine($web_provider[0], $a);
    // }); // possible fix

    
    // $providers = array_merge($csv_provider, $b); # merge arrays
    // $database = new SQLite3($getcurrentworkingDirectory . '/database/users.dump');
    // $database->exec('CREATE TABLE IF NOT EXISTS users (
    //     id   INTEGER PRIMARY KEY,
    //     email TEXT    NOT NULL,
    //     name TEXT NOT NULL,
    //     country TEXT)
    // ');

    // foreach ($providers as $user) {
    //     $result = $database->query('SELECT count(*) FROM users WHERE id = ' . $user[0]);
    //     $num = $result->fetchArray(SQLITE3_NUM);
    //     if ($num[0] === 0) {
    //         $email = $user[5];
    //         $name = $user[2];
    //         $id = $user[0];
    //         $country = $user[3];
    //         $database->exec("INSERT INTO users VALUES($id,'$email','$name','$country')");
    //     }
    // }
// try{
//     $database = new PDO('sqlite3:/database/test.db');
//     } catch (PDOException $e) {
//         echo $e->getMessage();
// }
    
//     $database->exec('CREATE TABLE IF NOT EXISTS users (
//         id   INTEGER PRIMARY KEY,
//         email TEXT    NOT NULL,
//         name TEXT NOT NULL,
//         country TEXT)
//     ');
//     foreach($providers as $data){
//         $sql = "INSERT INTO users (id, email, name, country) VALUES (:id, :email, :name, :country)";
//         $stmt = $database->prepare($sql);
//         $stmt->bindParam(':id', $data['name']);
//         $stmt->bindParam(':email', $data['email']);
//         $stmt->bindParam(':country', $data['country']);
//         $stmt->execute();
//     }
//     $pdo = null;
//     echo 'Users imported!' . PHP_EOL;
// ?>



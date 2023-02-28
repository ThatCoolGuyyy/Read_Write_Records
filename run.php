<?php
require_once __DIR__ . '/vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/env.env');

trait ReadDataTrait {

    public function readCsv($dir) {
        $csv_data = [];
        if (($handle = fopen($dir, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $csv_data[] = array_combine($header, $data);
            }
            fclose($handle);
        }
        return $csv_data;
    }

    public function readNetwork($url) {
        try{
            $data = file_get_contents($url);
            $network_data = json_decode($data, true)['results'];
        } catch (Exception $e) {
            echo $e->getMessage();
        }
       
        $network_data = array_map(function($provider) {
            
            return [
                'id' => uniqid(),
                'gender' => $provider['gender'],
                'name' => ($provider['name']['first']) . ' ' . ($provider['name']['last']),
                'country' => $provider['location']['country'],
                'postcode' => $provider['location']['postcode'],
                'email' => $provider['email'],
                'birthday' => (new Datetime('now'))->format('Y-m-d\TH:i:s.u\Z'),
            ];
        }, $network_data);
        return $network_data;
    }
}
class AggregateData {

    use ReadDataTrait;

    public function aggregate() {
        $csv_data = $this->readCsv(__DIR__.'/data/users.csv');
        $network_data = $this->readNetwork($_ENV['USER_URL']);
        return $merged_data = array_merge($csv_data, $network_data);
    }

    public function saveToDatabase($merged_data) {
        $dsn = '';
        $username = '';
        $password = '';

        $db_type =$_ENV["DB_TYPE"];
        switch ($db_type) {
            case 'sqlite':
                $dsn = "sqlite:{$_ENV["DB_PATH"]}";
                $username = " ";
                $password = " ";
                break;
            case 'mysql':
                $dsn = "mysql:host={$_ENV("DB_HOST")};dbname={$_ENV['DB_DATABASE']};port={$_ENV['DB_PORT']}";
                $username = $_ENV['DB_USERNAME'];
                $password = $_ENV['DB_PASSWORD'];
                break;
            case 'pgsql':
                $dsn = "pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']}";
                $username = $_ENV['DB_USERNAME'];
                $password = $_ENV['DB_PASSWORD'];
                break;
            default:
                echo "Database type not supported";
                break;
        }

        try {
            $database = new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        $database->exec('CREATE TABLE IF NOT EXISTS users (
            id TEXT PRIMARY KEY NOT NULL,
            email TEXT NOT NULL,
            name TEXT NOT NULL,
            country TEXT
        )');
        foreach($merged_data as $data) {
            $sql = "INSERT INTO users (id, email, name, country) VALUES ( :id, :email, :name, :country)";
            $stmt = $database->prepare($sql);
            $id = $data['id'];
            $email = $data['email'];
            $name = $data['name'];
            $country = $data['country'];
            
            $id_check = $database->prepare("SELECT COUNT(*) FROM users WHERE id = :id");
            $id_check->bindValue(':id', $id);
            $id_check->execute();
            $count = $id_check->fetchColumn();
            if ($count > 0) {
                echo "User with ID '{$id}' already exists in the database".PHP_EOL;
                continue;
            }

            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':country', $country);
            try{
                $stmt->execute();
                if ($stmt->rowCount() === 0) {
                    throw new Exception("User with ID '{$id}' already exists in the database");
                }
                echo 'User with ID '.$id.' imported'. PHP_EOL;
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        $database = null;
        
    }
}

$aggregator = new AggregateData();
$merged_data = $aggregator->aggregate();
$aggregator->saveToDatabase($merged_data);
?>



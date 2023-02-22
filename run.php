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
        }else {
            throw new Exception('Failed to read csv file');
        }
        return $csv_data;
    }

    public function readNetwork($url) {
        try{
            $data = file_get_contents($url);
            $network_data = json_decode($data, true)['results'];
        } catch (Exception $e) {
            throw new Exception('Failed to read network data: ' . $e->getMessage());
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
        try{
            $csv_data = $this->readCsv($_ENV['CSV_PATH']);
            $network_data = $this->readNetwork($_ENV['USER_URL']);
            $merged_data = array_merge($csv_data, $network_data);
            return $merged_data;
        } catch (Exception $e) {
           throw new Exception('Failed to aggregate data: ' . $e->getMessage());
        }
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
                $dsn = "mysql:host={$_ENV["DB_HOST"]};dbname={$_ENV['DB_DATABASE']};port={$_ENV['DB_PORT']}";
                $username = $_ENV['DB_USERNAME'];
                $password = $_ENV['DB_PASSWORD'];
                break;
            case 'pgsql':
                $dsn = "pgsql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};port={$_ENV['DB_PORT']}";
                $username = $_ENV['DB_USERNAME'];
                $password = $_ENV['DB_PASSWORD'];
                break;
            default:
                throw new Exception('Invalid database type');
                break;
        }

        try {
            $database = new PDO($dsn, $username, $password);
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Failed to connect to database' .$e->getMessage());
        }
        try{
            $database->exec('CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY NOT NULL,
                email TEXT NOT NULL,
                name TEXT NOT NULL,
                country TEXT
            )');
        }catch (PDOException $e) {
            throw new Exception('Failed to create table' .$e->getMessage());
        }
       
        foreach($merged_data as $data) {
            $sql = "INSERT INTO users (id, email, name, country) VALUES ( :id, :email, :name, :country)";
            $stmt = $database->prepare($sql);
            $id = $data['id'];
            $email = $data['email'];
            $name = $data['name'];
            $country = $data['country'];

           
            try{
                $count = $database->query("SELECT COUNT(*) FROM users WHERE id = '{$id}'")->fetchColumn();
                if ($count > 0) {
                    echo "User with ID {$id} already exists in the database".PHP_EOL;
                    continue;
                }
                $stmt->bindValue(':id', $id);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':name', $name);
                $stmt->bindValue(':country', $country);
                $stmt->execute();
                echo 'User with ID '.$id.' imported'. PHP_EOL;
            } catch (PDOException $e) {
                throw new Exception('Error executing query' . $e->getMessage() . PHP_EOL);
            }
            catch(Exception $e) {
                throw new Exception('Error occured' . $e->getMessage() . PHP_EOL);
            }
        }
        $database = null;
    }
}

$aggregator = new AggregateData();
$merged_data = $aggregator->aggregate();
$aggregator->saveToDatabase($merged_data);
?>



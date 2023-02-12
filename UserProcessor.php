<?php

declare(strict_types=1);
include_once("env.php");

trait UserTrait {
    public function processUsers(array $providers, SQLite3 $database)
    {
        print_r($providers);
        foreach ($providers as $user) {
            $result = $database->query('SELECT count(*) FROM users WHERE id = ' . $user['id']);
            $num = $result->fetchArray(SQLITE3_NUM);
            if ($num[0] === 0) {
                $email = $user['email'];
                $name = $user['name'];
                $id = $user['id'];
                $country = $user['country'];
                $database->exec("INSERT INTO users VALUES($id,'$email','$name','$country')");
            }
        }
    }

    public function readDataFromCsv(string $getcurrentworkingDirectory) :array
    {
        $csv_provider = array_map('str_getcsv', file($getcurrentworkingDirectory . '/data/users.csv'));
        array_walk($csv_provider, function (&$a) use ($csv_provider) {
            $a = array_combine($csv_provider[0], $a);
        });
        array_shift($csv_provider); 

        return $csv_provider;
    }

    public function readDataFromNetwork() :array
    {   
        try{
            $file_path = UPLOAD_FILE_PATH_URL ?? '/data/network.json';
            $result = json_decode(file_get_contents($file_path))->results;
            return $result;
        }catch(Exception $e){
            echo 'An error occured while fetching data: ' . $e->getMessage();
        }
    }

    public function getNamedKeys() :array
    {
        return ['id', 'gender', 'name', 'country', 'postcode', 'email', 'birthdate'];
    }
}

class UserProcessor {
    use UserTrait;
    
    public function execute()
    {
        try{
            $getcurrentworkingDirectory = getcwd();
            $csv_provider = $this->readDataFromCsv($getcurrentworkingDirectory);
            $web_provider = $this->readDataFromNetwork();
            $web_data = [];
            foreach ($web_provider as $key => $item) {
                if ($item instanceof stdClass) {
                    $data = [
                        $key+1, 
                        $item->gender,
                        $item->name->first . ' ' . $item->name->last,
                        $item->location->country,
                        $item->location->postcode,
                        $item->email,
                        (new Datetime('now'))->format('Y') 
                    ];
                    $web_data[] = array_combine($this->getNamedKeys(), $data);
                }
            }

            $providers = array_merge($csv_provider, $web_data); 
            $database = new SQLite3($getcurrentworkingDirectory. '/database/users.dump');
            $database->exec('CREATE TABLE IF NOT EXISTS users (
                id   INTEGER PRIMARY KEY,
                email TEXT    NOT NULL,
                name TEXT NOT NULL,
                country TEXT)
            ');

            $this->processUsers($providers, $database);

            $database->close();

            echo 'Users imported!' . PHP_EOL;
        }catch (Exception $e) {
            echo 'An error occured while creating import: ' . $e->getMessage();
        }
    }
}

$processor = new UserProcessor();
class_alias('UserProcessor', 'run');
$processor->execute();

# Aggregate Data to Database
This PHP script aggregates data from a CSV file and an external API, then saves the data to a SQLite database.

## Setup
Make sure you have PHP and SQLite installed on your system.
Clone this repository.
Run the script by executing php run.php in the command line.

## Configuration
The following constants can be modified to change the behavior of the script:

- `USER_URL`: the URL of the network API that returns user data in JSON format.
- `DATABASE_PATH`: the file path to the SQLite database. The default path is ./database/test.db.
- `CSV_PATH`: the file path to the CSV file containing user data. The default path is ./data/users.csv.

## Functionality
The script uses a trait named ReadDataTrait to read data from the CSV file and the external API.

The AggregateData class uses the ReadDataTrait trait to read data from the CSV file and the external API. The aggregate method returns an array of merged data from both sources. The saveToDatabase method saves the merged data to a SQLite database named test.db in the database directory.

The SQLite database has a table named users with the following columns: id, email, name, and country. The id column is the primary key and is automatically generated. The email column contains the user's email address, the name column contains the user's full name, and the country column contains the user's country.

If a row in the merged data array does not have a name or location field, it is skipped.

## Acknowledgments
This script was created as a coding challenge for an interview. The ReadDataTrait trait was provided by the interviewer.
## About

Purpose of this project is to demonstrate Webhook accepts data from payment system and sync it with the Quick Books accounting software in real time.

## Pre Requisite

- Xampp/Wampp or Mamp server must be installed.
- Composer is installed and configured properly.
- Minimum PHP 7.2 (Not tested below or with later versions).

## Setup

- Fork the repository using this command: <code>git clone https://github.com/shahzaib91/tmam-expense-system.git</code>
- Run command <code>composer install</code> while you are inside the root folder.
- Configure .env file and setup database connection, webhook secret (can be obtained from payment system merchant's table) & Quick Books Api parameters by copying env.example file.
- Run command: <code>php artisan key:generate</code> to generate encryption key.
- Run command: <code>php artisan migrate</code> to generate database tables.
- Visit the URL to obtain Quick Books Access Token <code>http://localhost/tmam-expense-system/auth</code> this will redirect you to the login page of Quick Books and obtain access code which will later changed with access token upon successful login.
- Start exploring end-points available inside postman folder of payment system source code.
- You can check Transactions created in expense system via Web Hook by visiting <code>http://localhost/tmam-expense-system/</code> home page along with the flag stating whether the record is synced with Quick Books or not.

Important: If running both expense and payment setup in same local environment you might see merchant_name column error in postman this can happen because of putenv() leakage. Run the command given below and to read more about the issue refer to the provided link.

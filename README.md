How to run the app?

Step 1: Configure ENV to access mysql.
    Open the .env file and edit the param DATABASE_URL

Step 2: Run CLI to create database
    
    ````
        php bin/console doctrine:database:create
    ````

Step 3: Run CLI to run the app:

    ````
    php -S 127.0.0.1:8000 -t public
    ````

Access to login page: http://127.0.0.1:8000/login

Extra Bonus Part

CLI create author
````
php bin/console app:add-author "ahsoka.tano@royal-apps.io" "Kryze4President" "John" "Doe" "1990-01-01" "Famous writer" "male" "New York"
````
## Producthunt GPT

- laravel app that pulls the data from producthunt api https://api.producthunt.com/v2/docs to be trained for Producthunt gpt https://chat.openai.com/g/g-ii1Z3SlM5-prodcthunt-gpt

## Installation
- git clone git@github.com:amaelftah/Producthunt-GPT.git
- cp .env.example .env
- configure .env file and add DB credentials
- get developer token from producthunt here https://api.producthunt.com/v2/oauth/applications
- paste the developer token value inside .env (PRODUCT_HUNT_DEVELOPER_TOKEN) variable
- composer install
- php artisan migrate
- php artisan key:generate
- php artisan schedule:work https://laravel.com/docs/10.x/scheduling#running-the-scheduler-locally

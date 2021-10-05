 # Shoop.de PHP Developer Case Study
 
## Project overview
This is an API for the project related to management of personal finances.
Using it customer can track incomes and expenses, list all previously recorded transactions and get overall balance.

## Requirements

We kindly ask you as a candidate do the following:

* Run the API and check how every endpoint works by running examples provided below.
* Perform a source code audit for existing implementation issues, architectural, design or other flaws and describe each in the newly created `FOUND_ISSUES.md` document.
  Please prioritise the issues and put most critical ones to the top of the list.
* Fix the most critical issues.
* Optionally perform the refactoring for a few the most major design flaws.
* Create one or several pull request with all the changes.

Feel free to change any file in the repository as you wish, but please do not pay too much attention on infrastructural setup.

## Prerequisites
* [Docker for Desktop](https://www.docker.com/products/docker-desktop)

## How to install and run project

1. Clone the repository
2. Navigate to repository's directory
3. Build docker images:
```bash
docker compose build
```
4. Run the API:
```bash
docker compose up -d
```
5. Install composer packages:
```bash
docker compose exec php composer install
```
6. API should be accessible at `http://localhost`
7. Run tests:
```bash
docker compose exec php vendor/bin/behat
```
8. Shutdown the API:
```bash
docker compose down
```

## Available API actions

### Insert new transaction

```bash
curl --location --request PUT 'http://localhost/transaction' \
--header 'Content-Type: application/json' \
--data-raw '{
    "title": "Income",
    "amount": 100
}'
```
```bash
curl --location --request PUT 'http://localhost/transaction' \
--header 'Content-Type: application/json' \
--data-raw '{
    "title": "Lunch",
    "amount": -7.99
}'
```

### Get list of all transactions

```bash
curl --location --request GET 'http://localhost/transaction'
```

### Get balance

```bash
curl --location --request GET 'http://localhost/balance'
```

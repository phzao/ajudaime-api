# Personal Financial Control App

A simple API to control our expenses 

## Requirements

You must have installed Git, Docker, Docker-compose and Make before proceeding.
 
These ports must be available:
- 8888 (api)
 
## Installing

After cloning the repository you must run:


```bash
make up
```

The whole process can take a while, it depends on your computer.

After finish, just access the url, to register a user and start using:

``
http://localhost:8888/register
`` 

Note.: This installation must be done only once.

## Routes

Open Routes

````
POST - http://localhost:8888/authenticate-demo  -> generate a demo user, does not need data.
POST - http://localhost:8888/register -> register a user.
POST - http://localhost:8888/authenticate -> need a valid user.
````

Private Routes

````
GET - http://localhost:8888/api/v1/users/me  -> show profile from user
PUT - http://localhost:8888/api/v1/users
PUT - http://localhost:8888/api/v1/users/my-status-to/{status}

POST - http://localhost:8888/api/v1/places
GET - http://localhost:8888/api/v1/places/{uuid}
GET - http://localhost:8888/api/v1/places
PUT - http://localhost:8888/api/v1/places/{uuid}
PUT - http://localhost:8888/api/v1/places/{uuid}/default
PUT - http://localhost:8888/api/v1/places/{uuid}/status/{status}
DELETE - http://localhost:8888/api/v1/places/{uuid}

POST - http://localhost:8888/api/v1/earns
GET - http://localhost:8888/api/v1/earns/{uuid}
PUT - http://localhost:8888/api/v1/earns/{uuid}/confirm
PUT - http://localhost:8888/api/v1/earns/{uuid}
GET - http://localhost:8888/api/v1/earns
DELETE - http://localhost:8888/api/v1/earns/{uuid}

POST - http://localhost:8888/api/v1/categories
GET - http://localhost:8888/api/v1/categories/{uuid}
PUT - http://localhost:8888/api/v1/categories/{uuid}
GET - http://localhost:8888/api/v1/categories
PUT - http://localhost:8888/api/v1/categories/{uuid}/default
PUT - http://localhost:8888/api/v1/categories/{uuid}/status/{status}
DELETE - http://localhost:8888/api/v1/categories/{uuid}

POST - http://localhost:8888/api/v1/credit-cards
GET - http://localhost:8888/api/v1/credit-cards/{uuid}
PUT - http://localhost:8888/api/v1/credit-cards/{uuid}
GET - http://localhost:8888/api/v1/credit-cards
PUT - http://localhost:8888/api/v1/credit-cards/{uuid}/default
PUT - http://localhost:8888/api/v1/credit-cards/{uuid}/status/{status}
DELETE - http://localhost:8888/api/v1/credit-cards/{uuid}

POST - http://localhost:8888/api/v1/expenses
GET - http://localhost:8888/api/v1/expenses/{uuid}
PUT - http://localhost:8888/api/v1/expenses/{uuid}
GET - http://localhost:8888/api/v1/expenses
PUT - http://localhost:8888/api/v1/expenses/paid/{uuid}
DELETE - http://localhost:8888/api/v1/expenses/{uuid}
````
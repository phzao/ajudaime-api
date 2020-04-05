# API Be The Hero

A simple API to control donations to people who need. 
This API use elasticsearch using that `` https: // github.com / phzao / Ajudaime-db``

Ps. This API was made on 2 days and is there is no unit tests for routes yet. 

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
http://localhost:8888/google-authenticate
`` 

Note.: This installation must be done only once.

## Routes

Open Routes

````
GET - http://localhost:8888/public/donations/{user_id}/user  -> Show donations by user.
GET - http://localhost:8888/public/donations/{status}/status -> Show donations by status.
GET - http://localhost:8888/public/needs/{user_id}/user -> Show needs by user.
````

Private Routes

````
POST - http://localhost:8888/api/v1/needs 
PUT - http://localhost:8888/api/v1/needs/{uuid}
DELETE - http://localhost:8888/api/v1/needs/{uuid}

PUT - http://localhost:8888/api/v1/donations/{uuid}/done
PUT - http://localhost:8888/api/v1/donations/{uuid}/cancel
POST - http://localhost:8888/api/v1/donations

POST - http://localhost:8888/api/v1/talks/{donation_id}

PUT - http://localhost:8888/api/v1/users

````
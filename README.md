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
GET - http://localhost:8888/public/needs -> List all needs not canceled.
````

Private Routes

````
POST - http://localhost:8888/api/v1/needs 
PUT - http://localhost:8888/api/v1/needs/{uuid}
DELETE - http://localhost:8888/api/v1/needs/{uuid}
GET - http://localhost:8888/api/v1/needs/{uuid} -> show details 

PUT - http://localhost:8888/api/v1/donations/{uuid}/done -> did by a helper
PUT - http://localhost:8888/api/v1/donations/{uuid}/cancel -> did by a helper
PUT - http://localhost:8888/api/v1/donations/{uuid}/confirm -> did by who needy help
POST - http://localhost:8888/api/v1/donations

POST - http://localhost:8888/api/v1/talks/{donation_id}

PUT - http://localhost:8888/api/v1/users
GET - http://localhost:8888/api/v1/users -> show details

````

## How this works

Some rules for using this API.
- People in need can ask for help at a time, after being served can make a new request.
- Helpers can help select up to three people in need and have 48 hours to complete or aid will be canceled. (Time functionality is in development)
- If the needy tries to register words that are blacklisted, it will be blocked. (is in development)
- If there are 6 cancellations of donations due to time, help will be blocked. (is in development)
- The list of needy is presented by filtering by country (is under development)
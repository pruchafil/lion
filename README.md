# lion

## About app

This is the backend for the lion application, 
which displays parcels in the Jičín district. 
All parcels are downloaded in advance using the
sync-parcels.php script and are then continuously 
updated once every 30 days via an endpoint that 
returns only those parcels that have undergone a 
change during that time. For performance reasons, 
the maximum number of parcels rendered at any given 
time is limited to 200. 

## Dependencies

- Postgresql
- Postgis
- simplexml
- vlucas/phpdotenv
- slim

## Run

- php -S localhost:8000 main.php
- run sync-parcels with php sync-parcels.php

## Time spent

- 11h 5m - BE
- 1h     - FE
- 1h 30m - Docs

To keep things simple, I didn’t use Docker at all for this task and committed all changes directly to the Main branch (which is, of course, not a suitable approach for collaborative work). The most time-consuming part was getting familiar with the documentation, finding the correct endpoints, and parsing the resulting XML into the database. I decided on an approach where I first download the current status of parcels in the Jičín district and then continuously update only the most recent changes. I eventually simplified this approach so that the database is updated only once every 30 days, regardless of how often it is viewed.

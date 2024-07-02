## Rrerequisites

Next tolls should be installed locally:

[Docker](https://www.docker.com/get-started/)

[Docker Compose](https://docs.docker.com/compose/)

## The project up and running

After cloning the current repository proceed next steps:

Up docker containers for the first time with the build:

```
docker-compose up --build
```

Install PHP packages:

```
docker-compose run --rm  composer install
```

Apply migrations:

```
docker-compose run --rm console doctrine:migrations:migrate
```

That's all abut the project setup it shoul work now.

For future run containers without build:

```
docker-compose up
```

or in detached mode

```
docker-compose -d up
```

### Short functionality description

#### Create Author

- endpint: /api/authors
- method: post
- payload example:

```
        {
            "name": "Taras", //required
            "surname": "Shevshenko", //required
            "secondName": "Grigorovich" //optional
        }
```

#### Authors List

- endpint: /api/authors
- method: get
- optional params: offset

#### Create Book

- endpint: /api/books
- method: post
- payload example:

```
{
    "name": "Kobzar", //required, min 3 chars
    "description": "Collection of poens ", //optional
    "published": "2005", //optional
    "authors": "[1]" //required, multy
}
```

There is posibiliti to upload image

Field "key" in form-data for image is "file" // max 2Mb, jpg | png

#### Books List

- endpint: /api/books
- method: get
- optional params: offset

#### Books List filtered by Author's surname

- endpint: /api/books/author/{surname}
- method: get
- optional params: offset

#### Show single book

- endpint: /api/books/{id}
- method: get

#### Update Book

- endpint: /api/books
- method: put
- payload example:

```
{
    "name": "Kobzar", //required, min 3 chars
    "description": "New description", //optional
    "published": "2005", //optional
    "authors": "[1]" //required, multy
}
```

# bileMoAPI - Projet 7
An API of BileMo

Codacy Badge :
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/08e9decc08074a3687dcd40772a0d2cf)](https://www.codacy.com/gh/sebzz07/bileMoAPI/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=sebzz07/bileMoAPI&amp;utm_campaign=Badge_Grade)
## Installation :

This project has been developed under php 8.1 and symfony 6.1.

### Start this project in localhost mode, run some command lines:


1. Clone the GitHub repo:

```git clone https://github.com/sebzz07/bileMoAPI.git```

2. Go to the root of the project.

3. Create the folder ```config/jwt/```

4. Generate your private/public key with this two Openssl commands line : 

```
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

5. Create and fill out your own ```.env.*```

6. At the root of the project, install dependencies with composer:

```composer install```

7. Create database and some fixtures via doctrine :

with the script :

```composer initialize```

or : 
```
"symfony console doctrine:database:create",
"symfony console doctrine:schema:update --force",
"symfony console doctrine:fixtures:load -n"
```

8. run local server :

````symfony server:start -d````

*Now the project is normally deploy correctly*


## Information to test the project :

Your can check the documentation to understand the api and to test it : 

```https://localhost:8000/api/doc```

you can use one of the nine accounts created with the fixtures or just 
follow token's route : ```https://localhost:8000/api/login_check```

```
emailofcompagny1@email.com//password
...
emailofcompagny9@email.com//password
```

Thank you


# Docker container based development setup

This setup uses Docker to run the app and the database,
therefore it is very easy to set up on any platform that supports Docker (Linux, Windows via WSL2, etc.).
This setup is also recommended if you don't want to install PHP and other dependencies on your host machine,
or if you already have a different (incompatible) version of PHP installed.

## Requirements

- [Docker version 25.0.1 or newer](https://docs.docker.com/):
  - [Docker Build](https://docs.docker.com/engine/)
  - [Docker Compose](https://docs.docker.com/compose/)
- [git](https://git-scm.com/)

You can check whether these requirements are fulfilled by running the following commands:

```shell
docker -v
docker compose --help
git --version
```

The `cat` and `sed` utility commands are also used in setup scripts. If they are unavailable, you can just use a text editor manually to do the steps they aim to automate.

### Recommendations for Windows

Use WSL2 for everything.

- Install Docker on WSL2
- Use WSL2's filesystem instead of Windows' (do everything under e.g. the `\\wsl.localhost\Ubuntu\home\username` directory)
- Use a WSL2 shell for running the commands

## Initial setup, installation

The following script will clone the repository into the `mars` folder and perform the initial setup.

```shell
git clone git@github.com:EotvosCollegium/mars.git mars
cd mars/docker-dev-setup
cat ../.env.example | sed "s/DB_PASSWORD=secret/DB_PASSWORD=password/" | sed "s/DB_HOST=mysql/DB_HOST=mars_mysql/" > ../.env
docker compose up -d
docker exec -it mars_dev bash -c \
  "composer install \
  && npm install \
  && npm run prod \
  && php artisan key:generate \
  && php artisan migrate --seed"
docker compose stop
```

## Usage

### Starting the development environment

The following command must be executed from the repository root, e.g. the `mars` folder.

```shell
cd docker-dev-setup
docker compose up -d
docker exec -it mars_dev bash -c "php artisan serve --host=0.0.0.0"
```

The app is now running at [http://localhost:8000](http://localhost:8000).

When you are finished and wish to stop the web server and the database:

- Press `Ctrl+C` to stop the development server (started by `php artisan serve`)
- Run `docker compose stop` to stop the development environment

### Accessing the database

While the app is running, you can access the database (in a second terminal) via the `mysql` command in the docker container:

```shell
docker exec -it mars_mysql mysql --user=mars --password=password
```

Alternatively, you can access the database directly from you host machine (via an application of your choice) at this address: `127.0.0.1:3307`

### IDE integration

You can use an IDE or text editor of your choice. Here are some recommended IDEs and some tips for setting them up:

#### PHPStorm

- Set up a [remote PHP interpreter](https://www.jetbrains.com/help/phpstorm/configuring-remote-interpreters.html) using Docker Compose
  - Select the `docker-dev-setup/docker-compose.yml` configuration file and the `mars_dev` service

#### Visual Studio Code (vsc, vscode)

- For just basic text editing and syntax highlighting you don't need any special setup
  - If you are on Windows, make sure to use the WSL plugin and follow its instructions
- I am personally not familiar with VSC's Docker/PHP support, but...
  - There might be some Docker integration you can use
  - Or you can use the VSC Remote Development extension to run the VSC backend in the Docker container through an SSH connection

## Extras

Please check out the *Optional steps* and *For everyday use* sections of the [_SETUP_DEV_ENVIRONMENT.md](../_SETUP_DEV_ENVIRONMENT.md) file.

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
  && php artisan migrate:fresh --seed"
docker compose stop
```

### Optional setup steps

These steps are not necessary for a basic setup, but might be required for some features or might be helpful in some situations.

Please check out the ["Optional steps" section of the _SETUP_DEV_ENVIRONMENT.md](../_SETUP_DEV_ENVIRONMENT.md#optional-steps) file.
You may need to change your working directory to the root (`mars`) folder, and you may need to run some commands in the `mars_dev` container.
You can achieve the latter by running `docker exec -it mars_dev bash` and then running the commands in the container shell.

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
docker exec -it mars_mysql mysql --user=mars --password=password --database=mars
```

Alternatively, you can access the database directly from you host machine (via an application of your choice) at this address: `127.0.0.1:3307`

### Other useful commands, tips

Please check out the ["For everyday use" section of the _SETUP_DEV_ENVIRONMENT.md](../_SETUP_DEV_ENVIRONMENT.md#for-everyday-use) file.
The section contains information about how to reset your database, how to log in, etc.
You may need to run some commands in the `mars_dev` container's shell instead of on the host machine.

### IDE integration

You can use an IDE or text editor of your choice. Here are some recommended IDEs and some tips for setting them up:

#### PHPStorm

- Set up a [remote PHP interpreter](https://www.jetbrains.com/help/phpstorm/configuring-remote-interpreters.html) using Docker Compose
  - Select the `docker-dev-setup/docker-compose.yml` configuration file and the `mars_dev` service
- As an alternative to running `docker exec -it bash mars_dev bash` you can open a "container shell" in PHPStorm:
  - Open the "Services" tool window and navigate to Docker / Docker / Docker-compose: docker-dev-setup / mars_dev / mars_dev
    - These entries should already exist if you have set up the remote PHP interpreter
  - You can open a terminal by right-clicking on the container and selecting "Create Terminal"
  - In this terminal you can run `php`, `php artisan` and other commands directly (without `docker exec ...`)
  - You can also start and stop the containers in the context (right click) menu of "Docker-compose: docker-dev-setup"
    - This is an alternative to running `docker compose up -d` and `docker compose stop`
- Please check out the ["IDE integration / PHPStorm" section of the _SETUP_DEV_ENVIRONMENT.md](../_SETUP_DEV_ENVIRONMENT.md#phpstorm) file for more tips.
  - You may need to adapt some commands to run in the `mars_dev` container's shell instead of on the host machine.
    You can do that by entering `docker exec -it mars_dev bash -c "<cmd>"` instead of just `<cmd>`.

#### Visual Studio Code (vsc, vscode)

- I am personally not familiar with VSC's Docker/PHP support, but...
  - There might be some Docker integration you can use
  - Or you can use the VSC Remote Development extension to run the VSC backend in the Docker container through an SSH connection
- Please check out the ["IDE integration / VSC" section of the _SETUP_DEV_ENVIRONMENT.md](../_SETUP_DEV_ENVIRONMENT.md#visual-studio-code-vsc-vscode) file for more tips.
  - You may need to adapt some commands to run in the `mars_dev` container's shell instead of on the host machine.
    You can do that by entering `docker exec -it mars_dev bash -c "<cmd>"` instead of just `<cmd>`.

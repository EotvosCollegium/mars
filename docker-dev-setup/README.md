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

## Usage

### Starting the development environment

The following command must be executed from the repository root, e.g. the `mars` folder.

```shell
cd docker-dev-setup
docker compose up -d
docker exec -it mars_dev bash -c "php artisan serve --host=0.0.0.0"
```

The app is now running at [http://localhost:8000](http://localhost:8000).

You can log in as `example@eotvos.elte.hu` to gain access to a superuser account or as `collegist@eotvos.elte.hu` for a regular account.
The password is `asdasdasd` in both cases.

When you are finished and wish to stop the web server and the database:

- Press `Ctrl+C` to stop the development server (started by `php artisan serve`)
- Run `docker compose stop` to stop the development environment

### Accessing the database

While the app is running, you can access the database (in a second terminal) via the `mysql` command in the docker container:

```shell
docker exec -it mars_mysql mysql --user=mars --password=password
```

Alternatively, you can access the database directly from you host machine (via an application of your choice) at this address: `127.0.0.1:3307`

You can reset the database by running `docker exec -it mars_dev bash -c "php artisan migrate:fresh --seed"`.

### IDE integration

You can use an IDE or text editor of your choice. Here are some recommended IDEs and some tips for setting them up:

#### PHPStorm

- Set up a [remote PHP interpreter](https://www.jetbrains.com/help/phpstorm/configuring-remote-interpreters.html) using Docker Compose
  - Select the `docker-dev-setup/docker-compose.yml` configuration file and the `mars_dev` service
- Add support for Laravel Eloquent:
  - Generate IDE helper files: `docker exec mars_dev bash -c "php artisan clear-compiled && php artisan ide-helper:refresh"`
  - In PHPStorm click on: `File / Invalidate caches / Invalidate and Restart`
  - Now `self::where(...)`, `@mixin \Eloquent`, etc. shouldn't get marked as errors
- Add advanced support for Laravel:
  - Install the 3rd party [Laravel Idea](https://plugins.jetbrains.com/plugin/13441-laravel-plugin) plugin
    - This is a paid plugin, but [students can get a license for free](https://plugins.jetbrains.com/docs/marketplace/community-programs.html#how-to-apply)
  - Now a *Laravel* option should appear in your menu bar, and you should have access to numerous other powerful features
- Database integration:
  - Add a new MySQL [data source](https://www.jetbrains.com/help/phpstorm/connecting-to-a-database.html) with the credentials specified above
  - Install the 3rd party [Laravel Query](https://plugins.jetbrains.com/plugin/16309-laravel-query) plugin
  - Now auto-completion and validation will be available for model columns in queries among other features
- Excluding libraries and automatically generated files/folders from indexing and search:
  - Open a .gitignore file and agree to exclude the files/folders that are excluded from version control
  - Manually exclude the following folders: `storage/debugbar`, `storage/framework`, `storage/logs`
  - Now you should see less irrelevant search results and warnings

#### Visual Studio Code (vsc, vscode)

- For just basic text editing and syntax highlighting you don't need any special setup
  - If you are on Windows, make sure to use the WSL plugin and follow its instructions
- I am personally not familiar with VSC's Docker/PHP support, but...
  - There might be some Docker integration you can use
  - Or you can use the VSC Remote Development extension to run the VSC backend in the Docker container through an SSH connection

## Extras

Please check out the *Optional steps* and *For everyday use* sections of the [_SETUP_DEV_ENVIRONMENT.md](../_SETUP_DEV_ENVIRONMENT.md) file.

# Development environment

There are several ways to set up your development server and environment. Basically, you only need a running php server that uses the /public folder and an sql database connected to it. There are some tips how to achieve that (and even more) below.

## Developing on the cloud with gitpod (for starters or for slow machines)

1. Ask for access to our gitpod organization
2. Go to projects, and go to the bracnhes under the Mars project. Create a new workspace for your branch and open it in the browser or VS Code locally.
3. You will see three terminals. One for the server, one for npm, and one for the database. You can close the last two once the scripts are finished, but you still need to run `php artisan migrate:fresh --seed` to get the DB seeded. The site is served at `http://localhost:8000` (or check the first few lines server logs). 
4. Use with care, we have 50 hours of free usage per month. 

## Universal (using Docker - recommended)

This setup uses Docker to run the app and the database,
therefore it is very easy to set up on any platform that supports Docker (Linux, Windows via WSL2, etc.).
This setup is also recommended if you don't want to install PHP and other dependencies on your host machine,
or if you already have a different (incompatible) version of PHP installed.

The instructions can be found in the [docker-dev-setup/README.md file](docker-dev-setup/README.md).

## Universal (using VS Code dev containers  - recommended)

 1. Clone Mars: `git clone git@github.com:EotvosCollegium/mars.git`.
 2. Install [Composer](https://getcomposer.org/) and run `composer install` in the project directory.
 3. You need to install Docker (and WSL2 on Windows). See [requirements here](https://code.visualstudio.com/docs/remote/containers#_system-requirements).
 4. You need to install VS code
 5. You need to install the Remote Development extension pack in VS code.
 6. Open the project in VS code. Copy the `.env.example` file to `.env` and run `php artisan key:generate`. Set `DB_HOST` to `mysql` in `.env` file.
 8. VS code should notice that the project is configured to use dev containers and will promt you if you want to use it. Click yes, and you're all done!

Note: to regenerate the docker configuration, use `php artisan sail:install --devcontainer`

## OS X
For OS X, [Valet](https://laravel.com/docs/6.x/valet) gives a pretty smooth experience. Easy to download, easy to configure.

## Windows and Linux

For Windows and Linux the project has an example [Laravel Homestead](https://laravel.com/docs/homestead) configuration which can be used for local development.

With these steps you should be able to run Mars on your machine:

1. Clone Mars: `git clone git@github.com:EotvosCollegium/mars.git`.
2. Install [Vagrant](https://www.vagrantup.com/) and [VirtualBox](https://www.virtualbox.org/). (Or other virtualization platforms supported by Vagrant. Don't forget to reconfigure the `provider` in the steps below if you do so.)
3. Follow the instructions in the [First steps](https://laravel.com/docs/8.x/homestead#first-steps) section:
    - `vagrant box add laravel/homestead`
    - `git clone https://github.com/laravel/homestead.git` from a folder where you want to set up Homestead
    - go into this new directory
    - `git checkout release`
    - `init.bat` (`bash init.sh` on Linux)
4. Set up Homestead: Copy and rename `Homestead.yaml.example` from this repository to `Homestead.yaml` in the Homestead directory (overwrite if needed). Modify this file by changing `folders: - map: /your/local/path/to/mars` .
5. Create ssh keys to `~/.ssh/homestead_rsa.pub` and `~/.ssh/homestead_rsa`. (You can use something like `ssh-keygen -t rsa -b 4096 -C "your_email@example.com"`.)
6. On Windows add the `192.168.10.10 mars.local` host entry to `C:\Windows\System32\drivers\etc\hosts`.
7. Go to your Homestead directory and Run `vagrant up` and `vagrant ssh` to set up and enter your virtual machine.
8. In the project root (`cd mars`) run `composer install`
9. Set up Mars: Copy and rename `.env.example` to `.env`, and change these settings:
   `DB_DATABASE=homestead DB_USERNAME=homestead DB_PASSWORD=secret APP_URL=http://mars.local`.
10. Run the following commands:

-   Run `php artisan migrate:fresh --seed`.
-   Run `php artisan key:generate`.
-   Run `npm install` to install JS related dependencies.
-   Run `npm run dev` to create the CSS and JS files in the `public` directory.

11. The project should be running at [mars.local](http://mars.local/).

## Optional steps

-   You can add your personal access token from GitHub to use the GitHub API (eg. bug reports are sent through this). [You can generate a token here.](https://github.com/settings/tokens) You have to check the 'public_repo' scope.
-   If you want to test emails, change `MAIL_TEST_ADMIN` to your email (after seeding, you will be able to log in to the admin user with this email address) and set your email credentials (`MAIL_USERNAME` and `MAIL_PASSWORD`) - you might have to enable third party access to your email account.
-  If you are working with uploaded files, run `php artisan storage:link` to make the uploads available by their paths in the url.

## For everyday use

Most of the above setup is a one-time thing to do. However, whenever you start working on based on a newer version, you will have to run the following commands:

-   `npm run dev`: In case of recent UI changes (ie. JS or CSS), this will generate the new assets from `webpack.mix.js`. For frontend developers, `npm watch` might be useful -- it does the same, but also updates on change.
-   `php artisan migrate:fresh --seed`: This will migrate everything from scratch (useful if you work on changes in parallel) and seeds the database (so it basically resets the database).

You can log in to our seeded admin user with email `MAIL_TEST_ADMIN` (`example@eotvos.elte.hu` by default - you can find this in your .env file) and with password `asdasdasd`.
A regular account is available via the `collegist@eotvos.elte.hu` email address and the same password.
See `database/seeds/UsersTableSeeder.php` for more predefined users.

## IDE integration

#### Visual Studio Code (vsc, vscode)

- If you are on Windows, make sure to use the WSL plugin and follow its instructions
- This section is a stub, feel free to contribute

#### PHPStorm

- Create a run configuration for `php artisan serve`:
  - Create a new Shell Script run configuration under Run / Edit Configurations
  - Set an appropriate name, select "Script text" and paste the following: `php artisan serve --host=0.0.0.0`
  - Now you can just click the run button to start the development server
  - Feel free to repeat this step for other commands you frequently use, e.g. database migrations
- Add support for Laravel Eloquent:
  - Generate IDE helper files: run `php artisan clear-compiled && php artisan ide-helper:refresh`
  - In PHPStorm click on: File / Invalidate caches / Invalidate and Restart (this step might not be needed)
  - Now `self::where(...)`, `@mixin \Eloquent`, etc. shouldn't get marked as errors
- Add advanced support for Laravel:
  - Install the 3rd party [Laravel Idea](https://plugins.jetbrains.com/plugin/13441-laravel-plugin) plugin
    - This is a paid plugin, but [students can get a license for free](https://plugins.jetbrains.com/docs/marketplace/community-programs.html#how-to-apply)
  - Now a "Laravel" option should appear in your menu bar, and you should have access to numerous other powerful features
- Database integration:
  - Add a new MySQL [data source](https://www.jetbrains.com/help/phpstorm/connecting-to-a-database.html) with the credentials found in the `.env` file
  - Install the 3rd party [Laravel Query](https://plugins.jetbrains.com/plugin/16309-laravel-query) plugin
  - Now auto-completion and validation will be available for model columns in queries among other features
- Excluding libraries and automatically generated files/folders from indexing and search:
  - Open a .gitignore file and agree to exclude the files/folders that are excluded from version control
  - Manually exclude the following folders: `storage/debugbar`, `storage/framework`, `storage/logs`
  - Now you should see less irrelevant search results and warnings

## Alternative: running Docker from terminal with Sail

This method is known to work under Linux (Ubuntu 20.04, to be precise). Maybe it also works with WSL2.

The steps:
1. Follow the points under "Universal" until step 3 ([here are Docker installation instructions](https://docs.docker.com/engine/install/ubuntu/)).
2. Add your user to the `docker` group: `sudo groupadd docker && sudo usermod -aG docker $USER`. This will ensure you can manage Docker without `sudo` later. (Note: this might be a bit unsafe. But this is the way it worked for me.)
3. In `.env`, update `APP_URL` to `http://localhost:8080`.
4. Add these lines:

```
APP_PORT=8080
FORWARD_DB_PORT=33066
```

5. Still in `.env`, rewrite `DB_HOST` from the given IP to `mysql`.
6. Run `./vendor/bin/sail up`.
7. Open another terminal. Before seeding, add the correct privilege to Laravel's user in MySQL by running:

```sh
docker exec -it mars-mysql-1 mysql --password -e "SET GLOBAL log_bin_trust_function_creators = 1;"
```

8. Run `./vendor/bin/sail artisan migrate:fresh --seed`. (Other Artisan commands need to be executed similarly.)
9. Now you can test the site at `http://localhost:8080`.
10. Instead of SSH, you can use `docker exec -it mars-laravel.test-1 bash`.
11. And to access MySQL, run `docker exec -it mars-mysql-1 mysql --user=mars --password mars` (change the container name, the username and the database name if needed; the latter two are in .env) and log in with the password (also found in .env).

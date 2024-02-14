
## Development environment

There are several ways to set up your development server and environment. Basically, you only need a running php server that uses the /public folder and an sql database connected to it. There are some tips how to achieve that (and even more) below.

### Developing on the cloud with gitpod (for starters or for slow machines)

1. Ask for access to our gitpod organization
2. Go to projects, and go to the bracnhes under the Mars project. Create a new workspace for your branch and open it in the browser or VS Code locally.
3. You will see three terminals. One for the server, one for npm, and one for the database. You can close the last two once the scripts are finished, but you still need to run `php artisan migrate:fresh --seed` to get the DB seeded. The site is served at `http://localhost:8000` (or check the first few lines server logs). 
4. Use with care, we have 50 hours of free usage per month. 

### Universal (using VS Code dev containers  - recommended)

 1. Clone Mars: `git clone git@github.com:EotvosCollegium/mars.git`.
 2. Install [Composer](https://getcomposer.org/) and run `composer install` in the project directory.
 3. You need to install Docker (and WSL2 on Windows). See [requirements here](https://code.visualstudio.com/docs/remote/containers#_system-requirements).
 4. You need to install VS code
 5. You need to install the Remote Development extension pack in VS code.
 6. Open the project in VS code. Copy the `.env.example` file to `.env` and run `php artisan key:generate`. Set `DB_HOST` to `mysql` in `.env` file.
 8. VS code should notice that the project is configured to use dev containers and will promt you if you want to use it. Click yes, and you're all done!

Note: to regenerate the docker configuration, use `php artisan sail:install --devcontainer`

### Windows (natively)
On Windows, probably the optimal option with respect to performance is
to simply install a web server and a database server natively on the system,
instead of using containers or virtual machines.
However, the installation itself is a bit more complicated.
(But actually, there aren't as many mysterious errors as with containers.)

1. Download and install the following programs:
    - [XAMPP](https://www.apachefriends.org/download.html) (the newest available one) – this includes the PHP interpreter, the Apache web server and the MySQL database server.
        - Besides the obligatory options, we only need MySQL, phpmyadmin and Fake Sendmail.
        - You don't need to allow it through the firewall, as we are only going to access these locally.
    - [Composer](https://getcomposer.org/download/) – this is a package manager for PHP.
        - It's enough to install it only for your account when asked.
    - [NVM](https://github.com/coreybutler/nvm-windows/releases) (choose `nvm-setup.exe`) – this downloads Node.js and the NPM package manager, which will again download other things:D
    - [Git](https://gitforwindows.org/) – the version manager.
2. Open `C:\xampp\php\php.ini`. This is PHP's configuration file. We need to enable two extensions here for things to work.
    - To do this, search for the lines `;extension=zip` and `;extension=gd`, and delete the semicolon (`;`) before them. (If there are more than one copies of them, it's enough to enable only one instance.)
3. Open a command prompt (open Start menu and type `cmd`).
4. Type `nvm install lts`. This command will install NPM for you.
5. To be able to use it, you need to add the folder containing the NPM binary to the operating system's PATH variable (so that the command prompt will find it).
    - Check the installed version of Node.js with `nvm list`
    - Follow [these steps](https://www.mathworks.com/matlabcentral/answers/94933-how-do-i-edit-my-system-path-in-windows). Add the path `%USERPROFILE%\AppData\Roaming\nvm\vXX.XX.X`, where `XX.XX.X` is the version printed by `nvm list`.
6. Close the command prompt and open it again. This way, it is going to see the binaries under the folder added.
7. Select the directory under which you want to install the project, and `cd` to that directory. For example, if you choose `C:\Users\myname\Documents\`, say `cd C:\Users\myname\Documents\`.
8. Download the project by saying `git clone https://github.com/EotvosCollegium/mars`. This will create a `mars` folder under the current directory.
9. Say `cd mars`.
10. There are lots of dependencies Laravel (and specifically Mars) use. But now, Composer and NPM does this work instead of you. Just say:
```bat
composer update
composer install
npm install
npm run dev
```
11. Make a copy of the file named `.env.example` and call it `.env`. This is the configuration file of Mars; this specifies some settings that are local to the environment (in this case, your machine).
12. Open `.env`. Change `DB_HOST` to `127.0.0.1` (this is the IP on which you can access your own machine) and `DB_PASSWORD` to some long, random string (you won't have to remember it).
13. There are still some adventures left: we need to start and configure the database server. For this:
    - Open the XAMPP Control Panel.
    - Here, start MySQL.
    - Then, click on the `Admin` button next to the `Start`˛button. This will open an administration page called _phpmyadmin_.
    - On the left side, create a new database and name it `mars`.
    - Select `User accounts` and add a new user. This is what Mars is going to use. Name it `mars` and give it the password you wrote in `.env`.
    - After you created it, click `Edit privileges` and switch to the `Database` tab. Click on the `mars` database you created in the first step, and then `Go`. `Check all` and `Go` again.
    - You can now close this page.
14. To initialise the database (with fancy test data!) and some other things, switch back to the command prompt and while being in the `mars` folder, run:
```bat
php artisan key:generate
php artisan migrate:fresh --seed
```
15. Finally, you can start your server! For this, type `php artisan serve`. (To stop the server, press Control-C. If you want to use the command prompt, you will need to open a new window.)
16. Your local site can be accessed from the browser at `127.0.0.1:8000`. Log in with `example@eotvos.elte.hu` and `asdasdasd`.
17. If there are firewall exceptions for Apache or mysqld under _Control Panel\System and Security\Windows Defender Firewall\Allowed apps_, you can safely delete them, as you only need them on your own machine.

Most of these things need to be done only once. There are two steps you have to take every time you start working:
- Start MySQL in XAMPP Control Panel. (If it asks, you need not let it through the firewall.)
- Open a command prompt, `cd` to the `mars` directory and run `php artisan serve`.
When you are done, simply shut down your server by pressing Control-C while in the command prompt.

### OS X
For OS X, [Valet](https://laravel.com/docs/6.x/valet) gives a pretty smooth experience. Easy to download, easy to configure.

### Windows and Linux

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

### Optional steps

-   You can add your personal access token from GitHub to use the GitHub API (eg. bug reports are sent through this). [You can generate a token here.](https://github.com/settings/tokens) You have to check the 'public_repo' scope.
-   If you want to test emails, change `MAIL_TEST_ADMIN` to your email (after seeding, you will be able to log in to the admin user with this email address) and set your email credentials (`MAIL_USERNAME` and `MAIL_PASSWORD`) - you might have to enable third party access to your email account.
-  If you are working with uploaded files, run `php artisan storage:link` to make the uploads available by their paths in the url.

### For everyday use

Most of the above setup is a one-time thing to do. However, whenever you start working on based on a newer version, you will have to run the following commands:

-   `npm run dev`: In case of recent UI changes (ie. JS or CSS), this will generate the new assets from `webpack.mix.js`. For frontend developers, `npm watch` might be useful -- it does the same, but also updates on change.
-   `php artisan migrate:fresh --seed`: This will migrate everything from scratch (useful if you work on changes in parallel) and seeds the database.

You can log in to our seeded admin user with email `MAIL_TEST_ADMIN` (`example@eotvos.elte.hu` by default - you can find this in your .env file) and with password `asdasdasd`. See `database/seeds/UsersTableSeeder.php` for more predefined users.

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

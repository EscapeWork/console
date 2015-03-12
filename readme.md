## Escape installer

This installer installs the [Laravel Boilerplate](github.com/EscapeWork/LaravelBoilarplate) provided by [Agência Escape](http://www.escape.ppg.br). We built this based on the [Laravel Installer](https://github.com/laravel/installer).

### Installing

```
$ composer global require escapework/console:dev-master
```

After that, just execute the command: 

```
$ escape app:install your-project-name 
```

This will:

* Clone the repository;
* Install npm dependencies;
* Install composer dependencies;
* Generate PHP artisan key;
* Init a new git repository;

##### Options

We also have some options to optimize your time:

* `--with-manager` - This will works only if you work at [Agência Escape](http://www.escape.ppg.br)

### License

See the [LICENSE](https://github.com/escapecriativacao/console/blob/master/LICENSE) file.
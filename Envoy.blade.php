

@servers(['web' => 'ubuntu@www.music.sir-edgar.com'])

@task('deploy', ['on' => ['web']])

    cd EdSound/

    php artisan down

    @if ($branch)
        git pull origin {{ $branch }}
    @else
        git pull
    @endif

    composer install

    php artisan migrate --force

    php artisan up

@endtask


/home/ubuntu/bin:/home/ubuntu/.local/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games:/snap/bin:/home/ubuntu/.composer/vendor/bin
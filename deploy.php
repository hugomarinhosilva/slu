<?php
namespace Deployer;

require 'recipe/symfony.php';

// Project name
set('application', 'slu');

// Project repository
set('repository', 'git@git.uft.edu.br:delta/slu2.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_dirs', []);
add('shared_files', []);


// Writable dirs by web server 
add('writable_dirs', ['app/sessions']);
set('allow_anonymous_stats', false);

// Hosts
host('producao')
    ->hostname('bacaba.uft.edu.br')
    ->user('sysadmin')
    ->set('deploy_path','/srv/www/{{application}}')
    ->stage('bacaba');
host('homolog')
    ->hostname('pitangueira.uft.edu.br')
    ->user('sysadmin')
    ->set('deploy_path','/srv/www/{{application}}')
    ->stage('homologacao');
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');



// Tasks
desc('Restart PHP-FPM service');
task('php-fpm:restart', function () {
    // The user must have rights for restart service
    // /etc/sudoers: username ALL=NOPASSWD:/bin/systemctl restart php-fpm.service
    run('sudo systemctl restart php5-fpm.service');
});
after('deploy:symlink', 'php-fpm:restart');

/**
 * Executa o assetics avanzu
 */
task('uft:avanzu_install', function () {
    run("SYMFONY_ENV=prod php {{release_path}}/" . trim(get('bin_dir'), '/') ."/console avanzu:admin:fetch-vendor");
    //run("mv {{release_path}}/web/bundles/avanzuadmintheme/vendor/adminlte/plugins/daterangepicker/daterangepicker.css {{release_path}}/web/bundles/avanzuadmintheme/vendor/adminlte/plugins/daterangepicker/daterangepicker-bs3.css");
})->desc('Update avanzu');
after('deploy:vendors','uft:avanzu_install');

/**
 * Configura o nginx
 */
task('uft:configura_nginx', function () {
    $mensagem = run("sudo nginxConf.sh {{application}}");
    run("sudo service nginx restart");
    $mensagem = str_replace('{url}',get('hostname'),$mensagem);
    writeln(" <comment>$mensagem</comment> ");
})->desc('Configurando NGINX');
after('cleanup','uft:configura_nginx');

/**
 * Executa o update do banco de dados
 */
task('uft:database_update', function () {
    $serverName = get('stage');
    $run = false;
    $run = askConfirmation("Run doctrine:schema:update on $serverName server?", $run);
    if ($run) {
        run("SYMFONY_ENV=prod php {{release_path}}/" . trim(get('bin_dir'), '/') ."/console doctrine:schema:update --force");
    }
})->desc('Update database');
after('deploy:cache:warmup','uft:database_update');

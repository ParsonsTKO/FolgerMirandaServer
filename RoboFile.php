<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */

use Robo\Tasks;

class RoboFile extends Tasks {

    /**
     * Frontend update (Bower).
     */
    function frontendPull() {
        $frontends = [
            "DAP" => "src/AppBundle/Resources/public",
        ];
        foreach ($frontends as $frontend => $path) {
            $this->say("Updating frontend of site '".$frontend."'");
            $this->taskBowerUpdate()
                ->dir($path)
                ->noDev()
                ->run();
        }
    }
    
    /**
     * Frontend update (for Vagrant use only).
     */
    function frontendUpdate() {
    	$this->taskGitStack()
    	->stopOnFail()
    	->pull();
    	$this->frontendPull();
    	$this->vagrant("ssh -- 'rm -rf web/css/* && bin/console assets:install --relative --symlink && bin/console assetic:dump'");
    	$this->cc();
    }

    /**
     * compile.
     * @param string $files files pair to compile, example path/to/file.scss:path/to/file.min.css
     */
    function sassCompress($files = "") {
        $this->taskExec('sass --scss --force --no-cache --style compressed --update '.$files)
            ->run();
    }

    /**
     * compile.
     * @param string $files files pair to compile, example path/to/file.scss:path/to/file.css
     */
    function sassCompile($files = "") {
        $this->taskExec('sass --scss --force --no-cache --style expanded --update '.$files)
            ->run();
    }
    
    /**
     * PHP Coding Standards Fixer (All CCB projects)
     */
    function backendCs() {
    	$this->taskParallelExec()
	    	->process('php-cs-fixer fix src/AppBundle/Controller')
	    	->process('php-cs-fixer fix src/DAPBundle/Controller')
	    	->process('php-cs-fixer fix src/DAPBundle/Resolver')
	    	->process('php-cs-fixer fix src/DAPImportBundle/Controller')
	    	->process('php-cs-fixer fix src/DAPImportBundle/Services')
    		->run();
    }
}

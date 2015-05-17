<?php namespace Xampper\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;


class AddNewHost extends Command {

    /**
     * @var string $lineBreak
     */
    public $lineBreak = "\r\n";

    /**
     * @var string $tab
     */
    public $tab = "\t";

    /**
     * @var InputInterface $input
     */
    protected $input;

    /**
     * @var OutputInterface $output
     */
    protected $output;

    /**
     * @var HelperInterface $helper
     */
    protected $helper;

    /**
     * The path of the virtual host file
     *
     * @var string $virtualHostPath
     */
    protected $virtualHostPath = "C:/xampp/apache/conf/extra/httpd-vhosts.conf";

    /**
     * The file contents of the virtual host file
     *
     * @var string $virtualHostFile
     */
    protected $virtualHostFile;

    /**
     * The new virtual host to be added to the virtual hosts file
     *
     * @var string $vhost
     */
    protected $vhost;

    /**
     * The path of the hosts file
     *
     * @var string $hostsPath
     */
    protected $hostsPath = "C:/Windows/System32/drivers/etc/hosts";

    /**
     * The file contents of the hosts file
     *
     * @var string $hostsFile
     */
    protected $hostsFile;

    /**
     * The new host to be added to the hosts file
     *
     * @var string $newHost;
     */
    protected $host;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('new')
            ->setDescription('Create a new Xampp virtual host and add an entry to the hosts file.');
    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $this->getHelper('question');

        // Get the server name
        $question = new Question("Please enter the server name, this will be used in the virtual host and in the entry inside the hosts file\r\n",
            'Test');
        $serverName = $this->helper->ask($input, $output, $question);

        $this->info("");
        $this->info("");

        $question = new Question("Please enter the directory name, the folder will be created here:\r\n<info>C:\\xampp\\htdocs\\[directory name]</info>\r\n",
            'Test');
        $directoryName = $this->helper->ask($input, $output, $question);

        $this->info("");
        $this->info("");

        $question = new Question("Please enter a comment to identify this virtual host (optional, press enter to ignore)\r\n",
            $directoryName . " - " . $serverName);
        $comment = $this->helper->ask($input, $output, $question);

        $this->info("Building Virtual Host...");

        $this->setVirtualHostFile();
        $this->createVirtualHost($serverName, $directoryName, $comment);

        print_r( $this->vhost );

        $this->saveVirtualHost();

        $this->info("Successfully added  Virtual Host");
        $this->info("");$this->info("");
        $this->info("Proceeding to add the entry to the hosts file");

        $question = new ConfirmationQuestion('Change the default 127.0.0.1 local address? (y=yes, n=no)', false);

        $ip = "127.0.0.1";
        if ( $this->helper->ask($input, $output, $question) )
        {
            $question = new Question("Please enter a new IP:\r\n",
                '127.0.0.1');
            $ip = $this->helper->ask($input, $output, $question);
        }

        $this->createNewHost($serverName, $ip);
        $this->info("");

        print_r($this->host);
        $this->setHostsFile();
        $this->saveHost();

        $this->info("\r\n\r\nSuccessfully saved the virtual host and the hosts file");

        // @TODO Check whether host already exists
        //$this->verifyVirtualHostDoesntExist( $input->getArgument('name') );

    }

    /**
     * Create a virtual host
     *
     * @param string $hostName
     * @param string $dir
     * @param string $comment
     */
    public function createVirtualHost($hostName, $dir, $comment)
    {
        $this->addLineBreak(2);

        $this->addComment($comment);
        $this->addLineBreak();
        $this->vhost .= "<VirtualHost *>";

        $this->addLineBreak();
        $this->addTab();
        $this->addDocumentRoot($dir);

        $this->addLineBreak();
        $this->addTab();
        $this->addServerName($hostName);

        $this->addLineBreak();
        $this->addLineBreak();

        $this->addTab();
        $this->addDirectoryPath($dir);

        $this->addLineBreak();

        $this->vhost .= "</VirtualHost>";
    }

    public function addDocumentRoot($dir)
    {
        $this->vhost .= 'DocumentRoot "C:\xampp\htdocs\\';

        $this->vhost .= $dir;

        $this->vhost .= '"';
    }

    /**
     * Add the server name to the virtual host
     *
     * @param string $hostName
     */
    public function addServerName($hostName)
    {
        $this->vhost .= 'ServerName ' . $hostName;
    }

    /**
     * Add the <directory> section to the virtual host
     *
     * @param string $dir
     */
    public function addDirectoryPath($dir)
    {
        $this->vhost .= '<Directory "C:\xampp\htdocs\\' . $dir . '">';

        $this->addLineBreak();
        $this->addTab(2);

        $this->addRequire();

        $this->addLineBreak();
        $this->addTab();

        $this->vhost .= "</Directory>";
    }

    /**
     * Add the "Require all granted" line to the virtual host
     *
     * @param string $grantOrDeny
     */
    public function addRequire($grantOrDeny = "granted")
    {
        $this->vhost .= "Require all " . $grantOrDeny;
    }

    /**
     * Add a line break(s) to the virtual host
     *
     * @param int $x    The number of line breaks to add
     */
    public function addLineBreak($x = 1)
    {
        $i = 1;
        while($i <= $x)
        {
            $this->vhost .= $this->lineBreak;
            $i++;
        }
    }

    /**
     * Add a tab(s) to the virtual host
     *
     * @param int $x    The number of tabs to add
     */
    public function addTab($x = 1)
    {
        $i = 1;
        while($i <= $x)
        {
            $this->vhost .= $this->tab;
            $i++;
        }
    }

    /**
     * Add a comment to the virtual host
     *
     * @param string $comment
     */
    public function addComment($comment)
    {
        $this->vhost .= "########### ";
        $this->vhost .= $comment;
        $this->vhost .= " ###########";
    }

    /**
     * Set $virtualHostFile to be the file contents
     */
    public function setVirtualHostFile()
    {
        $this->virtualHostFile = file_get_contents($this->virtualHostPath);
    }

    /**
     * Get the virtual host file
     *
     * @return string
     */
    public function getVirtualHostFile()
    {
        return $this->virtualHostFile;
    }

    /**
     * Save the new virtual host at the bottom of the vhosts file
     */
    public function saveVirtualHost()
    {
        $this->backupVirtualHostFile();
        file_put_contents($this->virtualHostPath, $this->virtualHostFile . $this->vhost);
    }

    /**
     * Backup the virtual host file for safety
     */
    public function backupVirtualHostFile()
    {
        $filename = $this->virtualHostPath . "_bak_" . date('Ymd_His');
        file_put_contents($filename, $this->virtualHostFile);
    }

    /**
     * Create a new hosts entry
     *
     * @param string $serverName
     * @param string $ip
     */
    public function createNewHost($serverName, $ip, $comment = null)
    {
        $this->host .= $this->lineBreak;
        $this->host .= $this->lineBreak;

        $this->host .= $ip . "    " . $serverName ;

        if( ! is_null($comment) )
        {
            $this->host .= " #" . $comment;
        }
    }

    /**
     * Sets the hostsFile to the contents of the hosts file
     */
    public function setHostsFile()
    {
        $this->hostsFile = file_get_contents($this->hostsPath);
    }

    /**
     * Save the new virtual host at the bottom of the vhosts file
     */
    public function saveHost()
    {
        file_put_contents($this->hostsPath, $this->hostsFile . $this->host);
    }


    /**
     * Print an info message to the console
     *
     * @param string $text  The text to be wrapped inside <info></info>
     */
    public function info($text)
    {
        $this->output->writeln("<info>" . $text . "</info>");
    }
}
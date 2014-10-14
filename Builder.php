<?php

namespace Gregwar\RST;

/**
 * A builder can parses a whole directory to build the target architecture
 * of a document
 */
class Builder
{
    const NO_PARSE = 1;
    const PARSE = 2;

    // Tree index name
    protected $indexName = 'index';

    // Error manager
    protected $errorManager = null;

    // Verbose build ?
    protected $verbose = true;

    // Files to copy at the end of the build
    protected $toCopy = array();
    protected $toMkdir = array();

    // Source and target directory
    protected $directory;
    protected $targetDirectory;

    // Metas for documents
    protected $metas;

    // States (decision) of the scanned documents
    protected $states = array();

    // Queue of documents to be parsed
    protected $parseQueue = array();

    // Parsed documents waiting to be rendered
    protected $documents = array();

    // Kernel
    protected $kernel;

    // Hooks before the parsing on the environment
    protected $beforeHooks = array();

    // Hooks after the parsing
    protected $hooks = array();

    public function __construct($kernel = null)
    {
        $this->errorManager = new ErrorManager;

        if ($kernel) {
            $this->kernel = $kernel;
        } else {
            $this->kernel = new HTML\Kernel;
        }

        $this->kernel->initBuilder($this);
    }

    public function getErrorManager()
    {
        return $this->errorManager;
    }

    /**
     * Adds an hook which will be called on each document after parsing
     */
    public function addHook($function)
    {
        $this->hooks[] = $function;
        
        return $this;
    }

    /**
     * Adds an hook which will be called on each environment during building
     */
    public function addBeforeHook($function)
    {
        $this->beforeHooks[] = $function;

        return $this;
    }

    protected function display($text)
    {
        if ($this->verbose) {
            echo $text."\n";
        }
    }

    public function build($directory, $targetDirectory = 'output', $verbose = true)
    {
        $this->verbose = $verbose;
        $this->directory = $directory;
        $this->targetDirectory = $targetDirectory;

        // Creating output directory if doesn't exists
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        // Try to load metas, if it does not exists, create it
        $this->display('* Loading metas');
        $this->metas = new Metas($this->loadMetas());

        // Scan all the metas and the index
        $this->display('* Pre-scanning files');
        $this->scan($this->getIndexName());
        $this->scanMetas();

        // Parses all the documents
        $this->parseAll();

        // Renders all the documents
        $this->render();

        // Saving the meta
        $this->display('* Writing metas');
        $this->saveMetas();

        // Copy the files
        $this->display('* Running the copies');
        $this->doMkdir();
        $this->doCopy();
    }

    /**
     * Renders all the pending documents
     */
    protected function render()
    {
        $this->display('* Rendering documents');
        foreach ($this->documents as $file => &$document) {
            $this->display(' -> Rendering '.$file.'...');
            $target = $this->getTargetOf($file);

            $directory = dirname($target);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($target, $document->renderDocument());
        }
    }

    /**
     * Adding a file to the parse queue
     */
    protected function addToParseQueue($file)
    {
        $this->states[$file] = self::PARSE;

        if (!isset($this->documents[$file])) {
            $this->parseQueue[$file] = $file;
        }
    }

    /**
     * Returns the next file to parse
     */
    protected function getFileToParse()
    {
        if ($this->parseQueue) {
            return array_shift($this->parseQueue);
        } else {
            return null;
        }
    }

    /**
     * Parses all the document that need to be parsed
     */
    protected function parseAll()
    {
        $this->display('* Parsing files');
        while ($file = $this->getFileToParse()) {
            $this->display(' -> Parsing '.$file.'...');
            // Process the file
            $rst = $this->getRST($file);
            $parser = new Parser(null, $this->kernel);

            $environment = $parser->getEnvironment();
            $environment->setMetas($this->metas);
            $environment->setCurrentFilename($file);
            $environment->setCurrentDirectory($this->directory);
            $environment->setTargetDirectory($this->targetDirectory);
            $environment->setErrorManager($this->errorManager);

            foreach ($this->beforeHooks as $hook) {
                $hook($parser);
            }

            if (!file_exists($rst)) {
                $this->errorManager->error('Can\'t parse the file '.$rst);
                continue;
            }

            $document = $this->documents[$file] = $parser->parseFile($rst);

            // Calling all the post-process hooks
            foreach ($this->hooks as $hook) {
                $hook($document);
            }

            // Calling the kernel document tweaking
            $this->kernel->postParse($document);

            $dependencies = $document->getEnvironment()->getDependencies();

            if ($dependencies) {
                $this->display(' -> Scanning dependencies of '.$file.'...');
                // Scan the dependencies for this document
                foreach ($dependencies as $dependency) {
                    $this->scan($dependency);
                }
            }

            // Append the meta for this document
            $this->metas->set(
                $file,
                $this->getUrl($document),
                $document->getTitle(),
                $document->getTitles(),
                $document->getTocs(),
                filectime($rst),
                $dependencies
            );
        }
    }

    /**
     * Scans a file, this will check the status of the file and tell if it
     * needs to be parsed or not
     */
    public function scan($file)
    {
        // If no decision is already made about this file
        if (!isset($this->states[$file])) {
            $this->display(' -> Scanning '.$file.'...');
            $this->states[$file] = self::NO_PARSE;
            $entry = $this->metas->get($file);
            $rst = $this->getRST($file);

            if (!$entry || !file_exists($rst) || $entry['ctime'] < filectime($rst)) {
                // File was never seen or changed and thus need to be parsed
                $this->addToParseQueue($file);
            } else {
                // Have a look to the file dependencies to knoww if you need to parse
                // it or not
                $depends = $entry['depends'];

                if (isset($entry['parent'])) {
                    $depends[] = $entry['parent'];
                }

                foreach ($depends as $dependency) {
                    $this->scan($dependency);

                    // If any dependency needs to be parsed, this file needs also to be
                    // parsed
                    if ($this->states[$dependency] == self::PARSE) {
                        $this->addToParseQueue($file);
                    }
                }
            }
        }
    }

    /**
     * Scans all the metas
     */
    public function scanMetas()
    {
        $entries = $this->metas->getAll();

        foreach ($entries as $file => $infos) {
            $this->scan($file);
        }
    }

    /**
     * Get the meta file name
     */
    protected function getMetaFile()
    {
        return $this->getTargetFile('meta.php');
    }


    /**
     * Try to inport the metas from the meta files
     */
    protected function loadMetas()
    {
        $metaFile = $this->getMetaFile();

        if (file_exists($metaFile)) {
            return @include($metaFile);
        }

        return null;
    }

    /**
     * Saving the meta files
     */
    protected function saveMetas()
    {
        $metas = '<?php return '.var_export($this->metas->getAll(), true).';';
        file_put_contents($this->getMetaFile(), $metas);
    }

    /**
     * Gets the .rst of a source file
     */ 
    public function getRST($file)
    {
        return $this->getSourceFile($file . '.rst');
    }

    /**
     * Gets the name of a target for a file, for instance /introduction/part1 could
     * be resolved into /path/to/introduction/part1.html
     */
    public function getTargetOf($file)
    {
        $meta = $this->metas->get($file);

        return $this->getTargetFile($meta['url']);
    }

    /**
     * Gets the URL of a target file
     */
    public function getUrl($document)
    {
        $environment = $document->getEnvironment();

        return $environment->getUrl() . '.' . $this->kernel->getFileExtension();
    }

    /**
     * Gets the name of a target file
     */
    public function getTargetFile($filename)
    {
        return $this->targetDirectory . '/' . $filename;
    }

    /**
     * Gets the name of a source file
     */
    public function getSourceFile($filename)
    {
        return $this->directory . '/' . $filename;
    }

    /**
     * Run the copy
     */
    public function doCopy()
    {
        foreach ($this->toCopy as $copy) {
            list($source, $destination) = $copy;
            if ($source[0] != '/') {
                $source = $this->getSourceFile($source);
            }
            $destination = $this->getTargetFile($destination);

            if (is_dir($source) && is_dir($destination)) {
                $destination = dirname($destination);
            }

            shell_exec('cp -R '.$source.' '.$destination);
        }
    }

    /**
     * Add a file to copy
     */
    public function copy($source, $destination = null)
    {
        if ($destination === null) {
            $destination = basename($source);
        }

        $this->toCopy[] = array($source, $destination);

        return $this;
    }

    /**
     * Run the directories creation
     */
    public function doMkdir()
    {
        foreach ($this->toMkdir as $mkdir) {
            $dir = $this->getTargetFile($mkdir);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }   
        }   
    }

    /**
     * Creates a directory in the target
     *
     * @param $directory the directory name to create
     */
    public function mkdir($directory)
    {
        $this->toMkdir[] = $directory;

        return $this;
    }

    public function setIndexName($name)
    {
        $this->indexName = $name;

        return $this;
    }

    public function getIndexName()
    {
        return $this->indexName;
    }
}

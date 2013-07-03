<?php

namespace Gregwar\RST;

/**
 * A directory parser can parses a whole directory tree and
 * produces an output tree
 */
class DirectoryParser
{
    const NO_PARSE = 1;
    const PARSE = 2;

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

    public function parse($directory, $targetDirectory = 'output')
    {
        $this->directory = $directory;
        $this->targetDirectory = $targetDirectory;

        // Creating output directory if doesn't exists
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        // Try to load metas, if it does not exists, create it
        echo "* Loading metas\n";
        $this->metas = new Metas($this->loadMetas());

        // Scan all the metas and the index
        echo "* Pre-scanning files\n";
        $this->scan('index');
        $this->scanMetas();

        // Parses all the documents
        $this->parseAll();

        // Renders all the documents
        $this->render();

        // Saving the meta
        echo "* Writing metas\n";
        $this->saveMetas();
    }

    /**
     * Renders all the pending documents
     */
    protected function render()
    {
        echo "* Rendering documents\n";
        foreach ($this->documents as $file => &$document) {
            echo " -> Rendering $file...\n";
            $target = $this->getNameOfFile($file);
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
        echo "* Parsing files\n";
        while ($file = $this->getFileToParse()) {
            echo " -> Parsing $file...\n";
            // Process the file
            $rst = $this->getRST($file);
            $parser = new Parser($this->metas);

            if (!file_exists($rst)) {
                throw new \Exception('Can\'t parse the file '.$rst);
            }

            $document = $this->documents[$file] = $parser->parse(file_get_contents($rst));
            $dependencies = $document->getEnvironment()->getDependencies();

            if ($dependencies) {
                echo " -> Scanning dependencies of $file...\n";
                // Scan the dependencies for this document
                foreach ($dependencies as $dependency) {
                    $this->scan($dependency);
                }
            }

            // Append the meta for this document
            $this->metas->set(
                $file,
                $this->getUrl($file),
                $document->getTitle(),
                $document->getTitles(),
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
            echo " -> Scanning $file...\n";
            $this->states[$file] = self::NO_PARSE;
            $entry = $this->metas->get($file);
            $rst = $this->getRST($file);

            if (!$entry || !file_exists($rst) || $entry['ctime'] < filectime($rst)) {
                // File was never seen or changed and thus need to be parsed
                $this->addToParseQueue($file);
            } else {
                // Have a look to the file dependencies to knoww if you need to parse
                // it or not
                foreach ($entry['depends'] as $dependency) {
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
     * Gets the name of a target file, for instance /introduction/part1 could
     * be resolved into /path/to/introduction/part1.html
     */
    public function getNameOfFile($file)
    {
        return $this->getTargetFile($this->getUrl($file));
    }

    /**
     * Gets the URL of a target file
     */
    public function getUrl($file)
    {
        return $file . '.html';
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
}

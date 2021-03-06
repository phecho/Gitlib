<?php

/*
 * This file is part of Gitlib.
 *
 * Copyright (C) 2015-2016 The Gitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlib;

use Gitlib\Model\Blob;
use Gitlib\Model\Commit\Commit;
use Gitlib\Model\Commit\Diff;
use Gitlib\Model\Tree;
use Gitlib\Statistics\StatisticsInterface;

class Repository
{
    protected $path;
    protected $client;
    protected $commitsHaveBeenParsed = false;

    protected $statistics = [];

    public function __construct($path, Client $client)
    {
        $this->setPath($path);
        $this->setClient($client);
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function setCommitsHaveBeenParsed($value)
    {
        $this->commitsHaveBeenParsed = $value;
    }

    /**
     * @return bool
     */
    public function getCommitsHaveBeenParsed()
    {
        return $this->commitsHaveBeenParsed;
    }

    /**
     * Create a new git repository.
     */
    public function create($bare = null)
    {
        mkdir($this->getPath());
        $command = 'init';

        if ($bare) {
            $command .= ' --bare';
        }

        $this->getClient()->run($this, $command);

        return $this;
    }

    /**
     * Get a git configuration variable.
     *
     * @param string $key Configuration key
     */
    public function getConfig($key)
    {
        $key = $this->getClient()->run($this, 'config '.$key);

        return trim($key);
    }

    /**
     * Set a git configuration variable.
     *
     * @param string $key   Configuration key
     * @param string $value Configuration value
     */
    public function setConfig($key, $value)
    {
        $this->getClient()->run($this, "config $key \"$value\"");

        return $this;
    }

    /**
     * Add statistic aggregator.
     *
     * @param StatisticsInterface|array $statistics
     */
    public function addStatistics($statistics)
    {
        if (! is_array($statistics)) {
            $statistics = [$statistics];
        }

        foreach ($statistics as $statistic) {
            $reflect = new \ReflectionClass($statistic);
            $this->statistics[strtolower($reflect->getShortName())] = $statistic;
        }
    }

    /**
     * Get statistic aggregators.
     *
     * @return array
     */
    public function getStatistics($branch = null)
    {
        if ($this->getCommitsHaveBeenParsed() === false) {
            $this->getCommits();
        }

        foreach ($this->statistics as $statistic) {
            $statistic->sortCommits();
        }

        return $this->statistics;
    }

    /**
     * Add untracked files.
     *
     * @param mixed $files Files to be added to the repository
     */
    public function add($files = '.')
    {
        if (is_array($files)) {
            $files = implode(' ', array_map('escapeshellarg', $files));
        } else {
            $files = escapeshellarg($files);
        }

        $this->getClient()->run($this, "add $files");

        return $this;
    }

    /**
     * Add all untracked files.
     */
    public function addAll()
    {
        $this->getClient()->run($this, 'add -A');

        return $this;
    }

    /**
     * Commit changes to the repository.
     *
     * @param string $message Description of the changes made
     */
    public function commit($message)
    {
        $this->getClient()->run($this, "commit -m \"$message\"");

        return $this;
    }

    /**
     * Checkout a branch.
     *
     * @param string $branch Branch to be checked out
     */
    public function checkout($branch)
    {
        $this->getClient()->run($this, "checkout $branch");

        return $this;
    }

    /**
     * Pull repository changes.
     */
    public function pull()
    {
        $this->getClient()->run($this, 'pull');

        return $this;
    }

    /**
     * Update remote references.
     *
     * @param string $repository Repository to be pushed
     * @param string $refspec    Refspec for the push
     */
    public function push($repository = null, $refspec = null)
    {
        $command = 'push';

        if ($repository) {
            $command .= " $repository";
        }

        if ($refspec) {
            $command .= " $refspec";
        }

        $this->getClient()->run($this, $command);

        return $this;
    }

    /**
     * Get name of repository (top level directory).
     *
     * @return string
     */
    public function getName()
    {
        $name = rtrim($this->path, '/');

        if (strstr($name, DIRECTORY_SEPARATOR)) {
            $name = substr($name, strrpos($name, DIRECTORY_SEPARATOR) + 1);
        }

        return trim($name);
    }

    /**
     * Show a list of the repository branches.
     *
     * @return array List of branches
     */
    public function getBranches()
    {
        static $cache = [];

        if (array_key_exists($this->path, $cache)) {
            return $cache[$this->path];
        }

        $branches = $this->getClient()->run($this, 'branch');
        $branches = explode("\n", $branches);
        $branches = array_filter(preg_replace('/[\*\s]/', '', $branches));

        if (empty($branches)) {
            return $cache[$this->path] = $branches;
        }

        // Since we've stripped whitespace, the result "* (detached from "
        // and "* (no branch)" that is displayed in detached HEAD state
        // becomes "(detachedfrom" and "(nobranch)" respectively.
        if ((strpos($branches[0], '(detachedfrom') === 0) || ($branches[0] === '(nobranch)')) {
            $branches = array_slice($branches, 1);
        }

        return $cache[$this->path] = $branches;
    }

    /**
     * Return the current repository branch.
     *
     * @return mixed Current repository branch as a string, or NULL if in
     *               detached HEAD state.
     */
    public function getCurrentBranch()
    {
        $branches = $this->getClient()->run($this, 'branch');
        $branches = explode("\n", $branches);

        foreach ($branches as $branch) {
            if ($branch[0] === '*') {
                if ((strpos($branch, '* (detached from ') === 0) || ($branch === '* (no branch)')) {
                    return;
                }

                return substr($branch, 2);
            }
        }
    }

    /**
     * Check if a specified branch exists.
     *
     * @param string $branch Branch to be checked
     *
     * @return bool True if the branch exists
     */
    public function hasBranch($branch)
    {
        $branches = $this->getBranches();
        $status = in_array($branch, $branches);

        return $status;
    }

    /**
     * Create a new repository branch.
     *
     * @param string $branch Branch name
     */
    public function createBranch($branch)
    {
        $this->getClient()->run($this, "branch $branch");
    }

    /**
     * Create a new repository tag.
     *
     * @param string $tag Tag name
     */
    public function createTag($tag, $message = null)
    {
        $command = 'tag';

        if ($message) {
            $command .= " -a -m '$message'";
        }

        $command .= " $tag";

        $this->getClient()->run($this, $command);
    }

    /**
     * Show a list of the repository tags.
     *
     * @return array List of tags
     */
    public function getTags()
    {
        static $cache = [];

        if (array_key_exists($this->path, $cache)) {
            return $cache[$this->path];
        }

        $tags = $this->getClient()->run($this, 'tag');
        $tags = explode("\n", $tags);
        array_pop($tags);

        if (empty($tags[0])) {
            return $cache[$this->path] = null;
        }

        return $cache[$this->path] = $tags;
    }

    /**
     * Show the amount of commits on the repository.
     *
     * @return int Total number of commits
     */
    public function getTotalCommits($file = null)
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $command = "rev-list --count --all $file";
        } else {
            $command = "rev-list --all $file | wc -l";
        }

        $commits = $this->getClient()->run($this, $command);

        return trim($commits);
    }

    /**
     * Show the repository commit log.
     *
     * @return array Commit log
     */
    public function getCommits($file = null)
    {
        $command = 'log --pretty=format:"<item><hash>%H</hash><short_hash>%h</short_hash><tree>%T</tree><parents>%P</parents><author>%an</author><author_email>%ae</author_email><date>%at</date><commiter>%cn</commiter><commiter_email>%ce</commiter_email><commiter_date>%ct</commiter_date><message><![CDATA[%s]]></message></item>"';

        if ($file) {
            $command .= " $file";
        }

        $logs = $this->getPrettyFormat($command);

        foreach ($logs as $log) {
            $commit = new Commit();
            $commit->importData($log);
            $commits[] = $commit;

            foreach ($this->statistics as $statistic) {
                $statistic->addCommit($commit);
            }
        }

        $this->setCommitsHaveBeenParsed(true);

        return $commits;
    }

    /**
     * Show the data from a specific commit.
     *
     * @param string $commitHash Hash of the specific commit to read data
     *
     * @return array Commit data
     */
    public function getCommit($commitHash)
    {
        if (version_compare($this->getClient()->getVersion(), '1.8.4', '>=')) {
            $logs = $this->getClient()->run($this, "show --ignore-blank-lines -w -b --pretty=format:\"<item><hash>%H</hash><short_hash>%h</short_hash><tree>%T</tree><parents>%P</parents><author>%an</author><author_email>%ae</author_email><date>%at</date><commiter>%cn</commiter><commiter_email>%ce</commiter_email><commiter_date>%ct</commiter_date><message><![CDATA[%s]]></message><body><![CDATA[%b]]></body></item>\" $commitHash");
        } else {
            $logs = $this->getClient()->run($this, "show --pretty=format:\"<item><hash>%H</hash><short_hash>%h</short_hash><tree>%T</tree><parents>%P</parents><author>%an</author><author_email>%ae</author_email><date>%at</date><commiter>%cn</commiter><commiter_email>%ce</commiter_email><commiter_date>%ct</commiter_date><message><![CDATA[%s]]></message><body><![CDATA[%b]]></body></item>\" $commitHash");
        }
        $xmlEnd = strpos($logs, '</item>') + 7;
        $commitInfo = substr($logs, 0, $xmlEnd);
        $commitData = substr($logs, $xmlEnd);
        $logs = explode("\n", $commitData);
        array_shift($logs);

        // Read commit metadata
        $format = new PrettyFormat();
        $data = $format->parse($commitInfo);
        $commit = new Commit();
        $commit->importData($data[0]);

        if (empty($logs[1])) {
            $logs = explode("\n", $this->getClient()->run($this, 'diff '.$commitHash.'~1..'.$commitHash));
        }

        $commit->setDiffs($this->readDiffLogs($logs));

        return $commit;
    }

    /**
     * Read diff logs and generate a collection of diffs.
     *
     * @param array $logs Array of log rows
     *
     * @return array Array of diffs
     */
    public function readDiffLogs(array $logs)
    {
        $diffs = [];
        $lineNumOld = 0;
        $lineNumNew = 0;
        foreach ($logs as $log) {
            if ('diff' === substr($log, 0, 4)) {
                if (isset($diff)) {
                    $diffs[] = $diff;
                }

                $diff = new Diff();
                if (preg_match('/^diff --[\S]+ a\/?(.+) b\/?/', $log, $name)) {
                    $diff->setFile($name[1]);
                }
                continue;
            }

            if ('index' === substr($log, 0, 5)) {
                $diff->setIndex($log);
                continue;
            }

            if ('---' === substr($log, 0, 3)) {
                $diff->setOld($log);
                continue;
            }

            if ('+++' === substr($log, 0, 3)) {
                $diff->setNew($log);
                continue;
            }

            // Handle binary files properly.
            if ('Binary' === substr($log, 0, 6)) {
                $m = [];
                if (preg_match('/Binary files (.+) and (.+) differ/', $log, $m)) {
                    $diff->setOld($m[1]);
                    $diff->setNew("    {$m[2]}");
                }
            }

            if (! empty($log)) {
                switch ($log[0]) {
                    case '@':
                        // Set the line numbers
                        preg_match('/@@ -([0-9]+)/', $log, $matches);
                        $lineNumOld = $matches[1] - 1;
                        $lineNumNew = $matches[1] - 1;
                        break;
                    case '-':
                        $lineNumOld++;
                        break;
                    case '+':
                        $lineNumNew++;
                        break;
                    default:
                        $lineNumOld++;
                        $lineNumNew++;
                }
            } else {
                $lineNumOld++;
                $lineNumNew++;
            }

            if ($diff) {
                $diff->addLine($log, $lineNumOld, $lineNumNew);
            }
        }

        if (isset($diff)) {
            $diffs[] = $diff;
        }

        return $diffs;
    }

    /**
     * Get the current HEAD.
     *
     * @param $default Optional branch to default to if in detached HEAD state.
     * If not passed, just grabs the first branch listed.
     *
     * @return string the name of the HEAD branch, or a backup option if
     *                in detached HEAD state.
     */
    public function getHead($default = null)
    {
        $file = '';
        if (file_exists($this->getPath().'/.git/HEAD')) {
            $file = file_get_contents($this->getPath().'/.git/HEAD');
        } elseif (file_exists($this->getPath().'/HEAD')) {
            $file = file_get_contents($this->getPath().'/HEAD');
        }

        // Find first existing branch
        foreach (explode("\n", $file) as $line) {
            $m = [];
            if (preg_match('#ref:\srefs/heads/(.+)#', $line, $m)) {
                if ($this->hasBranch($m[1])) {
                    return $m[1];
                }
            }
        }

        // If we were given a default branch and it exists, return that.
        if ($default !== null && $this->hasBranch($default)) {
            return $default;
        }

        // Otherwise, return the first existing branch.
        $branches = $this->getBranches();
        if (! empty($branches)) {
            return current($branches);
        }

        // No branches exist - null is the best we can do in this case.
        return;
    }

    /**
     * Extract the tree hash for a given branch or tree reference.
     *
     * @param string $branch
     *
     * @return string
     */
    public function getBranchTree($branch)
    {
        $hash = $this->getClient()->run($this, "log --pretty=\"%T\" --max-count=1 $branch");
        $hash = trim($hash, "\r\n ");

        return $hash ?: false;
    }

    /**
     * Get the Tree for the provided folder.
     *
     * @param string $tree Folder that will be parsed
     *
     * @return Tree Instance of Tree for the provided folder
     */
    public function getTree($tree)
    {
        $tree = new Tree($tree, $this);
        $tree->initialize();

        return $tree;
    }

    /**
     * Get the Blob for the provided file.
     *
     * @param string $blob File that will be parsed
     *
     * @return Blob Instance of Blob for the provided file
     */
    public function getBlob($blob)
    {
        return new Blob($blob, $this);
    }

    /**
     * Blames the provided file and parses the output.
     *
     * @param string $file File that will be blamed
     *
     * @return array Commits hashes containing the lines
     */
    public function getBlame($file)
    {
        $blame = [];
        $logs = $this->getClient()->run($this, "blame -s $file");
        $logs = explode("\n", $logs);

        $i = 0;
        $previousCommit = '';
        foreach ($logs as $log) {
            if ($log == '') {
                continue;
            }

            preg_match_all("/([a-zA-Z0-9^]{8})\s+.*?([0-9]+)\)(.+)/", $log, $match);

            $currentCommit = $match[1][0];
            if ($currentCommit != $previousCommit) {
                ++$i;
                $blame[$i] = ['line' => '', 'commit' => $currentCommit];
            }

            $blame[$i]['line'] .= PHP_EOL.$match[3][0];
            $previousCommit = $currentCommit;
        }

        return $blame;
    }

    /**
     * Get the current Repository path.
     *
     * @return string Path where the repository is located
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the current Repository path.
     *
     * @param string $path Path where the repository is located
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get the current Client instance.
     *
     * @return Client Client instance
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the Client.
     *
     * @param Client $path Client instance
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    public function run($command)
    {
        return $this->client->run($this, $command);
    }

    /**
     * Get and parse the output of a git command with a XML-based pretty format.
     *
     * @param string $command Command to be run by git
     *
     * @return array Parsed command output
     */
    public function getPrettyFormat($command)
    {
        $output = $this->getClient()->run($this, $command);
        $format = new PrettyFormat();

        return $format->parse($output);
    }

    public function parseCommitishPathParam($commitishPath)
    {
        $commitish = null;
        $path = null;

        $slashPosition = strpos($commitishPath, '/');
        if (strlen($commitishPath) >= 40 &&
            ($slashPosition === false ||
             $slashPosition === 40)) {
            // We may have a commit hash as our commitish.
            $hash = substr($commitishPath, 0, 40);
            if ($this->hasCommit($hash)) {
                $commitish = $hash;
            }
        }

        if ($commitish === null) {
            $branches = $this->getBranches();

            $tags = $this->getTags();
            if ($tags !== null && count($tags) > 0) {
                $branches = array_merge($branches, $tags);
            }

            $matchedBranch = null;
            $matchedBranchLength = 0;
            foreach ($branches as $branch) {
                if (strpos($commitishPath, $branch) === 0 &&
                    strlen($branch) > $matchedBranchLength) {
                    $matchedBranch = $branch;
                    $matchedBranchLength = strlen($matchedBranch);
                }
            }

            if ($matchedBranch !== null) {
                $commitish = $matchedBranch;
            } else {
                // We may have partial commit hash as our commitish.
                $hash = $slashPosition === false ? $commitishPath : substr($commitishPath, 0, $slashPosition);
                if ($this->hasCommit($hash)) {
                    $commit = $this->getCommit($hash);
                    $commitish = $commit->getHash();
                } else {
                    throw new EmptyRepositoryException('This repository is currently empty. There are no commits.');
                }
            }
        }

        $commitishLength = strlen($commitish);
        $path = substr($commitishPath, $commitishLength);
        if (strpos($path, '/') === 0) {
            $path = substr($path, 1);
        }

        return [$commitish, $path];
    }

    public function extractRef($branch = '', $tree = '')
    {
        $branch = trim($branch, '/');
        $tree = trim($tree, '/');
        $input = $branch.'/'.$tree;

        // If the ref appears to be a SHA, just split the string
        if (preg_match('/^([[:alnum:]]{40})(.+)/', $input, $matches)) {
            $branch = $matches[1];
        } else {
            // Otherwise, attempt to detect the ref using a list of the project's branches and tags
            $validRefs = array_merge((array) $this->getBranches(), (array) $this->getTags());
            foreach ($validRefs as $key => $ref) {
                if (! preg_match(sprintf('#^%s/#', preg_quote($ref, '#')), $input)) {
                    unset($validRefs[$key]);
                }
            }

            // No exact ref match, so just try our best
            if (count($validRefs) > 1) {
                preg_match('/([^\/]+)(.*)/', $input, $matches);
                $branch = preg_replace('/^\/|\/$/', '', $matches[1]);
            } else {
                // Extract branch name
                $branch = array_shift($validRefs);
            }
        }

        return [$branch, $tree];
    }
}

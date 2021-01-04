<?php

use Robo\Exception\AbortTasksException;

/**
 * cryptotrader
 * Copyright (C) 2018 Domingo Oropeza
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class RoboFile extends \Robo\Tasks
{

    /**
     * @var string
     */
    var $newVersion;
    var $releaseType = 'patch';


    var $options = [];

    /**
     * Task to create the changelog of the project
     * @param string $repository name
     * @return \Robo\Result|\Robo\ResultData
     * @throws Exception
     */
    public function makeChangelog($repository)
    {
        $changelog = $this->taskChangelog();

        list($parentRevision, $currentRevision) = $this->getParentAndCurrentRevision();

        $this->stopOnFail(true);
        $changes = $this->getGitChangeLog($repository, $parentRevision, $currentRevision);
        if (empty($changes)) {
            return Robo\Result::cancelled('No new commits for release an update');
        }
        if (preg_match('/(BREAKING CHANGE:)|(^ \* feat)/m', $changes, $regs)) {
            switch ($regs[0]) {
                case 'BREAKING CHANGE:':
                    $this->releaseType = 'major';
                    break;
                case ' * feat':
                    $this->releaseType = 'minor';
                    break;
                // by default is patch
            }
        }
        $newVersion = $this->newVersion = $this->generateNextVersion(
            $this->setVersionFromLastRevision($parentRevision)
        );
        $changes = ($changes) ? $changes : ' * Minor changes, for more details see our [commit history](https://github.com/'.$repository.'/compare/master...'.$newVersion.'/bugfixes)';

        return $changelog->filename('CHANGELOG.md')
            ->version("[".$newVersion."](https://github.com/$repository/tree/".$newVersion.") (".date('Y-m-d').")")
            ->setBody($changes)
            ->run();
    }

    /**
     * @param $revision
     * @return string
     */
    private function setVersionFromLastRevision($revision)
    {
        $version = strstr($revision, '/');

        return ($version !== false) ? ltrim($version, '/') : '0.0.0';
    }

    /**
     * Task to publish a release similar to git-flow
     * @param $repository
     * @param string $label
     * @param string $origin
     * @throws Exception
     */
    public function publishRelease($repository, $label = 'none', $origin = '')
    {
        if (!in_array($label, ['rc', 'beta', 'alpha', 'none'])) {
            throw new \InvalidArgumentException('Release label, can be rc, beta, alpha or none');
        }
        $this->options['label'] = $label;

        // changelog generation
        $result = $this->makeChangelog($repository);
        if (null !== $result && $result->wasCancelled()) {
            $this->say($result->getMessage());

            return;
        }

        $newVersion = $this->newVersion;
        $releaseBranch = 'release/'.$newVersion;

        // git-flow start
        $this->taskGitStack()->checkout('-b '.$releaseBranch)->run();

        // Continue the git flow
        $filename = 'CHANGELOG.md';
        $commitMessage = 'docs: updated project files for new release.';
        $this->taskGitStack()->add($filename)
            ->commit($commitMessage)
            ->checkout('-b robo-master upstream/master')
            ->merge($releaseBranch)->tag($newVersion)
            ->checkout('-b robo-develop upstream/develop')->merge($releaseBranch)
            ->run();

        // finish git-flow with delete release branch
        $this->taskGitStack()->exec('branch -D '.$releaseBranch)->run();

        // make changelog for gh-pages
        // $this->taskGitStack()->checkout('-b robo-gh-pages upstream/gh-pages')->checkout('robo-develop ' . $filename)->run();
        // $this->_exec('mv -f ' . $filename . ' _includes/' . $filename);
        // $this->taskGitStack()->add('_includes/' . $filename)->commit($commitMessage, '--no-gpg-sign')->run();

        // publish the changes
        if ($origin) {
            $this->taskGitStack()
                //->push($origin, 'robo-gh-pages:gh-pages')
                ->push($origin, 'robo-develop:develop')
                ->push($origin, 'robo-master:master')
                ->push($origin, $newVersion)
                ->run();
        }
    }

    /**
     * Generate the next semantic version
     * @param $currentVersion
     * @return string
     * @throws Exception
     */
    private function generateNextVersion($currentVersion)
    {
        $type = $this->releaseType;

        $label = isset($this->options['label']) ? $this->options['label'] : 'none';

        // Type validation
        $validTypes = ['patch', 'minor', 'major'];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException(
                'The option [type] must be one of: {'.implode(
                    $validTypes,
                    ', '
                )."}, \"$type\" given"
            );
        }

        $versionRegex = '(?:(\d+\.\d+\.\d+)(?:(-)([a-zA-Z]+)(\d+)?)?)';
        if (!preg_match('#^'.$versionRegex.'$#', $currentVersion)) {
            throw new \Exception(
                'Current version format is invalid ('.$currentVersion.'). It should be major.minor.patch'
            );
        }

        $matches = null;
        preg_match('$'.$versionRegex.'$', $currentVersion, $matches);
        // if last version is with label
        if (count($matches) > 3) {
            list($major, $minor, $patch) = explode('.', $currentVersion);
            $patch = substr($patch, 0, strpos($patch, '-'));

            if ($label != 'none') {
                // increment label
                $labelVersion = '';
                if (array_key_exists(3, $matches)) {
                    $oldLabel = $matches[3];
                    $labelVersion = 2;

                    // if label is new clear version
                    if ($label !== $oldLabel) {
                        $labelVersion = false;
                    } else {
                        if (array_key_exists(4, $matches)) {
                            // if version exists increment it
                            $labelVersion = intval($matches[4]) + 1;
                        }
                    }
                }

                return implode([$major, $minor, $patch], '.').'-'.$label.$labelVersion;
            }

            return implode([$major, $minor, $patch], '.');
        }

        list($major, $minor, $patch) = explode('.', $currentVersion);
        // Increment
        switch ($type) {
            case 'major':
                $major += 1;
                $patch = $minor = 0;
                break;
            case 'minor':
                $minor += 1;
                $patch = 0;
                break;
            default:
                $patch += 1;
                break;
        }

        // new label
        if ($label != 'none') {
            return implode([$major, $minor, $patch], '.').'-'.$label;
        }

        return implode([$major, $minor, $patch], '.');
    }

    /**
     * @return array
     * @throws AbortTasksException
     */
    private function getParentAndCurrentRevision(): array
    {
        $parentRevision = 'master';
        $currentRevision = 'develop';
        if (!$this->taskGitStack()->printOutput(false)->exec('remote get-url upstream')->run()->getMessage()) {
            throw new AbortTasksException('Upstream branch is not defined');
        }
        $this->taskGitStack()->exec('fetch --all -t')->run();
        $lastTag = $this->taskGitStack()->printOutput(false)->exec('describe --abbrev=0 --tags')
            ->run()->getMessage();
        if ($lastTag) {
            // if tags exist check against the last one
            $parentRevision = 'tags/'.trim($lastTag);
        } else {
            $fromHash = $this->taskGitStack()->printOutput(false)
                ->exec("ls-remote upstream $parentRevision")->run()->getMessage();
            $fromHash = str_replace("\trefs/heads/master", '', $fromHash);
            if (!$fromHash) {
                // master branch does not exist, let's go against first parent as first time ever revision check
                $message = $this->taskGitStack()->printOutput(false)
                    ->exec("rev-list --max-parents=0 HEAD")->run()->getMessage();
                $parentRevision = trim($message);
                $currentRevision = 'HEAD';
            }
        }

        return array($parentRevision, $currentRevision);
    }

    /**
     * @param string $repository
     * @param $parentRevision
     * @param $currentRevision
     * @return string
     */
    private function getGitChangeLog(string $repository, $parentRevision, $currentRevision): string
    {
        $command = 'log --pretty=" * %s ([%h](https://github.com/'.$repository.'/commit/%h))"  '.$parentRevision.'..'.$currentRevision.' --grep="^fix" --grep="^feat" --grep="^perf"';
        $result = $this->taskGitStack()->printOutput(false)->exec($command)->run()->getMessage();

        return $result;
    }

}
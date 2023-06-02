<?php

declare(strict_types=1);

namespace FixerApiErrorHandlingProcessor;

use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class Component extends BaseComponent
{
    /**
     * @throws \Keboola\Component\UserException
     */
    protected function run(): void
    {
        $finder = new Finder();
        $finder->notName('*.manifest')->in($this->getDataDir() . '/in/tables')->depth(0);
        foreach ($finder as $sourceTable) {
            $this->processFile($sourceTable, $this->getDataDir() . '/out/tables');
        }

        $finder = new Finder();
        $finder->name('*.manifest')->in($this->getDataDir() . '/in/tables')->depth(0);
        foreach ($finder as $sourceTableManifest) {
            $this->moveFile($sourceTableManifest, $this->getDataDir() . '/out/tables');
        }
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }

    private function moveFile(SplFileInfo $sourceFile, string $destinationFolder): void
    {
        $process = new Process(
            ['mv', $sourceFile->getPathname(), $destinationFolder . '/' . $sourceFile->getBasename()]
        );
        $process->mustRun();
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    private function processFile(SplFileInfo $sourceFile, string $destinationFolder): void
    {
        $source = fopen($sourceFile->getPathname(), 'r');
        if ($source === false) {
            throw new RuntimeException(sprintf('Failed to open source file: "%s"', $sourceFile->getPathname()));
        }

        $header = fgets($source);
        if (!$header) {
            throw new RuntimeException('Cannot obtain header row from CSV');
        }
        $columns = explode(',', trim(str_replace('"', '', $header)));
        if (in_array('error_code', $columns)) {
            $row = fgets($source);
            if (!$row) {
                throw new RuntimeException('Cannot obtain row from CSV');
            }
            $values = explode(',', trim(str_replace('"', '', $row)));
            $data = [];
            foreach ($columns as $key => $column) {
                $data[$column] = $values[$key];
            }

            if ($data['error_code'] !== '') {
                throw new UserException(sprintf(
                    '%d: Fixer API error%s: %s',
                    $data['error_code'],
                    isset($data['error_type']) ? sprintf(' "%s"', $data['error_type']) : '',
                    $data['error_info']
                ));
            }
        }

        $this->moveFile($sourceFile, $destinationFolder);
    }
}

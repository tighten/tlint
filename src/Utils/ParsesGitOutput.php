<?php

namespace Tighten\Utils;

class ParsesGitOutput
{
    public static function parseFilesFromGitDiffOutput(string $output): iterable
    {
        if ($output === '') {
            return [];
        }

        foreach (explode(PHP_EOL, trim($output)) as $relativeFilePath) {
            yield getcwd() . '/' . $relativeFilePath;
        }
    }
}

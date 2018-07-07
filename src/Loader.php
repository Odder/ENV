<?php

namespace ENV;

use ENV\Exception\InvalidFileException;
use ENV\Exception\InvalidKeyException;

/**
 * Class Loader
 * @package ENV
 */
class Loader
{
    protected $file;
    public $variables;

    /**
     * Loader constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->file = $this->getFilePath($path);
    }

    /**
     *
     */
    public function load()
    {
        foreach ($this->readLines() as $line) {
            if (!$this->isSetter($line)) {
                list($key, $value) = $this->parseLine($line);
                $this->setVariable($key, $value);
            }
        }

        $this->resolveVariables();
    }

    /**
     * @param $line
     * @return array
     */
    protected function parseLine($line): array
    {
        return array_map('trim', explode('=', $line, 2));
    }

    /**
     * @param $path
     * @return string
     */
    protected function getFilePath($path): string
    {
        return $path . DIRECTORY_SEPARATOR . '.env';
    }

    /**
     * @return array
     */
    protected function readLines(): array
    {
        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', 1);
        $lines = file($this->file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        ini_set('auto_detect_line_endings', $autodetect);

        return $lines;
    }

    /**
     * @param $line
     * @return bool
     */
    protected function isSetter($line): bool
    {
        return $line ? False : True;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function setVariable($key, $value)
    {
        if (!preg_match('/^[A-Z][A-Z0-9_]+$/', $key)) {
            throw new InvalidKeyException("'${key}' is not a a proper key format.");
        }

        $this->variables[$key] = $this->sanitiseValue($value);
    }

    /**
     * @param $value
     * @return mixed|null|string|string[]
     */
    protected function sanitiseValue($value)
    {
        if (isset($value[0]) && $value[0] == '\'' || $value[0] == '"') {
            $quote = $value[0];

            $pattern = "/^${quote}((?:[^${quote}\\\\]*|\\\\\\\\|\\\\${quote})*)${quote}.*$/mx";
            $value = preg_replace($pattern, '$1', $value);
            $value = str_replace("\\${quote}", $quote, $value);
            $value = str_replace('\\\\', '\\', $value);
        }
        else {
            $value = explode(' #', $value, 2)[0];
            $value = $value[0] !== '#' ? $value : '';

            if (preg_match('/\s/', $value) > 0 ) {
                throw new InvalidFileException('.env file requires you to put strings with spaces in quotes');
            }
        }

        return $value;
    }

    /**
     *
     */
    protected function resolveVariables()
    {
        $stack = [];
        $previous = '';
        $variables = $this->variables;
        $pattern = '/\$\{([a-zA-Z0-9_.]+)\}/';

        foreach ($variables as $key => $value) {
            $stack[] = [$key, $value];
        }

        while (list($key, $value) = array_shift($stack)) {
            if ($previous == $key) {
                throw new InvalidFileException('variables dependencies not met');
            }
            $previous = $key;
            $value = preg_replace_callback(
                $pattern,
                function ($matches) use ($variables, &$stack, $key, $value, $pattern) {
                    if ($variables[$matches[1]] === null) {
                        throw new InvalidFileException('Variable missing!');
                    }
                    else {
                        $newValue = $variables[$matches[1]];
                        if (preg_match($pattern, $newValue) > 0) {
                            $stack[] = [$key, $value];
                            return $value;
                        }
                        else {
                            return $newValue;
                        }
                    }
                },
                $value
            );

            $variables[$key] = $value;
            $this->setVariable($key, $value);
        }
    }

}

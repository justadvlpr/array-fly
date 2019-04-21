<?php

namespace ArrayFly;

use ArrayFly\Exception\FileLocationException;
use ArrayFly\Exception\InvalidCombinationException;

class File
{
    private const ARRAY_SEARCH_PATTERN = "/(\"|'){key}(\"|') => (\"|')(.*)(\"|')/";

    /** @var string */
    private $file;

    /** @var string */
    private $fileContent;

    /**
     * @param string|null $fileLocation The file location path.
     *
     * @return File
     *
     * @throws FileLocationException
     */
    public function __construct(string $fileLocation = null)
    {
        if (empty($fileLocation)) {
            throw new FileLocationException('File location path is empty');
        }

        if (!file_exists($fileLocation)) {
            throw new FileLocationException('File does not exist');
        }

        $this->file = $fileLocation;

        $this->fileContent = file_get_contents($this->file);

        return $this;
    }

    /**
     * Saves the new array content into the file.
     *
     * @return $this
     */
    public function save()
    {
        file_put_contents($this->file, $this->fileContent);

        return $this;
    }

    /**
     * Sets the array values
     *
     * @param string $key
     * @param string $value
     * @param bool $save Default is false, set it to true if you want to save the file with the new value.
     * @param bool $strictCheck By default this check is turned off, if your array uses
     * double quotes it will be converted to single quote, if you want to keep
     * the double quotes, set this option to true.
     *
     * @return File
     *
     * @throws InvalidCombinationException
     */
    public function setValue(string $key, string $value, bool $save = false, $strictCheck = false): self
    {
        $matches = $this->getMatches($key);

        // escape bad user input
        $value = addslashes(htmlentities($value));

        $defaultReplace = "'{$key}' => '{$value}'";

        if ($strictCheck) {
            if (
                isset($matches[1]) &&
                isset($matches[2]) &&
                isset($matches[3]) &&
                isset($matches[5])
            ) {
                $combination = $matches[1] . $matches[2] . $matches[3] . $matches[5];
            } else {
                $combination = '';
            }

            switch ($combination) {
                case '\'\'\'\'':
                    $replace = $defaultReplace;
                    break;

                case '""""':
                    $replace = "\"{$key}\" => \"{$value}\"";
                    break;

                case '""\'\'':
                    $replace = "\"{$key}\" => '{$value}'";
                    break;

                case '\'\'""':
                    $replace = "'{$key}' => \"{$value}\"";
                    break;

                default:
                    throw new InvalidCombinationException('No combination found');
            }
        }

        $this->fileContent = preg_replace(
            str_replace('{key}', $key, self::ARRAY_SEARCH_PATTERN),
            $replace ?? $defaultReplace,
            $this->fileContent
        );

        if ($save) {
            $this->save();
        }

        return $this;
    }

    /**
     * Gets the array value.
     *
     * @param string $key
     *
     * @return string Returns the value associated with the key, if
     * the the key element is not found an empty string will be returned instead.
     */
    public function getValue(string $key): string
    {
        return $this->getMatches($key)[4] ?? '';
    }

    /**
     * Matches an array containing 'key' => 'value'
     * ( single quote and double quote check is performed as well )
     *
     * @param string $key
     *
     * @return array
     */
    private function getMatches(string $key): array
    {
        $matches = [];

        preg_match(
            str_replace('{key}', $key, self::ARRAY_SEARCH_PATTERN),
            $this->fileContent,
            $matches
        );

        return $matches;
    }
}
<?php

namespace ArrayFly;

use ArrayFly\Exception\FileLocationException;
use ArrayFly\Exception\InvalidCombinationException;
use ArrayFly\Exception\NoMatchFoundException;

class ArrayFly
{
    private const ARRAY_SEARCH_PATTERN = "/(\"|'){key}(\"|') => (\"|')(.*)(\"|')/";

    private string $file;

    private string $fileContent;

    /**
     * @param string|null $fileLocation The file location path.
     *
     * @return ArrayFly
     *
     * @throws FileLocationException
     */
    public function __construct(string $fileLocation = null)
    {
        if (empty($fileLocation)) {
            throw new FileLocationException('File location path cannot be empty.');
        }

        if (!file_exists($fileLocation)) {
            throw new FileLocationException(
                sprintf("The file '%s' was not found.", $fileLocation)
            );
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
    public function save(): self
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
     * @return ArrayFly
     *
     * @throws InvalidCombinationException
     * @throws NoMatchFoundException
     */
    public function setValue(string $key, string $value, bool $save = false, $strictCheck = false): self
    {
        $matches = $this->getMatches($key);

        if (empty($matches)) {
            throw new NoMatchFoundException(
                sprintf("No matches found for the current key: '%s'.", $key)
            );
        }

        // escape bad code
        $value = addslashes(htmlentities($value));

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
                    $replace = "'{$key}' => '{$value}'";
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
                    throw new InvalidCombinationException('No combination found.');
            }
        } else {
            $replace = "'{$key}' => '{$value}'";
        }

        $replaceContent = preg_replace(
            str_replace('{key}', $key, self::ARRAY_SEARCH_PATTERN),
            $replace,
            $this->fileContent
        );

        if ($replaceContent === null) {
            return $this;
        }

        $this->fileContent = $replaceContent;

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
        return ($this->getMatches($key)[4] ?? '');
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
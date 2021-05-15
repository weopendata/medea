<?php

namespace App\Import;

use App\Helpers\FixCsvStreamFilter;
use League\Csv\Reader;

class Csv
{
    /**
     * The Reader object that wraps the CSV contents
     *
     * @var $csvReader
     */
    protected $csvReader;

    /**
     * The position of where the Reader stands in the file (= the iterator in the Reader object)
     *
     * @var $lineNumber
     */
    protected $lineNumber = 0;

    /**
     * The line to start reading from, which can differ from 0
     *
     * @var $startLine
     */
    protected $startLine;

    /**
     * The max amount of lines to read
     *
     * @var $readMaxLines
     */
    protected $readMaxLines;

    /**
     * The amount of data lines already read, so excluding the header line
     *
     * @var $linesRead
     */
    protected $linesRead;

    /**
     * The path to the CSV file
     *
     * @var $filePath
     */
    protected $filePath;

    /**
     * The delimiter of the CSV
     *
     * @var $delimiter
     */
    protected $delimiter;

    /**
     * Create a Csv DataSource instance, don't forget to add these in extending classes
     *
     */
    public function __construct($filePath)
    {
        stream_filter_register('fixCsvQuotes', FixCsvStreamFilter::class);

        $this->filePath = $filePath;
        $this->delimiter = ',';
        $this->readMaxLines = 600;
        $this->linesRead = 0;

        $this->csvReader = Reader::createFromPath($this->filePath);
        $this->csvReader->appendStreamFilter('fixCsvQuotes');
        $this->csvReader->setDelimiter($this->delimiter ?? ',');

        // Assume that there are headers present in the CSV file
        $this->headers = $this->csvReader->fetchOne(0);
        $this->lineNumber = 1;
    }

    public function getNext()
    {
        $rawData = $this->csvReader->fetchOne($this->lineNumber);

        $this->lineNumber++;
        $this->linesRead++;

        $hasNext = !empty($rawData);

        if (!$hasNext) {
            return [];
        }

        // Apply the headers to the company array to make it an associative array
        foreach ($this->headers as $index => $header) {
            $header = trim($header);

            $value = @$rawData[$index];

            if (!empty($value)) {
                $value = trim($value);
            }
            
            $data[$header] = $value;
        }

        return $data;
    }

    /**
     * Return the amount of lines we've read from the file
     *
     * @return integer
     */
    public function getLinesRead()
    {
        return $this->linesRead;
    }

    /**
     * Return the max amount of lines we can read in 1 processing operation
     *
     * @return integer
     */
    public function getMaxLinesToRead()
    {
        return $this->readMaxLines;
    }

    /**
     * Return the line number of the line from which we're returning the data from
     *
     * @return integer
     */
    public function getIndex()
    {
        return $this->lineNumber - 1;
    }
}

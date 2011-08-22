<?php
/**
 * @author OskarStark <oskarstark@googlemail.com>
 */
class CsvParser
{

    /**
    * @return array
    * @var $file string
    * @var $delimiter string
    * @var $load_first_row bool
    * @var $columns array
    *
    * CSV-Example:     "ObjectId";"StartDate";"EndDate";"EndTime"
    *                  19123;"26.04.2011";"19.06.2011";"21:00"
    *
    *
    *
    * You have to use the same order from left to right, like the file.
    * If you use:      $columns = array(
    *                              'object_id',
    *                              'start_date',
    *                              'end_date',
    *                              'end_time'
    *                          );
    *
    * You'll get:      $result = array(
    *                              0 => array (
    *                                      'object_id' => 'ObjectId',
    *                                      'start_date' => 'StartDate',
    *                                      'end_date' => 'EndDate',
    *                                      'end_time' => 'EndTime'
    *                                  ),
    *                              1 => array(
    *                                      'object_id' => 19123,
    *                                      'start_date' => '26.04.2011',
    *                                      'end_date' => '19.06.2011',
    *                                      'end_time' => '21:00'
    *                                  )
    *                           );
    *
    * If you set $load_first_row to false, $result[0] will be removed. 
    */
    public function csv2arrayByColumns($file, $delimiter = ';', $load_first_row = true, array $columns)
    {
        $rows = $this->getCsvDataByFile($file, $delimiter);
        if (empty($columns))
        {
            throw new Exception('Columns needed');
        }

        $load_first_row ? 0 : 1;

        $results = array();
        $columns_validated = false;

        foreach ($rows as $key => $row)
        {
            /* ignore first line of csv file */
            if (0 == $key && false == $load_first_row)
            {
                continue;
            }

            /* check if count columns == count values in row */
            self::validateColumns($columns, $row);

            foreach ($columns as $columns_key => $column)
            {
                $results[$key][$column] = $row[$columns_key];
            }
        }

        /* refresh array index */
        $results = array_merge($results);

        return $results;
    }


    /**
    * @return array
    * @var $file string
    * @var $delimiter string
    *
    * CSV-Example:     "ObjectId";"StartDate";"EndDate";"EndTime"
    *                  19123;"26.04.2011";"19.06.2011";"21:00"
    *
    *
    * You'll get:      $result = array(
    *                              0 => array(
    *                                      'ObjectId' => 19123,
    *                                      'StartDate' => '26.04.2011',
    *                                      'EndDate' => '19.06.2011',
    *                                      'EndTime' => '21:00'
    *                                  )
    *                           );
    *
    * NOTE: The first row (header) will be removed every time !!
    */
    public function csv2arrayByHeader($file, $delimiter = ';')
    {
        $rows = $this->getCsvDataByFile($file, $delimiter);

        $columns = array();

        foreach ($rows as $key => $row)
        {
            if ($key == 0)
            {
                $columns = $row;
                continue;
            }

            /* check if count columns == count values in row */
            self::validateColumns($columns, $row);

            foreach ($columns as $columns_key => $column)
            {
                $results[$key][$column] = $row[$columns_key];
            }
        }

        /* refresh array index */
        $results = array_merge($results);

        return $results;

    }

    /**
     * @return bool
     * @var $columns array
     * @var $row array
     */
    static protected function validateColumns(array $columns, array $row)
    {
        static $validated = false;
        if (false === $validated)
        {
            if (count($row) != count($columns))
            {
                throw new Exception('Number of columns equal to number of data in row!');
            }
            else
            {
                $validated = true;
            }
        }
    }

    /**
    * @return array
    */
    protected function getCsvDataByFile($file, $delimiter = ';')
    {
        if (!file_exists($file))
        {
            throw new Exception('File doesn\'t exists: ' . $file);
        }

        $handle = fopen($file, "r");

        $rows = array();
        while (false !== ($row = fgetcsv($handle, 1000, $delimiter)))
        {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }
}
<?php
namespace Openstore\Store\Renderer;

use Soluble\FlexStore\Renderer\RowRendererInterface;
use ArrayObject;
use DateTime;

class DateMinRenderer implements RowRendererInterface
{
    /**
     *
     * @var string
     */
    protected $date_column;
    
    /**
     *
     * @var string
     */
    protected $date_format;
    
    
    /**
     *
     * @var DateTime
     */
    protected $min_date;
    
    
    /**
     *
     * @var string
     */
    protected $formatted_min_date;
    

    /**
     *
     * @param string $date_column name of the column containg the date
     * @param string $row_date_format date format returned by the store 'Y-m-d' (default) or 'Y-m-d H:i:s'...
     * @param DateTime $min_date by default now
     */
    public function __construct($date_column, $row_date_format = 'Y-m-d', DateTime $min_date = null)
    {
        $this->date_column = $date_column;
        $this->date_format = $row_date_format;
        if ($min_date === null) {
            $min_date = new DateTime();
        }
        $this->min_date = $min_date;
        
        $fmd = $this->min_date->format($row_date_format);
        
        // Actually shoul never send an exception,
        // no real way to check if formatting works
        if (!$fmd) {
            throw new \Exception(__METHOD__ . " Cannot format minimum date according to format '{$this->date_format}'.");
        }
        $this->formatted_min_date = $fmd;
    }
    
    
    
    /**
     *
     * @param ArrayObject $row
     */
    public function apply(ArrayObject $row)
    {
        if (!$row->offsetExists($this->date_column)) {
            throw new \Exception(__METHOD__ . " Date column '{$this->date_column} does not exists in row.");
        }

        $date = $row[$this->date_column];
        if ($date != '') {
            $dt = DateTime::createFromFormat($this->date_format, $date);
            if (!$dt) {
                throw new \Exception(__METHOD__ . " Date '$date' does not honour date format '{$this->date_format}'");
            }
           
            $now = new DateTime();
            
            if ($dt < $this->min_date) {
                $row[$this->date_column] = $this->formatted_min_date;
            }
        }
    }
    
    
    /**
     * Return the list of columns required in order to use this renderer
     * @return array
     */
    public function getRequiredColumns()
    {
        return array($this->date_column);
    }
}

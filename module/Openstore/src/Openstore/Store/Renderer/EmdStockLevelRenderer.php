<?php
namespace Openstore\Store\Renderer;

use Soluble\FlexStore\Renderer\RowRendererInterface;
use ArrayObject;

class EmdStockLevelRenderer implements RowRendererInterface
{

    /**
     * Specific cases
     */
    const STOCK_ON_REQUEST_ONLY = "STOCK_ON_REQUEST_ONLY";
    const STOCK_LEVEL_UNDETERMINABLE = "STOCK_LEVEL_UNDETERMINABLE";
    const STOCK_AVAILABLE_UPON_ACCEPTANCE = "STOCK_AVAILABLE_UPON_ACCEPTANCE";
    const STOCK_UPON_AVAILABLE_QTY = "STOCK_UPON_AVAILABLE_QTY";

    /**
     * Stock levels
     */
    const STOCK_FULL   = "ON_STOCK_FULL";
    const STOCK_HIGH   = "ON_STOCK_HIGH";
    const STOCK_NORMAL = "ON_STOCK_NORMAL";
    const STOCK_LOW    = "ON_STOCK_LOW";
    const STOCK_EMPTY  = "NO_STOCK";

    /**
     * @var string
     */
    protected $stock_column;

    /**
     * @var string
     */
    protected $stock_level_column;

    /**
     * @var string$avg_sale_qty
     */
    protected $pricelist_column;

    /**
     * @var string
     */
    protected $avg_sale_column;

    /**
     * @var bool
     */
    protected $columnsChecked = false;

    /**
     * @var bool
     */
    protected $skipNullValues = false;

    /**
     * EmdStockLevelRenderer constructor.
     * @param $stock_level_column column name that will contain the stock level
     * @param $stock_column column name of the stock information on which stock level is computed
     * @param $avg_sale_column column name of the average sales column
     * @param $pricelist_column column name containing the pricelist reference
     */
    public function __construct($stock_level_column, $stock_column, $avg_sale_column, $pricelist_column)
    {
        $this->stock_level_column = $stock_level_column;
        $this->stock_column = $stock_column;
        $this->pricelist_column = $pricelist_column;
        $this->avg_sale_column = $avg_sale_column;
    }


    /**
     *
     * @param ArrayObject $row
     */
    public function apply(ArrayObject $row)
    {
        if (!$this->columnsChecked) {
            if (!$row->offsetExists($this->stock_column)) {
                throw new \Exception(__METHOD__ . " stock_column '{$this->stock_column}' does not exists in row.");
            } elseif (!$row->offsetExists($this->stock_level_column)) {
                throw new \Exception(__METHOD__ . " stock_level_column '{$this->stock_level_column}' does not exists in row.");
            } elseif (!$row->offsetExists($this->pricelist_column)) {
                throw new \Exception(__METHOD__ . " pricelist_column '{$this->pricelist_column}' does not exists in row.");
            } elseif (!$row->offsetExists($this->avg_sale_column)) {
                throw new \Exception(__METHOD__ . " avg_sale_column '{$this->avg_sale_column}' does not exists in row.");
            }
            $this->columnsChecked = true;
        }

        $pricelist = $row[$this->pricelist_column];
        $stock = $row[$this->stock_column];
        $avg_sale_qty = $row[$this->avg_sale_column];

        if ($this->skipNullValues && $stock === null) {
            $level = null;
        } else {
            switch ($pricelist) {
                case '100F':
                case '120F':
                    $level = ($stock < 1) ? self::STOCK_EMPTY : self::STOCK_UPON_AVAILABLE_QTY;
                    break;

                case '100U':
                case '120U':
                case '200U':
                case '100U':
                    $level = self::STOCK_ON_REQUEST_ONLY;
                    break;

                case '100B':
                case '120B':
                    if ($stock < 1) {
                        $level = self::STOCK_EMPTY;
                    } elseif ($avg_sale_qty < 1) {
                        // Undeterminable because average is not known
                        $level = self::STOCK_LEVEL_UNDETERMINABLE;
                    } elseif ($stock >= (2 * $avg_sale_qty)) {
                        // If stock > 2 * average then 'green light'
                        $level = self::STOCK_FULL;
                    } elseif ($stock <= $avg_sale_qty) {
                        // If stock > average, then 'red light'
                        $level = self::STOCK_EMPTY;
                    } else {
                        // Stock between average and 2xavg, then 'orange light'
                        // Conditional approval based on availability for other territories
                        $level = self::STOCK_AVAILABLE_UPON_ACCEPTANCE;
                    }
                    break;

                default:
                    // All regular pricelists like BE, FR, NL, ES, DE, AT,...
                    if ($stock < 1) {
                        $level = self::STOCK_EMPTY;
                    } elseif ($avg_sale_qty < 1) {
                        $level = self::STOCK_LEVEL_UNDETERMINABLE;
                    } elseif ($stock >= $avg_sale_qty) {
                        // if greater than average, then full stock [****]
                        $level = self::STOCK_FULL;
                    } elseif ($stock >= ($avg_sale_qty * 2 / 3)) {
                        // if greater than 2/3 average, then high stock [*** ]
                        $level = self::STOCK_HIGH;
                    } elseif ($stock >= ($avg_sale_qty / 3)) {
                        // if greater than 1/3 average, then normal stock [**  ]
                        $level = self::STOCK_NORMAL;
                    } else {
                        // if lower than 1/3 average, then low stock [*   ]
                        $level = self::STOCK_LOW;
                    }
            }
        }

        $row[$this->stock_level_column] = $level;
    }

    /**
     * Whether to skip null stock values
     * @param boolean $skip
     */
    public function skipNullValues($skip=true)
    {
        $this->skipNullValues = $skip;
    }


    /**
     * Return the list of columns required in order to use this renderer
     * @return array
     */
    public function getRequiredColumns()
    {
        return [
            $this->stock_column,
            $this->stock_level_column,
            $this->pricelist_column,
            $this->avg_sale_column];
    }
}

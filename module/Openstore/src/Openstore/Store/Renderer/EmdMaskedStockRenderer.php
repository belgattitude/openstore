<?php
namespace Openstore\Store\Renderer;

use Soluble\FlexStore\Renderer\RowRendererInterface;
use ArrayObject;

class EmdMaskedStockRenderer implements RowRendererInterface
{

    /**
     * @var string
     */
    protected $stock_column;


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
     * @var int
     */
    protected $exportMaxDisplayQty = 30;

    /**
     * @var int
     */
    protected $maxDisplayQty = 30;


    /**
     * @param $stock_column column name of the stock information on which stock level is computed
     * @param $avg_sale_column column name of the average sales column
     * @param $pricelist_column column name containing the pricelist reference
     */
    public function __construct($stock_column, $avg_sale_column, $pricelist_column)
    {
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
            } elseif (!$row->offsetExists($this->pricelist_column)) {
                throw new \Exception(__METHOD__ . " pricelist_column '{$this->pricelist_column}' does not exists in row.");
            } elseif (!$row->offsetExists($this->avg_sale_column)) {
                throw new \Exception(__METHOD__ . " avg_sale_column '{$this->avg_sale_column}' does not exists in row.");
            }
            $this->columnsChecked = true;
        }

        $pricelist = $row[$this->pricelist_column];
        $stock = (int) $row[$this->stock_column];
        $avg_sale_qty = $row[$this->avg_sale_column];


        if ($stock < 1) {
            $masked_stock = 0;
        } else {
            switch ($pricelist) {
                case '100F':
                case '120F':
                    $masked_stock = $stock;
                    break;

                case '100U':
                case '120U':
                case '200U':
                case '100U':
                    $masked_stock = 0;
                    break;

                case '100B':
                case '120B':
                    // Limited to max export quantity
                    $masked_stock = min([$stock, $this->exportMaxDisplayQty]);
                    break;

                default:
                    // Limited average quantity
                    if ($avg_sale_qty < 1) {
                        $masked_stock = min([$stock, $this->maxDisplayQty]);
                    } else {
                        $avg_threshold = ($avg_sale_qty / 2);
                        if ($this->maxDisplayQty > $avg_threshold) {
                            // In case of very low average, the product may seem out of stock
                            $masked_stock = min([$stock, $this->maxDisplayQty]);

                        } else {
                            $masked_stock = min([$stock, $avg_threshold]);
                        }
                    }
            }
        }


        $row[$this->stock_column] = max([(int) $masked_stock, 0]);
    }

    /**
     * @param int $max_display_qty
     */
    public function setMaxDisplayQty($max_display_qty)
    {
        $this->maxDisplayQty = $max_display_qty;
    }

    /**
     * @param int $max_display_qty
     */
    public function setExportMaxDisplayQty($max_display_qty)
    {
        $this->exportMaxDisplayQty = $max_display_qty;
    }


    /**
     * Return the list of columns required in order to use this renderer
     * @return array
     */
    public function getRequiredColumns()
    {
        return [
            $this->stock_column,
            $this->pricelist_column,
            $this->avg_sale_column];
    }
}

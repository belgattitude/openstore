<?php

namespace OpenstoreApi\Api\ApiTrait;

use Soluble\FlexStore\FlexStore;
use Openstore\Store\Renderer\DateMinRenderer;
use Openstore\Store\Renderer\EmdStockLevelRenderer;
use Openstore\Store\Renderer\EmdMaskedStockRenderer;

trait StockRendererTrait
{

    /**
     * Prevent next_available_stock_at to be in the past
     *
     * @param FlexStore $store
     */
    protected function addNextAvailableStockAtRenderer(FlexStore $store, $date_column = 'next_available_stock_at')
    {
        $cm = $store->getColumnModel();

        if ($cm->exists($date_column)) {
            //$col = $cm->get($date_column);
            $dateMinRenderer = new DateMinRenderer($date_column);
            $cm->addRowRenderer($dateMinRenderer);
        }
    }

    /**
     * @param FlexStore $store
     * @param string $stock_column
     * @param string $avg_sale_column
     * @param string $pricelist_column
     */
    protected function addMaskedStockRenderer(FlexStore $store, $stock_column, $avg_sale_column = 'avg_monthly_sale_qty', $pricelist_column = 'pricelist_reference') {
        $cm = $store->getColumnModel();
        $stockLevelRenderer = new EmdMaskedStockRenderer($stock_column, $avg_sale_column, $pricelist_column);
        $cm->addRowRenderer($stockLevelRenderer);
    }

    /**
     * Add stock level renderer
     *
     * @param FlexStore $store
     * @param string $stock_level_column name of the stock level column
     * @param string $stock_column name of the column containing the stock value
     * @param string $avg_sale_column name of the column containing the average monthly sales
     * @param string $pricelist_column
     */
    protected function addStockLevelRenderer(FlexStore $store, $stock_level_column, $stock_column, $avg_sale_column="avg_monthly_sale_qty", $pricelist_column="pricelist_reference")
    {
        $cm = $store->getColumnModel();

        $stockLevelRenderer = new EmdStockLevelRenderer($stock_level_column, $stock_column, $avg_sale_column, $pricelist_column);
        if ($stock_level_column == "next_stock_level") {
            $stockLevelRenderer->skipNullValues();
        }
        $cm->addRowRenderer($stockLevelRenderer);
    }
}

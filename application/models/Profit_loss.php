<?php
class Profit_loss extends CI_Model
{
    public function getData($start_date, $end_date)
	{

   
        $sql =  "SELECT
        (
        SELECT
            SUM(ipos_expenses.amount)
        FROM
            ipos_expenses
        WHERE
            created_at >= '{$start_date} 00:00:00' AND created_at <= '{$end_date} 23:59:59' and type = 'OUTFLOW'
        ) total_expenses,
        SUM(
        ( sales_items.item_unit_price * ( if(sales_items.quantity_purchased > 0, sales_items.quantity_purchased, 0) ) ) - (sales_items.item_unit_price * ABS(sales_items.quantity_purchased) * (sales_items.discount_percent / 100) )
        ) AS total_revenue,
        SUM( IF(sales_items.qty_selected = 'wholesale', sales_items.pack * sales_items.item_cost_price * sales_items.quantity_purchased, sales_items.item_cost_price * sales_items.quantity_purchased) ) AS cost_of_goods,
        SUM(
        sales_items.item_unit_price *(
            IF(
                sales_items.quantity_purchased < 0,
                ABS(sales_items.quantity_purchased),
                0
            )
        )
        ) total_returns,
        SUM(sales_items.vat) AS total_vat
        FROM
        ipos_sales sales
        LEFT JOIN ipos_sales_items sales_items ON
        sales.sale_id = sales_items.sale_id
        WHERE
        sales.sale_status = 0 AND
        sales.location_id = 2 AND
        sales.sale_time >= '{$start_date} 00:00:00' AND sale_time <= '{$end_date} 23:59:59'";

        $query = $this->db->query($sql);
		return $query->result_array();
	}
	
}
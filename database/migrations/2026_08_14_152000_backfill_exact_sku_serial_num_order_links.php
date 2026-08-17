<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class BackfillExactSkuSerialNumOrderLinks extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id')
        ) {
            return;
        }

        DB::statement('
            UPDATE serial_nums sn
            INNER JOIN (
                SELECT unique_links.serial_num_id,
                       unique_links.order_id,
                       unique_links.order_item_id
                FROM (
                    SELECT candidate_links.serial_num_id,
                           MAX(candidate_links.order_id) AS order_id,
                           MAX(candidate_links.order_item_id) AS order_item_id
                    FROM (
                        SELECT sn2.id AS serial_num_id,
                               item_candidates.order_id,
                               item_candidates.order_item_id
                        FROM serial_nums sn2
                        INNER JOIN (
                            SELECT oi.id AS order_item_id,
                                   oi.order_id,
                                   oi.product_sku_id,
                                   o.paid_at,
                                   oi.amount,
                                   COALESCE(linked.linked_count, 0) AS linked_count,
                                   COUNT(DISTINCT sn3.id) AS candidate_count
                            FROM order_items oi
                            INNER JOIN orders o ON o.id = oi.order_id
                            LEFT JOIN (
                                SELECT order_item_id, COUNT(*) AS linked_count
                                FROM serial_nums
                                WHERE order_item_id IS NOT NULL
                                  AND deleted_at IS NOT NULL
                                GROUP BY order_item_id
                            ) linked ON linked.order_item_id = oi.id
                            INNER JOIN serial_nums sn3
                                ON sn3.productSku_id = oi.product_sku_id
                               AND sn3.order_item_id IS NULL
                               AND sn3.deleted_at IS NOT NULL
                               AND o.paid_at IS NOT NULL
                               AND sn3.deleted_at BETWEEN DATE_SUB(o.paid_at, INTERVAL 1 HOUR)
                                                      AND DATE_ADD(o.paid_at, INTERVAL 1 HOUR)
                            GROUP BY oi.id,
                                     oi.order_id,
                                     oi.product_sku_id,
                                     o.paid_at,
                                     oi.amount,
                                     linked.linked_count
                            HAVING CAST(oi.amount AS SIGNED) > CAST(COALESCE(linked.linked_count, 0) AS SIGNED)
                               AND candidate_count = CAST(oi.amount AS SIGNED) - CAST(COALESCE(linked.linked_count, 0) AS SIGNED)
                        ) item_candidates
                            ON item_candidates.product_sku_id = sn2.productSku_id
                           AND sn2.order_item_id IS NULL
                           AND sn2.deleted_at IS NOT NULL
                           AND sn2.deleted_at BETWEEN DATE_SUB(item_candidates.paid_at, INTERVAL 1 HOUR)
                                                  AND DATE_ADD(item_candidates.paid_at, INTERVAL 1 HOUR)
                    ) candidate_links
                    GROUP BY candidate_links.serial_num_id
                    HAVING COUNT(DISTINCT candidate_links.order_item_id) = 1
                ) unique_links
            ) links ON links.serial_num_id = sn.id
            SET sn.order_id = links.order_id,
                sn.order_item_id = links.order_item_id
            WHERE sn.order_item_id IS NULL
        ');
    }

    public function down()
    {
        //
    }
}

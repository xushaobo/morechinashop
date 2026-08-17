<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class ReassignStaleSerialDataLinksToUniquePaidAtItems extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('serial_nums', 'order_id') ||
            !Schema::hasColumn('serial_nums', 'order_item_id') ||
            !Schema::hasColumn('product_skus', 'root_sku_id')
        ) {
            return;
        }

        DB::statement('
            UPDATE serial_nums sn
            INNER JOIN product_skus sn_sku ON sn.productSku_id = sn_sku.id
            INNER JOIN order_items old_oi ON sn.order_item_id = old_oi.id
            INNER JOIN orders old_o ON old_oi.order_id = old_o.id
            INNER JOIN (
                SELECT safe_links.root_sku_id,
                       safe_links.deleted_at,
                       safe_links.order_id,
                       safe_links.order_item_id
                FROM (
                    SELECT serial_groups.root_sku_id,
                           serial_groups.deleted_at,
                           MAX(item_candidates.order_id) AS order_id,
                           MAX(item_candidates.order_item_id) AS order_item_id,
                           MAX(CAST(item_candidates.amount AS SIGNED) - CAST(item_candidates.linked_count AS SIGNED)) AS remaining_amount,
                           serial_groups.serial_count
                    FROM (
                        SELECT ps.root_sku_id,
                               sn2.deleted_at,
                               COUNT(sn2.id) AS serial_count
                        FROM serial_nums sn2
                        INNER JOIN product_skus ps ON sn2.productSku_id = ps.id
                        WHERE sn2.deleted_at IS NOT NULL
                          AND ps.root_sku_id IS NOT NULL
                        GROUP BY ps.root_sku_id, sn2.deleted_at
                    ) serial_groups
                    INNER JOIN (
                        SELECT oi.id AS order_item_id,
                               oi.order_id,
                               oi.amount,
                               o.paid_at,
                               ps.root_sku_id,
                               COALESCE(linked.linked_count, 0) AS linked_count
                        FROM order_items oi
                        INNER JOIN orders o ON o.id = oi.order_id
                        INNER JOIN product_skus ps ON oi.product_sku_id = ps.id
                        LEFT JOIN (
                            SELECT order_item_id, COUNT(*) AS linked_count
                            FROM serial_nums
                            WHERE order_item_id IS NOT NULL
                              AND deleted_at IS NOT NULL
                            GROUP BY order_item_id
                        ) linked ON linked.order_item_id = oi.id
                        WHERE o.paid_at IS NOT NULL
                          AND ps.root_sku_id IS NOT NULL
                          AND CAST(oi.amount AS SIGNED) > CAST(COALESCE(linked.linked_count, 0) AS SIGNED)
                    ) item_candidates
                        ON item_candidates.root_sku_id = serial_groups.root_sku_id
                       AND item_candidates.paid_at = serial_groups.deleted_at
                    GROUP BY serial_groups.root_sku_id,
                             serial_groups.deleted_at,
                             serial_groups.serial_count
                    HAVING COUNT(DISTINCT item_candidates.order_item_id) = 1
                       AND serial_groups.serial_count = remaining_amount
                ) safe_links
            ) links
                ON links.root_sku_id = sn_sku.root_sku_id
               AND links.deleted_at = sn.deleted_at
            SET sn.order_id = links.order_id,
                sn.order_item_id = links.order_item_id
            WHERE old_o.serial_data IS NOT NULL
              AND sn.productSku_id <> old_oi.product_sku_id
              AND sn.deleted_at NOT BETWEEN DATE_SUB(old_o.paid_at, INTERVAL 1 HOUR)
                                      AND DATE_ADD(old_o.paid_at, INTERVAL 1 HOUR)
              AND sn.order_item_id <> links.order_item_id
        ');
    }

    public function down()
    {
        //
    }
}

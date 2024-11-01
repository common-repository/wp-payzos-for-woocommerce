<div id="PAYZOS">
    <div class="row">
        <?php if ($_data["error"]) { ?>
            <h5 style="color : #e74c3c; text-align:center"><?php _e($_data["error"]); ?></h5>
        <?php } else { ?>
            <table width="500px">
                <tr>
                    <td class="text-left"><?php _e(
                        "Payment Id : ",
                        "wp-payzos-for-woocommerce"
                    ); ?></td>
                    <td class="text-left">PZ<?php echo $_data["payment_id"]; ?></td>
                </tr>
                <tr>
                    <td class="text-left"><?php _e("TX : ", "wp-payzos-for-woocommerce"); ?></td>
                    <td class="text-left"><?php echo $_data["transaction_hash"]; ?></td>
                </tr>
                <tr>
                    <td class="text-left"><?php _e(
                        "amount : ",
                        "wp-payzos-for-woocommerce"
                    ); ?></td>
                    <td class="text-left"><?php echo $_data["amount"]; ?></td>
                </tr>
                <tr>
                    <td class="text-left"><?php _e("date : ", "wp-payzos-for-woocommerce"); ?></td>
                    <td class="text-left"><?php echo $_data["date"]; ?></td>
                </tr>
            </table>
        <?php } ?>

    </div>
</div>

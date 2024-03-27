<?php 
    $cs_quantity = get_post_meta( $post->ID, 'cs_quantity', true );
    $cs_valid_from = get_post_meta( $post->ID, 'cs_valid_from', true );
    $cs_valid_until = get_post_meta( $post->ID, 'cs_valid_until', true );
?>
<table class="form-table priority-coupon-metabox" data-locale="<?php echo get_locale(); ?>"> 
<input type="hidden" name="<?php echo self::$nounce; ?>" value="<?php echo wp_create_nonce( self::$nounce ); ?>">
    <tr>
        <th>
            <label for="cs_quantity"><?php echo esc_html__( 'Quantity', 'competitive-scheduling' ); ?></label>
        </th>
        <td>
            <input 
                type="number" 
                name="cs_quantity" 
                id="cs_quantity" 
                class="input-numbers"
                min="1"
                max="999"
                value="<?php echo ( isset( $cs_quantity ) ) ? esc_html( $cs_quantity ) : ''; ?>"
                required
            >
        </td>
    </tr>
    <tr>
        <th>
            <label for="cs_valid_from"><?php echo esc_html__( 'Valid From', 'competitive-scheduling' ); ?></label>
        </th>
        <td>
            <div class="ui calendar" id="rangestart">
                <div class="ui input left icon">
                    <i class="calendar icon"></i>
                    <input 
                        type="text" 
                        name="cs_valid_from" 
                        id="cs_valid_from" 
                        class="input-small-text"
                        value="<?php echo ( isset( $cs_valid_from ) ) ? esc_html( $cs_valid_from ) : ''; ?>"
                    >
                </div>
            </div>
            
        </td>
    </tr>
    <tr>
        <th>
            <label for="cs_valid_until"><?php echo esc_html__( 'Valid Until', 'competitive-scheduling' ); ?></label>
        </th>
        <td>
            <div class="ui calendar" id="rangeend">
                <div class="ui input left icon">
                    <i class="calendar icon"></i>
                    <input 
                        type="text" 
                        name="cs_valid_until" 
                        id="cs_valid_until" 
                        class="input-small-text"
                        value="<?php echo ( isset( $cs_valid_until ) ) ? esc_html( $cs_valid_until ) : ''; ?>"
                    >
                </div>
            </div>
            
        </td>
    </tr>               
</table>
<?php if( $post->post_status === 'publish' ): ?>
<button id="imprimir-post" onclick="imprimirPost()">Imprimir</button>
<?php endif; ?>
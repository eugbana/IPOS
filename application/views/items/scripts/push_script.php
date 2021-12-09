<script>
    $(document).ready(function () {
        $('.selected-items-del-anchor').on('click',function (e) {
            e.preventDefault();
            const thisId = $(this).attr('data-itemId');
            alert(thisId);
            $('#'+thisId).remove();
        });
        $('.selected-items-pack').on('change',function (e) {
            const thisId = $(this).attr('data-itemId');
        });
        $('.selected-item-quantity').on('change',function (e) {
            const thisId = $(this).attr('data-itemId');
            const quant = $(this).val();
            const uPrice = $('#selected-item-transfer-price-'+thisId).val();
            // alert(uPrice+' '+quant+' '+thisId);
            const tPrice = uPrice * quant;
            $('#item-price-'+thisId).text(tPrice);
        });
        $('.selected-item-transfer-price').on('change',function (e) {
            const thisId = $(this).attr('data-itemId');
            const uPrice = parseFloat($(this).val());
            const quant = $('#selected-item-quantity-'+thisId);
            let tPrice = uPrice;
            // alert(uPrice+' '+quant+' '+JSON.stringify(quant));
            if(quant.length !== 0){
                tPrice = uPrice * quant.val();
            }
            $('#item-price-'+thisId).text(tPrice);
        });
    });
</script>

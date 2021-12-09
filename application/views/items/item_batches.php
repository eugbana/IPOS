
<ul id="error_message_box" class="error_message_box"></ul>
<table class="table table-primary">
    <thead>
    <tr>
        <th>S/N</th>
        <th>Batch</th>
        <th>Expiry Date</th>
        <th>Quantity</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if(isset($batches) && count($batches)> 0){
        $c = 1;
        foreach($batches as $batch){
            ?>
            <tr>
                <td><?=$c?></td>
                <td><?=$batch->batch_no?></td>
                <td><?=$batch->expiry?></td>
                <td><?=$batch->quantity?></td>
            </tr>
    <?php
            $c++;
        }
    }else{
        echo "No batches to display";
    }
    ?>
    </tbody>
</table>



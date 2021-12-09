<!--<script type="text/javascript">-->
<!--    dialog_support.init("a.modal-dlg");-->
<!--</script>-->
<div class="">
    <div id="i-rech-message-div">
    </div>
    <ul class="nav nav-tabs" data-tabs="tabs">
        <li class="active" role="presentation">
            <a data-toggle="tab" href="#power_tab" title="Power unit purchase">Power</a>
        </li>
        <li role="presentation">
            <a data-toggle="tab" href="#data_tab" title="Data bundles and more">Data</a>
        </li>
        <li role="presentation">
            <a data-toggle="tab" href="#tv_tab" title="Tv bouquet and others">TV</a>
        </li>
        <li role="presentation">
            <a data-toggle="tab" href="#airtime_tab" title="Airtime vending">Airtime</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade in active" id="power_tab">
            <?php $this->load->view("irecharge/power"); ?>
        </div>
        <div class="tab-pane" id="data_tab">
            <?php $this->load->view("irecharge/data"); ?>
        </div>
        <div class="tab-pane" id="tv_tab">
            <?php $this->load->view("irecharge/tv"); ?>
        </div>
        <div class="tab-pane" id="airtime_tab">
            <?php $this->load->view("irecharge/data",['airtime'=>1]); ?>
        </div>
    </div>
</div>

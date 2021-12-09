<?php


class SyncController extends CI_Controller
{

    public function index(){
        if(!is_cli()){
            echo json_encode(["error"=>"Wrong link pal"]);
//            exit();
        }
    }

    public function pushSales()
    {
        // echo is_cli();
        // echo json_encode(["message"=>"You got sales"]). ' '.PHP_EOL;
        $this->load->model('sale');
        echo json_encode($this->Sale->register_sales_batch());
        // echo "here";
//        exit();
    }
    public function create_stock_report(){
        $year = date('Y');
        $month = date('m');
        $this->Item->record_stock($month,$year);
    }
    public function pushStock()
    {
        $this->load->model('item');
        echo json_encode($this->Item->register_item_quantities_batch());
    }
    public function updateBranches(){

    }
    public function pushTransfers(){
        $this->load->model('item');
        echo json_encode($this->Item->register_item_transfers_batch());
    }
    public function pushExpenses()
    {
        $this->load->model('Expenses');
        echo json_encode($this->Expenses->push_expenses_batch());
        exit();
    }
    public function pushCustomerWallet()
    {
        echo json_encode(["message"=>"You got customer wallets"]);
        exit();
    }
}

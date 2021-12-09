<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once("Secure_Controller.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Profit_and_loss extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('profit_and_loss');

		$this->load->library('barcode_lib');
		$this->load->library('sale_lib');
        $this->load->library('item_lib');
        $this->load->model('Profit_loss');

		$this->load->library('simpleXLSX');
    }

    public function index(){

        $data = array();

        $this->load->view('account/profit_and_loss', $data);
    }

    public function data(){
        $data = $this->Profit_loss->getProfit();

        echo "<pre>";
        print_r($data);
        echo "</pre>";

    }

    public function get_data($start_date, $end_date, $type = "monthly"){

        $startTime = strtotime($start_date);
        $endtime = strtotime($end_date);

        $header = [];
        $rows = [];
    
        $baseDate = $start_date;

        $month = 2592000;

        if($type == "monthly") {
            for($i = $startTime; $i <= $endtime; $i += 86400) 
            {
                $lastday = (strtotime(date('Y-m-t',$i)) <  strtotime(date('Y-m-d',$endtime)))? date('Y-m-t',$i) : date('Y-m-d',$endtime);
               
                $data = $this->Profit_loss->getData($baseDate, $lastday);

                array_push($header,date("M Y" ,strtotime($baseDate)));

                $i = strtotime($lastday) + 86400;

                $baseDate = date("Y-m-d",$i);
            
                array_push($rows, $data);
               
            }
        }else if($type == 'yearly') {
            for($i = $startTime; $i <= $endtime; $i += 86400) 
            {
                $lastDay = (strtotime(date('Y',$i)."-12-31" ) <  strtotime(date('Y-m-d',$endtime)))? date('Y',$i)."-12-31" :  date('Y-m-d',$endtime) ;

                $data = $this->Profit_loss->getData($baseDate, $lastDay);

                array_push($header,date("Y" ,$i ));

                $i = strtotime($lastDay) + 86400;
                $baseDate = date("Y-m-d",$i);
                array_push($rows, $data);
           
            }
        }else {
            for($i = $startTime; $i <= $endtime; $i += 86400) 
            {
                $month = date("m" ,$i );
                $column = "";

                if($month < 4)  {
                    $lastDay = (strtotime(date('Y',$i)."-03-31" ) <  strtotime(date('Y-m-d',$endtime)))? date('Y',$i)."-03-31" :  date('Y-m-d',$endtime) ;
                    $column="Q1";
                }
                elseif($month >3 && $month < 7) {
                    $lastDay = (strtotime(date('Y',$i)."-06-31" ) <  strtotime(date('Y-m-d',$endtime)))? date('Y',$i)."-06-31" :  date('Y-m-d',$endtime) ;
                    $column = "Q2";
                }
                elseif($month >6 && $month < 10) {
                    $lastDay = (strtotime(date('Y',$i)."-09-31" ) <  strtotime(date('Y-m-d',$endtime)))? date('Y',$i)."-09-31" :  date('Y-m-d',$endtime) ;
                    $column = "Q3";
                }
                else {
                    $lastDay = (strtotime(date('Y',$i)."-12-31" ) <  strtotime(date('Y-m-d',$endtime)))? date('Y',$i)."-12-31" :  date('Y-m-d',$endtime) ;
                    $column = "Q4";
                }
                
                $column .= date(" Y" ,$i );
                $data = $this->Profit_loss->getData($baseDate, $lastDay);

                $i = strtotime($lastDay) + 86400;

                $baseDate = date("Y-m-d",$i);

                array_push($rows, $data);
                array_push($header,$column);
            }
        }

        //logged in employee and branch info 
        $data = array();
		$employee_info = $this->Employee->get_info($this->Employee->get_logged_in_employee_info()->person_id);
		$branch_info = $this->Employee->get_branchinfo($employee_info->branch_id);
		$data['branch_address'] = $branch_info->location_address;
		$data['branch_number'] = $branch_info->location_number;

        $data['headers'] = $header;
        $data['rows'] = $rows;
        $data['title'] = 'Profit & Loss Statement';
        $data['start'] = $start_date;
        $data['end'] = $end_date;
        $data['type'] = $type;

        // echo "<pre>";
        // print_r(date("Y-m-d",$startTime));
        // print_r($header);
        // print_r($rows);
        // echo "</pre>";

        $this->load->view('account/profit_and_loss_report', $data);
    }

    public function export_profit_and_loss($start_date, $end_date, $type)
	{
      
	
        $startTime = strtotime($start_date);
        $endtime = strtotime($end_date);

        $header = [];
        $rows = [];
    
        $baseDate = $start_date;

        $month = 2592000;

        if($type == "monthly") {
            for($i = $startTime; $i <= $endtime; $i += 86400) 
            {
                $lastday = (strtotime(date('Y-m-t',$i)) <  strtotime(date('Y-m-d',$endtime)))? date('Y-m-t',$i) : date('Y-m-d',$endtime);
               
                $data = $this->Profit_loss->getData($baseDate, $lastday);

                array_push($header,date("M Y" ,strtotime($baseDate)));

                $i = strtotime($lastday) + 86400;

                $baseDate = date("Y-m-d",$i);
            
                array_push($rows, $data);
               
            }
        }else if($type == 'yearly') {
            for($i = $startTime; $i <= $endtime; $i += 86400) 
            {
                $lastDay = (strtotime(date('Y',$i)."-12-31" ) <  strtotime(date('Y-m-d',$endtime)))? date('Y',$i)."-12-31" :  date('Y-m-d',$endtime) ;

                $data = $this->Profit_loss->getData($baseDate, $lastDay);

                array_push($header,date("Y" ,$i ));

                $i = strtotime($lastDay) + 86400;
                $baseDate = date("Y-m-d",$i);
                array_push($rows, $data);
           
            }
        }else {
            for($i = $startTime; $i <= $endtime; $i += 86400) 
            {
                $month = date("m" ,$i );
                $column = "";

                if($month < 4)  {
                    $lastDay = (strtotime(date('Y',$i)."-03-31" ) <  strtotime(date('Y-m-d',$endtime)))? date('Y',$i)."-03-31" :  date('Y-m-d',$endtime) ;
                    $column="Q1";
                }
                elseif($month >3 && $month < 7) {
                    $lastDay = (strtotime(date('Y',$i)."-06-31" ) <  strtotime(date('Y-m-d',$endtime)))? date('Y',$i)."-06-31" :  date('Y-m-d',$endtime) ;
                    $column = "Q2";
                }
                elseif($month >6 && $month < 10) {
                    $lastDay = (strtotime(date('Y',$i)."-09-31" ) <  strtotime(date('Y-m-d',$endtime)))? date('Y',$i)."-09-31" :  date('Y-m-d',$endtime) ;
                    $column = "Q3";
                }
                else {
                    $lastDay = (strtotime(date('Y',$i)."-12-31" ) <  strtotime(date('Y-m-d',$endtime)))? date('Y',$i)."-12-31" :  date('Y-m-d',$endtime) ;
                    $column = "Q4";
                }
                
                $column .= date(" Y" ,$i );
                $data = $this->Profit_loss->getData($baseDate, $lastDay);

                $i = strtotime($lastDay) + 86400;

                $baseDate = date("Y-m-d",$i);

                array_push($rows, $data);
                array_push($header,$column);
            }
        }

       

        $spreadsheet = new SpreadSheet();
        
    
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue("A1", "");
		$sheet->setCellValue("B1", "Revenue");
		$sheet->setCellValue("C1", "Returns");
		$sheet->setCellValue("D1", "Total Net Revenue");
		$sheet->setCellValue("E1", "Cost of Good Sold");
		$sheet->setCellValue("F1", "Gross Profit");
		$sheet->setCellValue("G1", "Total Expenses");
		$sheet->setCellValue("H1", "Earning before Tax");
		$sheet->setCellValue("I1", "Income Tax");
        $sheet->setCellValue("J1", "Net Earnings");
        
        $totalRevenue = 0;
        $totalReturns = 0;
        $allTotalNetRevenue = 0;
        $totalCostOfGoods = 0;
        $totalGrossProfit = 0;
        $totalExpenses = 0;
        $totalEarningsBeforeTax = 0;
        $allTotalVat = 0;
        $totalNetEarnings = 0;

		$sn = 1;

		foreach ($header as $key=>$head) {


           

            $totalNetRevenue = $rows[$key][0]["total_revenue"] - $rows[$key][0]["total_returns"];
            $grossProfit = $totalNetRevenue - $rows[$key][0]["cost_of_goods"];
            $earningsBeforeTax = $grossProfit -  $rows[$key][0]["total_expenses"];
            $netEarning = $earningsBeforeTax - $rows[$key][0]["total_vat"];

            $totalRevenue += $rows[$key][0]["total_revenue"];
            $totalReturns += $rows[$key][0]["total_returns"];
            $allTotalNetRevenue += $totalNetRevenue;
            $totalCostOfGoods += $rows[$key][0]["cost_of_goods"];
            $totalGrossProfit += $grossProfit;
            $totalExpenses += $rows[$key][0]["total_expenses"];
            $totalEarningsBeforeTax += $earningsBeforeTax;
            $allTotalVat += $rows[$key][0]["total_vat"];
            $totalNetEarnings += $netEarning; 

			
			
            $sn++;
            
            $sheet->setCellValue("A" . $sn, $head);
            $sheet->setCellValue("B" . $sn, $rows[$key][0]["total_revenue"]);
            $sheet->setCellValue("C" . $sn, $rows[$key][0]["total_returns"]);
            $sheet->setCellValue("D" . $sn, $totalNetRevenue);
            $sheet->setCellValue("E" . $sn, $rows[$key][0]["cost_of_goods"]);
            $sheet->setCellValue("F" . $sn, $grossProfit);
            $sheet->setCellValue("G" . $sn, $rows[$key][0]["total_expenses"]);
            $sheet->setCellValue("H" . $sn, $earningsBeforeTax);
            $sheet->setCellValue("I" . $sn, $rows[$key][0]["total_vat"]);
            $sheet->setCellValue("J" . $sn, $netEarning);
			
        }
        $sn++;
        $sheet->setCellValue("A" . $sn, "Total");
        $sheet->setCellValue("B" . $sn, $totalRevenue);
        $sheet->setCellValue("C" . $sn, $totalReturns);
        $sheet->setCellValue("D" . $sn, $allTotalNetRevenue);
        $sheet->setCellValue("E" . $sn, $totalCostOfGoods);
        $sheet->setCellValue("F" . $sn, $totalGrossProfit);
        $sheet->setCellValue("G" . $sn, $totalExpenses);
        $sheet->setCellValue("H" . $sn, $totalEarningsBeforeTax);
        $sheet->setCellValue("I" . $sn, $allTotalVat);
        $sheet->setCellValue("J" . $sn, $totalNetEarnings);

		$writer = new Xlsx($spreadsheet);

		$filename =  "Profit_and_lost_statement" . $start_date . 'to' . $end_date;

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
	}

	
}

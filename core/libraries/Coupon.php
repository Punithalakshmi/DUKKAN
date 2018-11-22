<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 * For Promo code Library
 *
 * @package        	CodeIgniter
 * @subpackage    	Libraries
 * @category    	Libraries
 */
class Coupon
{
    private $CI;	// CodeIgniter instance
    
		
	public function __construct()
	{
		$this->CI =& get_instance();
				
	}

  /**
       Apply new coupon to specific plan/product.
    **/

  public function coupon_apply($code,$plan_id,$cont='',$subtype='')
  {
  
      //ob_start();
    
      $this->CI->load->model('plans_model');
  
      $chk = $this->CI->promo_model->get_promo_by_code($code);
  
      $plan = $this->CI->plans_model->get_plan_by_id($plan_id);
  
      //$user_id = session_id();
  
      $user_c = $chk['no_users'];
  
      $ch_applied = $this->CI->plans_model->check_coupon_applied("coupon_applied",array("coupon_id"=>$chk['id']));
  
      $ins_data['status'] = "error";
  
      if(count($ch_applied)<$user_c || $user_c == 0)
      {
          if($chk)
          {
        
              $c_date = strtotime(date("Y-m-d"));
      
              $f_date = strtotime($chk['from_date']);
      
              $t_date = strtotime($chk['to_date']);
      
              if(($f_date<=$c_date && $c_date<=$t_date) || ($chk['from_date']=="0000-00-00" || $chk['to_date']=="0000-00-00"))
              {       
                  $amt = ($subtype=="monthly")?$plan['monthly_payment']:$plan['yearly_payment']*12;
        
                  $coupon_id = $chk['id'];
        
                  if($chk['promo_type'] =="Percentage")
                  {
          
                      $value = ($amt / 100) * $chk['percentage'];
          
                      $ans = ($amt- (($amt) / 100) * $chk['percentage']);
          
                      $st="1";
                  }

                  else if($chk['promo_type']=="Flat")
                  {
          
                      $value = $chk['amount'];
          
                      $ans = $amt - $value;
          
                      $st="2";
                  }

                  $ins_data['code'] = $code;
        
                  $ins_data['coupon_id'] = $chk['id'];
        
                  $ins_data['plan_id'] = $plan_id;         
        
                  $ins_data['org_amount'] = $amt;
        
                  $ins_data['discount_amount'] = round($value,2);
        
                  $ins_data['discount_amt'] = displayData(round($value,2),'money');
        
                  $ins_data['status'] = "success";
        
                  $ins_data['total'] = round($ans,2);
        
                  $ins_data['html'] = "<div class='alert alert-warning alert-dismissible'>".
                                  "Coupon : <strong>".$code."</strong>".
                                  "<div class='col-md-2 pull-right text-right'><button type='button' class='btn btn-danger btn-xs' onclick='remove_coupon(".$plan_id.");'>x</button></div>".
                                "</div>".
                            "</div>";

                  $this->CI->session->set_userdata("promo_details",$ins_data);                       
              }

              else
              {
            
                  $this->CI->session->unset_userdata("promo_details");
        
                  $ins_data['message'] = "Coupon is Expired";
        
                  $ins_data['discount_amount'] = "00.00";
              }
          }

          else
          {
              $this->CI->session->unset_userdata("promo_details");
      
              $ins_data['message'] = "Coupon is Invalid";
      
              $ins_data['discount_amount'] = "00.00";
          }
      }

      else
      {
    
          $this->CI->session->unset_userdata("promo_details");
    
          $ins_data['message'] = "Coupon is Invalid";
    
          $ins_data['discount_amount'] = "00.00";
      }

      return $ins_data;
  }
    
    	
}
?>
<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 * For Authorize.Net Library to perform authorize CRUD operation
 *
 * @package        	CodeIgniter
 * @subpackage    	Libraries
 * @category    	Libraries
 */
class Authorize
{
    private $CI;	// CodeIgniter instance
    
		
	public function __construct()
	{
		$this->CI =& get_instance();
				
	}
    
    /**
       Create new subscription in authorize interface
    **/
	public function create_authorize_subscribe($data,$card)
    {

        $unit = "days";
  
        $length = 7;
        
        /*if($data['subscription_type']=="monthly")
        {
            $length = 1;
        
            $unit = "months";
        }
  
        else
        {
            $length = 12;
    
            $unit = "months"; 
        }*/


        
  
        $phone = substr($data['phone'],0,3)."-".substr($data['phone'],3,3)."-".substr($data['phone'],6);
  
        $this->CI->load->library('authorize_arb');
  
        $this->CI->authorize_arb->startData('create');

        $refId = substr(md5( microtime() . 'ref' ), 0, 20);
  
        $this->CI->authorize_arb->addData('refId', $refId);
  
        $subscription_data = array(
      								'name' => $data['plan_name'],

      								'paymentSchedule' => array(
        														'interval' => array(
          																			'length' => $length,
          																			'unit' => $unit,
          																	  ),
        														'startDate' => date('Y-m-d'),       
        														'totalOccurrences' => 9999,
        														'trialOccurrences' => 0,
        												),
      
      								'amount' =>  $data['subscription_amount'],
      
      								'trialAmount' => 0.00,
      
      								'payment' => array(
        												'creditCard' => array(
          																		'cardNumber' =>  $card['card_number'],
          																		'expirationDate' =>  $card['expiry_year'].'-'.$card['expiry_month'],
          																		'cardCode' =>  $card['cvv'],
          																),
        										),
      
      								'customer' => array(
        													'id' => '',
        													'email' => $data['email'],
        													'phoneNumber' => $phone,
        											),
      
      								'billTo' => array(
        												'firstName' =>$data['first_name'],
        												'lastName' => $data['last_name'],
        												'address' => $data['address1'],
        												'city' => $data['city'],
        												'state' => $data['state'],
        												'zip' => $data['zipcode'],
        												'country' => "US",
        										),
      						);

        $this->CI->authorize_arb->addData('subscription', $subscription_data);
  
        if($this->CI->authorize_arb->send() )
        {
            $output['status'] = "success";
    
            $output['msg'] = $this->CI->authorize_arb->getId();
        }
  
        else
        {
    
            $output['status'] = "error";
    
            $output['msg'] = $this->CI->authorize_arb->getError();
        }

        return $output;
    }

    /**
       Update existing authorize subscription
    **/

    public function update_authorize_subscribe($data)
    {
  
        $this->CI->load->library('authorize_arb');    
    }

    /**
       Cancel existing authorize subscription
    **/

    public function cancel_authorize_subscribe($subscription_id='')
    {
  
  
        $this->CI->load->library('authorize_arb');
  
        $this->CI->authorize_arb->startData('cancel');
  
        $this->CI->authorize_arb->addData('subscriptionId', $subscription_id);
  
        $this->CI->authorize_arb->send();
    }
	
}
?>
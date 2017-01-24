<?php
require 'vendor/autoload.php';

require 'mysql.php';
include('lib/Way2SMS/way2sms-api.php');
require 'phpmailer/class.phpmailer.php';


$app = new Slim\App();
$app->get('/', 'get_employees');
$app->get('/event', 'get_event');
$app->get('/reg', 'get_emp');
$app->get('/otp', 'get_otp');
$app->get('/eventlist', 'get_eventlist');

// $app->get('/ee','isUserExists');

$app->get('/ee/{mobile}',
function ($request, $response, $args)
	{
	verify_mobile($args['mobile']);
	});
$app->post('/otpreg',
function ($request, $response, $args)
	{
	reg_otp($request->getParsedBody()); //Request object’s <code>getParsedBody()</code> method to parse the HTTP request
	});
$app->post('/verifyotp',
function ($request, $response, $args)
	{
	verifyotp($request->getParsedBody()); //Request object’s <code>getParsedBody()</code> method to parse the HTTP request
	});
	$app->post('/additionalregistration',
function ($request, $response, $args)
	{
	registration($request->getParsedBody()); //Request object’s <code>getParsedBody()</code> method to parse the HTTP request
	});
$app->get('/employee/{emp_code}',
function ($request, $response, $args)
	{
	get_EmpID($args['emp_code']);
	});
/**$app->post('/employee_add',function($request, $response, $args) {

// Validation($email);

add_employee($request->getParsedBody());//Request object�s <code>getParsedBody()</code> method to parse the HTTP request
});**/
$app->put('/update_employee',
function ($request, $response, $args)
	{
	update_employee($request->getParsedBody());
	});
$app->delete('/delete_employee',
function ($request, $response, $args)
	{
	delete_employee($request->getParsedBody());
	});
$app->post('/pinverification',
function ($request, $response, $args)
	{
	verification($request->getParseBody());
	});
$app->post('/addevent',
function ($request, $response, $args)
	{
	add_event($request->getParsedBody());
	});
$app->put('/add_push_id',
function ($request, $response, $args)
	{
	add_push_id($args['push_id']);
	});
$app->post('/sent_pushmsg',
function ($request, $response, $args)
	{
	sentpush_msg($request->getParsedBody()); //Request object’s <code>getParsedBody()</code> method to parse the HTTP request
	});
	$app->post('/sendemail',function ($request, $response, $args)
	{
	sendemail($request->getParsedBody()); //Request object’s <code>getParsedBody()</code> method to parse the HTTP request
	});
	$app->post('/sendsms',function ($request, $response, $args)
	{
	send_sms($request->getParsedBody()); //Request object’s <code>getParsedBody()</code> method to parse the HTTP request
	});
$app->run();

function get_employees()
	{
	$db = connect_db();
	$sql = "SELECT * FROM employee ORDER BY `emp_name`";
	$exe = $db->query($sql);
	$data = $exe->fetch_all(MYSQLI_ASSOC);
	$db = null;
	echo json_encode($data);
	}

function verify_employee($emp_code)
	{
	$db = connect_db();
	$sql = "SELECT * FROM employee WHERE `emp_code` = '$emp_code'";
	$exe = $db->query($sql);
	$db = null;
	if ($exe->num_rows > 0)
		{
		return true;
		}

	return false;
	}
function get_EmpID($emp_code)
	{
	$result=array();
	$db = connect_db();
	if(verify_employee($emp_code)){
	$sql = "SELECT * FROM employee WHERE `emp_code` = '$emp_code'";
	$exe = $db->query($sql);
	$data = $exe->fetch_all(MYSQLI_ASSOC);
	$db = null;
	echo json_encode($data);
	}
	else{
	$result['code']=1003;
	$result['desc']="Emp_code not found";
	echo json_encode($result);
	}
	}
function checkMobile($mobile)
	{
	$db = connect_db();
	$sql = "SELECT * FROM employee where mobile = '$mobile'";
	$exe = $db->query($sql);
	$db = null;
	if ($exe->num_rows > 0)
		{
		return true;
		}

	return false;
	}

function generatePIN($digits = 6)
	{
	$i = 0;
	$otp = "";
	while ($i < $digits)
		{
		$otp.= mt_rand(0, 9);
		$i++;
		}

	return $otp;
	}

function insertOTP($otp, $mobile)
	{
	  $db = connect_db();
	 if(verifyMobile($mobile)){
		$sql="update reg_employee set otp='$otp' where mobile='$mobile'";
		$exe = $db->query($sql);
	//	echo $response;
	}
	else{
	$sql = "insert into reg_employee (mobile,otp)" . " VALUES($mobile,$otp)";
	$exe = $db->query($sql);
			}
	$db = null;

}
	
/**	$sql = "insert into reg_employee (mobile,otp)" . " VALUES($mobile,$otp)";
	$exe = $db->query($sql);**/
	function verifyMobile($mobile){
		$db=connect_db();
		$sql="select * from reg_employee where mobile='$mobile'";
		$exe=$db->query($sql);
			$db=null;
	if($exe->num_rows>0){
		return true;
	}
	return false;
	}
	

function verifyPIN($mobile, $otp)
	{
	$db = connect_db();
	$sql = "SELECT * FROM reg_employee where mobile = '$mobile'  and otp = '$otp' ";
	$exe = $db->query($sql);
	$db = null;
	if ($exe->num_rows > 0)
		{ 
		return true;
		}

	return false;
	}
function verifyotp($data)
	{
	$mobile = $data["mobile"];
	$otp = $data["otp"];
	$result = array();
	if (verifyPIN($mobile, $otp))
		{
		$today = date("Y-m-d H:i:s"); 
		$db=connect_db();
		$sql="update reg_employee set verified_date='$today' where mobile='$mobile'";
		$exe=$db->query($sql);
		$result['code'] = 0;
		$result['desc'] = "Success";

		}
	  else
		{
		$result['code'] = 1002;
		$result['desc'] = "mobile number or otp pin is wrong";
		}

	echo json_encode($result);
	}

function reg_otp($data)
	{
	$mobile = $data['mobile'];
	$result = array();
    if(!preg_match('/^\+?([0-9]{1,4})\)?[-. ]?([0-9]{9})$/',$mobile)) {
	$result[]=1010;
	$result[]="Mobile number not valid";
	}
	
	else{
	if (checkMobile($mobile))
		{
	 	$otp = generatePIN(6);
		insertOTP($otp, $mobile);
		$res = sendWay2SMS('7904446431','mob1234', $mobile, $otp.' CVIAC APP Registration Confirmation Code');
		$result['code'] = 0;
		$result['desc'] = "Success";
		}
		else{
		$result['code'] =1001;
		$result['desc'] = "Mobile Number Not Found";
		}
		}
	
		

	echo json_encode($result);
	}

function get_emp()
	{
	$db = connect_db();
	$sql = "SELECT * FROM reg_employee ORDER BY `created_date`";
	$exe = $db->query($sql);
	$data = $exe->fetch_all(MYSQLI_ASSOC);
	$db = null;
	echo json_encode($data);
	}
function verification($data){
	$emp_code=$data['emp_code'];
	$dob=$data['dob'];
	$db=connect_db();
	$sql="select * from employee where emp_code='$emp_code' and dob='$dob'";
	$exe=$db->query($sql);
	$db=null;
	if($exe->num_rows>0){
		return true;
	}
return false;
}
function registration($data){
	$mobile=$data['mobile'];
	$result=array();
	if(verification($data)){
		$otp=generatePIN();
		$res = sendWay2SMS('7904446431','mob1234', $mobile, $otp.' CVIAC APP Registration Confirmation Code');
		insertOTP($otp, $mobile);
		$db=connect_db();
		$sql="update employee set mobile='$mobile' where emp_code='$data[emp_code]'";
		$exe = $db->query($sql);
		$db = null;
		$result['code']=0;
		$result['desc']="success";
	}
	else {
		$result['code']=1003;
		$result['desc']="emp_code not found";
	}
		echo json_encode($result);
}

function update_employee($data)
	{
	$db = connect_db();
	$sql = "update employee SET emp_name = '$data[emp_name]',email='$data[email]',mobile = '$data[mobile]',dob='$data[dob]',gender='$data[gender]',department='$data[department]',designation='$data[designation]',status='$data[status]'" . " WHERE emp_code = '$data[emp_code]'";
	$exe = $db->query($sql);
	$last_emp_code = $db->affected_rows;
	$db = null;
	if (!empty($last_emp_code)) echo $last_emp_code;
	}

function delete_employee($employee)
	{
	$db = connect_db();
	$sql = "DELETE FROM employee WHERE emp_code = '$employee[emp_code]'";
	$exe = $db->query($sql);
	$db = null;
	if (!empty($last_id)) echo $last_id;
	  else echo false;
	}

function add_push_id($data)
	{

	//  $push_id=$data['push_id'];

	$result = array();
	$db = connect_db();
	$sql = "update employee set push_id='$data[push_id]'" . " where emp_code='$data[emp_code]'";
	$exe = $db->query($sql);
	$last_id = $db->affected_rows;
	$db = null;
	if (!empty($last_id))
		{
		$result['code'] = 0;
		$result['desc'] = "success";
		}
	  else
		{
		$result['code'] = 1003;
		$result['desc'] = "emp_code not found";
		}

	echo json_encode($result);
	}

function get_eventlist()
	{
	$response = array();
	$db = connect_db();
	$sql = "SELECT * FROM event_list ORDER BY `event_date`";
	$exe = $db->query($sql);
	$data = $exe->fetch_all(MYSQLI_ASSOC);
	$db = null;
	echo json_encode($data);
	}

function sentpush_msg($data)
	{

	// $result=array();

	$db = connect_db();
	$sql = "insert into sent_pushmsg(msg_id,push_id,sender_id,sender_name,receiver_id,receiver_name,msg)" . "values ('$data[msg_id]','$data[push_id]','$data[sender_id]','$data[sender_name]','$data[receiver_id]','$data[receiver_name]','$data[msg]')";
	$exe = $db->query($sql);
	}

function get_event()
	{
	$db = connect_db();
	$sql = "SELECT * FROM event_list ORDER BY `id`";
	$exe = $db->query($sql);
	$data = $exe->fetch_all(MYSQLI_ASSOC);
	$db = null;
	echo json_encode($data);
	}

function sendemail($data){
		$mail = new PHPMailer();
    $mail->CharSet =  "utf-8";
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->Username = "cviacmobility@gmail.com";
    $mail->Password = "tech@cviac";
	$mail->SMTPSecure = "ssl";  
    $mail->Host = "smtp.gmail.com";
    $mail->Port = "465";
  
   // $mail->setFrom('cviacmobility.com', 'your name');
    //$mail->AddAddress('gunaseelan240@gmail.com', 'receivers name');
 
    //$mail->Subject  =  'using PHPMailer';
    //$mail->IsHTML(true);
    //$mail->Body    = 'Hi there ,
	                 // <br />
					  //this mail was sent using PHPMailer...
					  //<br />
					  //cheers... :)';
				//	$mail->setFrom($data['username']);
					$mail->AddAddress($data['email']);
					$mail->Subject=$data['subject'];
					$mail->Body=$data['message'];
	if($mail->Send())
	{
		$result=array();
		$result['code']=0;
		$result['desc']="Message was Successfully Send :)";
		echo json_encode($result);
	}
	else
	{
		$result=array();
		$result['code']=1013;
		$result['desc']="Mail Error - >".$mail->ErrorInfo;
		echo json_encode($result);
	}
		

	}

function send_sms($data){
	$result=array();
	$mobile=$data['mobile'];
	$msg=$data['msg'];
	 $res=sendWay2SMS('7904446431','mob1234', $mobile, $msg);
	 /**.' http://apps.cviac.com/mobileapps/cviacapp.apk'**/
	 $result['code']=0;
	 $result['desc']="success";
         echo json_encode($result);
	 }
	
?>
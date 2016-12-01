<?php
require 'vendor/autoload.php';

require 'mysql.php';

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

function get_EmpID($emp_code)
	{
	$db = connect_db();
	$sql = "SELECT * FROM employee WHERE `emp_code` = '$emp_code'";
	$exe = $db->query($sql);
	$data = $exe->fetch_all(MYSQLI_ASSOC);
	$db = null;
	echo json_encode($data);
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
	$sql = "insert into reg_employee (mobile,otp)" . " VALUES($mobile,$otp)";
	$exe = $db->query($sql);
	$db = null;
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
	$mobile = $data["mobile"];
	$result = array();
	if (checkMobile($mobile))
		{
		$otp = generatePIN(5);
		insertOTP($otp, $mobile);
		$result['code'] = 0;
		$result['desc'] = "Success";
		}
	  else
		{
		$result['code'] = 1001;
		$result['desc'] = "mobile number is not found";
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

/**function add_employee($data) {
$db = connect_db();

// $data=array(emp_code,emp_name,email,mobile,dob,gender,manager,department,designation,status);
// $data_string = json_encode($data);

$sql = "insert into employee (emp_code,emp_name,email,mobile,dob,gender,manager,department,designation,status)"
. " VALUES('$data[emp_code]','$data[emp_name]','$data[email]','$data[mobile]','$data[dob]','$data[gender]','$data[manager]','$data[department]','$data[designation]','$data[status]')";
$exe = $db->query($sql);
$last_id = $db->insert_id;
$db = null;
if (!empty($last_id))
echo $last_id;
  else
echo false;
} **/
/**function reg_employee($data) {
if($this->get_request_method()!='POST'){
$this->response('',406);
}

$mobile=$this->_request['mobile'];
$Otp=$this->_request['otp'];
if(!empty($mobile)){
if(filter_var($mobile,FILTER_VALIDATE_MOBILE)){
$db = connect_db();

// $data=array(emp_code,emp_name,email,mobile,dob,gender,manager,department,designation,status);
// $data_string = json_encode($data);

$sql = "insert into reg_employee (mobile,otp,created_date)"
. " VALUES('$data[mobile]','$Otp(otp)','now()')";
$exe = $db->query($sql);
$db->insert;

// $last = $db->insert;

$db = null;
}
}

if (!empty($last))
echo $last;
  else
echo false;
}

**/

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
		$result['code'] = "0";
		$result['desc'] = "success";
		}
	  else
		{
		$result['code'] = "1003";
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

?>